<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\RoleAssignedNotification;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function roles()
    {
        $roles = Role::with('permissions')->get();
        
        return response()->json([
            'roles' => $roles,
        ]);
    }

    public function permissions()
    {
        $permissions = Permission::all();
        
        return response()->json([
            'permissions' => $permissions,
        ]);
    }

    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->assignRole($request->role);

        // Send notification
        $user->notify(new RoleAssignedNotification($request->role));

        return response()->json([
            'message' => 'Role assigned successfully',
            'user' => $user->load('roles'),
        ]);
    }

    public function removeRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->removeRole($request->role);

        return response()->json([
            'message' => 'Role removed successfully',
            'user' => $user->load('roles'),
        ]);
    }

    public function users()
    {
        $users = User::with('roles', 'permissions')->get();

        return response()->json([
            'users' => $users,
        ]);
    }
}
