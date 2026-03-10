# Smart Home Security API

A Laravel 12 backend service that registers smart home devices, stores device activity, detects suspicious activity, and returns security alerts through a secure API.

---

## Requirements

- PHP 8.2+
- Composer
- SQLite (default) or MySQL/MariaDB

---

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
```

---

## Running

```bash
php artisan serve
```

API is available at `http://127.0.0.1:8000/api`.

---

## API Authentication

All API endpoints (except `/api/login`) are protected by **Laravel Sanctum** token-based authentication.

### 1. Obtain a token

**`POST /api/login`**

Request body (JSON):

```json
{
  "email": "user@example.com",
  "password": "secret"
}
```

Successful response (`200`):

```json
{
  "token": "1|abc123...",
  "token_type": "Bearer"
}
```

Invalid credentials response (`401`):

```json
{
  "message": "The provided credentials are incorrect."
}
```

### 2. Use the token

Include the token in the `Authorization` header on every subsequent request:

```
Authorization: Bearer 1|abc123...
```

Example:

```bash
curl -H "Authorization: Bearer 1|abc123..." \
     http://127.0.0.1:8000/api/health
```

### 3. Revoke the token (logout)

**`POST /api/logout`** _(requires valid token)_

```bash
curl -X POST \
     -H "Authorization: Bearer 1|abc123..." \
     http://127.0.0.1:8000/api/logout
```

Response (`200`):

```json
{
  "message": "Logged out successfully."
}
```

### 4. Unauthenticated requests

Any request to a protected endpoint without a valid token returns:

**`401 Unauthorized`**

```json
{
  "message": "Unauthenticated."
}
```

---

## Creating a test user

Use Artisan Tinker to create a user for local testing:

```bash
php artisan tinker
```

```php
\App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
]);
```

---

## Architecture

- **Framework:** Laravel 12
- **Database:** SQLite (configurable via `DB_CONNECTION` in `.env`)
- **Authentication:** Laravel Sanctum (API token-based)
- **Models:** `Device`, `DeviceActivity`, `SecurityAlert`, `User`

---

## Design Decisions

- Sanctum API tokens chosen over Basic Auth for stateless, revocable, production-ready token flows.
- SQLite used by default for portability; swapping to MySQL/MariaDB requires only updating `.env`.
- All API routes live under `/api` prefix (registered via `bootstrap/app.php`).
- Unauthenticated requests to API routes always return `401` JSON regardless of `Accept` header.
