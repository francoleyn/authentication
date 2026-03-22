<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    protected array $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain',
        'text/csv',
        'application/zip',
        'application/x-rar-compressed',
    ];

    protected int $maxFileSize = 10240; // 10MB in KB

    public function index(Request $request)
    {
        $query = $request->user()->files()->latest();

        if ($request->has('collection')) {
            $query->inCollection($request->collection);
        }

        if ($request->has('type')) {
            match ($request->type) {
                'images' => $query->images(),
                'documents' => $query->documents(),
                default => null,
            };
        }

        $files = $query->paginate(15);

        return response()->json([
            'files' => $files,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:' . $this->maxFileSize,
            'collection' => 'nullable|string|max:50',
            'is_public' => 'boolean',
        ]);

        $uploadedFile = $request->file('file');

        if (!in_array($uploadedFile->getMimeType(), $this->allowedMimeTypes)) {
            return response()->json([
                'message' => 'File type not allowed',
                'allowed_types' => $this->allowedMimeTypes,
            ], 422);
        }

        $disk = $request->is_public ?? true ? 'public' : 'local';
        $collection = $request->collection ?? 'uploads';
        $extension = $uploadedFile->getClientOriginalExtension();
        $fileName = Str::uuid() . '.' . $extension;
        $path = $collection . '/' . date('Y/m') . '/' . $fileName;

        Storage::disk($disk)->put($path, file_get_contents($uploadedFile));

        $file = File::create([
            'user_id' => $request->user()->id,
            'name' => $fileName,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'path' => $path,
            'disk' => $disk,
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'extension' => $extension,
            'collection' => $collection,
            'is_public' => $request->is_public ?? true,
        ]);

        return response()->json([
            'message' => 'File uploaded successfully',
            'file' => $file,
        ], 201);
    }

    public function storeMultiple(Request $request)
    {
        $request->validate([
            'files' => 'required|array|max:10',
            'files.*' => 'file|max:' . $this->maxFileSize,
            'collection' => 'nullable|string|max:50',
            'is_public' => 'boolean',
        ]);

        $uploadedFiles = [];
        $errors = [];

        foreach ($request->file('files') as $index => $uploadedFile) {
            if (!in_array($uploadedFile->getMimeType(), $this->allowedMimeTypes)) {
                $errors[] = [
                    'file' => $uploadedFile->getClientOriginalName(),
                    'error' => 'File type not allowed',
                ];
                continue;
            }

            $disk = $request->is_public ?? true ? 'public' : 'local';
            $collection = $request->collection ?? 'uploads';
            $extension = $uploadedFile->getClientOriginalExtension();
            $fileName = Str::uuid() . '.' . $extension;
            $path = $collection . '/' . date('Y/m') . '/' . $fileName;

            Storage::disk($disk)->put($path, file_get_contents($uploadedFile));

            $file = File::create([
                'user_id' => $request->user()->id,
                'name' => $fileName,
                'original_name' => $uploadedFile->getClientOriginalName(),
                'path' => $path,
                'disk' => $disk,
                'mime_type' => $uploadedFile->getMimeType(),
                'size' => $uploadedFile->getSize(),
                'extension' => $extension,
                'collection' => $collection,
                'is_public' => $request->is_public ?? true,
            ]);

            $uploadedFiles[] = $file;
        }

        return response()->json([
            'message' => 'Files uploaded',
            'uploaded' => count($uploadedFiles),
            'files' => $uploadedFiles,
            'errors' => $errors,
        ], 201);
    }

    public function show(File $file)
    {
        if (!$file->is_public && $file->user_id !== request()->user()?->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'file' => $file,
        ]);
    }

    public function download(File $file): StreamedResponse
    {
        if (!$file->is_public && $file->user_id !== request()->user()?->id) {
            abort(403, 'Unauthorized');
        }

        if (!$file->exists()) {
            abort(404, 'File not found');
        }

        return Storage::disk($file->disk)->download($file->path, $file->original_name);
    }

    public function stream(File $file)
    {
        if (!$file->is_public && $file->user_id !== request()->user()?->id) {
            abort(403, 'Unauthorized');
        }

        if (!$file->exists()) {
            abort(404, 'File not found');
        }

        return Storage::disk($file->disk)->response($file->path);
    }

    public function update(Request $request, File $file)
    {
        if ($file->user_id !== $request->user()->id && !$request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'original_name' => 'sometimes|string|max:255',
            'collection' => 'nullable|string|max:50',
            'is_public' => 'boolean',
        ]);

        $file->update($request->only(['original_name', 'collection', 'is_public']));

        return response()->json([
            'message' => 'File updated successfully',
            'file' => $file->fresh(),
        ]);
    }

    public function destroy(Request $request, File $file)
    {
        if ($file->user_id !== $request->user()->id && !$request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $file->delete();

        return response()->json([
            'message' => 'File deleted successfully',
        ]);
    }

    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'file_ids' => 'required|array',
            'file_ids.*' => 'exists:files,id',
        ]);

        $deleted = 0;
        $errors = [];

        foreach ($request->file_ids as $fileId) {
            $file = File::find($fileId);
            
            if (!$file) {
                continue;
            }

            if ($file->user_id !== $request->user()->id && !$request->user()->hasRole('admin')) {
                $errors[] = [
                    'file_id' => $fileId,
                    'error' => 'Unauthorized',
                ];
                continue;
            }

            $file->delete();
            $deleted++;
        }

        return response()->json([
            'message' => 'Files deleted',
            'deleted' => $deleted,
            'errors' => $errors,
        ]);
    }

    public function myFiles(Request $request)
    {
        $stats = [
            'total_files' => $request->user()->files()->count(),
            'total_size' => $request->user()->files()->sum('size'),
            'images' => $request->user()->files()->images()->count(),
            'documents' => $request->user()->files()->documents()->count(),
            'collections' => $request->user()->files()
                ->select('collection')
                ->distinct()
                ->pluck('collection'),
        ];

        $stats['total_size_formatted'] = $this->formatBytes($stats['total_size']);

        return response()->json([
            'stats' => $stats,
        ]);
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
