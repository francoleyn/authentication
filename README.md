# Laravel Sanctum Authentication API

A simple API authentication system using Laravel Sanctum with Docker.

## Requirements

- Docker Desktop

## Quick Start

### Option 1: Using Docker Compose (Recommended)

```bash
# Build and start containers
docker-compose up -d --build

# Run migrations
docker-compose exec app php artisan migrate

# Generate app key (if needed)
docker-compose exec app php artisan key:generate
```

The API will be available at `http://localhost:8000`

### Option 2: Using Laravel Sail

```bash
# Start Docker containers
./vendor/bin/sail up -d

# Run database migrations
./vendor/bin/sail artisan migrate
```

The API will be available at `http://localhost`

## API Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/auth/register` | Register new user | No |
| POST | `/api/auth/login` | Login user | No |
| POST | `/api/auth/logout` | Logout user | Yes |
| GET | `/api/auth/user` | Get current user | Yes |

## Usage

### Register

```json
POST /api/auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

### Login

```json
POST /api/auth/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "message": "Login successful",
    "user": { ... },
    "access_token": "1|abc123...",
    "token_type": "Bearer"
}
```

### Protected Routes

Add the token to the `Authorization` header:

```
Authorization: Bearer 1|abc123...
```

### Get User

```
GET /api/auth/user
Authorization: Bearer YOUR_TOKEN
```

### Logout

```
POST /api/auth/logout
Authorization: Bearer YOUR_TOKEN
```

## Project Structure

```
authentication/
├── app/
│   ├── Http/Controllers/Api/
│   │   └── AuthController.php    # Auth logic
│   └── Models/
│       └── User.php              # User model with HasApiTokens
├── docker/
│   └── nginx/
│       └── default.conf          # Nginx configuration
├── routes/
│   └── api.php                   # API routes
├── config/
│   └── sanctum.php               # Sanctum config
├── Dockerfile                    # PHP-FPM container
├── docker-compose.yml            # Docker Compose config
├── compose.yaml                  # Laravel Sail config
└── .env                          # Environment variables
```

## Docker Commands

### Docker Compose

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# Rebuild containers
docker-compose up -d --build

# Run artisan commands
docker-compose exec app php artisan <command>

# View logs
docker-compose logs -f

# Access MySQL
docker-compose exec mysql mysql -u sail -ppassword authentication
```

### Laravel Sail

```bash
# Start containers
./vendor/bin/sail up -d

# Stop containers
./vendor/bin/sail down

# Run artisan commands
./vendor/bin/sail artisan <command>

# Access MySQL
./vendor/bin/sail mysql
```

## Environment

Key `.env` settings for Docker:

```env
DB_HOST=mysql
DB_USERNAME=sail
DB_PASSWORD=password
```
