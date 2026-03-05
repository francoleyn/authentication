# Laravel Sanctum Authentication API

A REST API for user authentication using Laravel Sanctum with Laravel Sail (Docker).

## Requirements

- Docker Desktop

## Quick Start

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

## Usage Examples

### Register

```
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

```
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

Add the token to the Authorization header:

```
Authorization: Bearer 1|abc123...
```

## Sail Commands

```bash
# Start containers
./vendor/bin/sail up -d

# Stop containers
./vendor/bin/sail down

# Run artisan commands
./vendor/bin/sail artisan <command>

# Run composer
./vendor/bin/sail composer <command>

# Access MySQL
./vendor/bin/sail mysql

# View logs
./vendor/bin/sail logs
```

## After Cloning

```bash
# Copy environment file
cp .env.example .env

# Start Sail
./vendor/bin/sail up -d

# Install dependencies
./vendor/bin/sail composer install

# Generate app key
./vendor/bin/sail artisan key:generate

# Run migrations
./vendor/bin/sail artisan migrate
```

## Database Credentials

| Setting | Value |
|---------|-------|
| Host | mysql |
| Database | authentication |
| Username | sail |
| Password | password |
