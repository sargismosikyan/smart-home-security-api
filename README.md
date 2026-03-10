# Smart Home Security API

A Laravel 12 backend service that registers smart home devices, stores device activity, detects suspicious activity, and raises security alerts through a secure REST API.

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
touch database/database.sqlite   # SQLite only
php artisan migrate
```

---

## Running

```bash
php artisan serve
```

API base URL: `http://127.0.0.1:8000/api`

Interactive API docs: `http://127.0.0.1:8000/docs/api`

---

## Architecture

### Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 |
| Auth | Laravel Sanctum (API tokens) |
| Database | SQLite (default) / MySQL / MariaDB |
| API docs | [dedoc/scramble](https://scramble.dedoc.co) — auto-generated, zero annotations |

### Components

- **Models:** `Device`, `DeviceActivity`, `SecurityAlert`, `User`
- **Controllers:** `DeviceController`, `DeviceActivityController`, `SecurityAlertController`, `AuthController`
- **Service:** `SuspiciousActivityDetector` — evaluates rules after each activity is stored
- **DTO:** `DetectionResult` — carries alert type, severity, description, and metadata between the service and the controller
- **Enum:** `DeviceType` — restricts the `type` field to known values

### Request flow

```
Client
  │
  ├── POST /api/devices           → DeviceController::store()
  │                                  Validate → Device::create()
  │
  └── POST /api/device-activities → DeviceActivityController::store()
                                     Validate
                                     ┌─ DB transaction ──────────────────────┐
                                     │  DeviceActivity::create()             │
                                     │  SuspiciousActivityDetector::detect() │
                                     │  if suspicious → SecurityAlert::create│
                                     └───────────────────────────────────────┘
                                     Return 201

  GET /api/security-alerts        → SecurityAlertController::index()
                                     Filter by device_id / severity / resolved
                                     Return paginated list

  PATCH /api/security-alerts/{id} → SecurityAlertController::update()
                                     Set resolved_at = now() or null
```

---

## API Authentication

All endpoints except `POST /api/login` require a **Bearer token** from Laravel Sanctum.

### Obtain a token

```bash
curl -s -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'
```

Response `200`:
```json
{ "token": "1|abc123...", "token_type": "Bearer" }
```

### Use the token

```
Authorization: Bearer 1|abc123...
```

### Logout (revoke token)

```bash
curl -s -X POST http://127.0.0.1:8000/api/logout \
  -H "Authorization: Bearer 1|abc123..."
```

### Unauthenticated requests → `401`

```json
{ "message": "Unauthenticated." }
```

---

## Creating a test user

```bash
php artisan tinker
```

```php
\App\Models\User::create([
    'name'     => 'Admin',
    'email'    => 'admin@example.com',
    'password' => bcrypt('password'),
]);
```

---

## API Documentation

Interactive Swagger UI is served automatically by [Scramble](https://scramble.dedoc.co) (no annotations required):

| URL | Description |
|---|---|
| `GET /docs/api` | Interactive UI (Swagger-like) |
| `GET /docs/api.json` | Raw OpenAPI JSON |

Available in local/dev environments only.

---

## Design Decisions

### Authentication: Sanctum tokens vs Basic Auth

Sanctum API tokens were chosen over HTTP Basic Auth because:
- Tokens are stateless and revocable per client without changing credentials.
- They are the standard Laravel pattern for SPA and mobile API authentication.
- Basic Auth sends credentials on every request; tokens are a single credential exchange.

### Suspicious activity rules

Four rules are evaluated in priority order inside `SuspiciousActivityDetector` (all thresholds are named constants, easy to tune):

| Priority | Rule | Trigger | Severity |
|---|---|---|---|
| 1 | High-risk event | `event_type` in `intrusion_detected`, `tamper_detected`, `unauthorized_access` | `critical` |
| 2 | Repeated failures | ≥ 5 `connection_failed` events for same device in 10 minutes | `high` |
| 3 | Activity burst | ≥ 10 any events for same device in 5 minutes | `medium` |
| 4 | Off-hours activity | Activity between 00:00 and 05:59 | `low` |

First matching rule wins. Only one `SecurityAlert` is created per activity.

### Generic device model

A single `Device` model with a `type` field (validated against the `DeviceType` enum: `camera`, `sensor`, `lock`, `alarm`, `doorbell`, `other`) keeps the schema flat and extensible. New device types require only a new enum case — no schema migration.

### Single-environment / multi-tenant

Currently all devices and alerts share one namespace. To extend to multi-tenancy, add a `tenant_id` (or `user_id`) FK to `devices` and scope all queries through it. Routes would filter by the authenticated user's tenant automatically via a model scope or middleware.

### Detection: synchronous vs async

Detection runs synchronously inside the same DB transaction as the activity. For high-throughput deployments, move `SuspiciousActivityDetector::detect()` to a queued job — `QUEUE_CONNECTION=database` is already configured in `.env`.

---

## Endpoints summary

| Method | Path | Auth | Description |
|---|---|---|---|
| POST | `/api/login` | — | Obtain Bearer token |
| POST | `/api/logout` | Bearer | Revoke current token |
| GET | `/api/health` | Bearer | Health check |
| POST | `/api/devices` | Bearer | Register a device |
| PATCH | `/api/devices/{id}` | Bearer | Update a device |
| POST | `/api/device-activities` | Bearer | Store activity + run detection |
| GET | `/api/security-alerts` | Bearer | List alerts (filterable, paginated) |
| GET | `/api/security-alerts/{id}` | Bearer | Show single alert |
| PATCH | `/api/security-alerts/{id}` | Bearer | Resolve / unresolve alert |

See `/docs/api` for full request/response schemas.
