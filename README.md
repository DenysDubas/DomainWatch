# Domain Monitor

Admin panel for automated domain availability monitoring.

## Stack

- **Backend**: Laravel 12, PHP 8.2, MySQL 8
- **Frontend**: Vue 3, Vite, TailwindCSS, Pinia
- **Infrastructure**: Docker, Nginx
- **Deploy**: Frontend → GitHub Pages | Backend → Railway / Docker

## Architecture

```
Controller (Portal) → Service → Repository → Model
                              ↘ DTO
```

Custom names used:
| Layer | Class |
|---|---|
| Controller | `AuthPortal`, `DomainPortal`, `MonitorPortal` |
| Repository | `DomainRepo`, `CheckLogRepo` |
| Service | `DomainService`, `PingService` |
| DTO | `DomainPayload`, `PingResultPayload` |
| Job | `DomainPingJob` |
| Command | `ExecuteDomainChecks` |

## Quick Start (Docker)

```bash
# 1. Clone and enter
cd domain-monitoring

# 2. Bootstrap everything
make setup

# 3. Start frontend dev server
make frontend-install
make frontend-dev
```

Visit `http://localhost:5173` for the frontend, `http://localhost:8000` for the API.

## Environment

### Backend (`backend/.env`)

Key variables:
```
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173
DB_HOST=mysql
DB_DATABASE=domain_monitor
DB_USERNAME=dmuser
DB_PASSWORD=dmpassword
QUEUE_CONNECTION=database
```

For email notifications (optional):
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=xxx
MAIL_PASSWORD=xxx
```

### Frontend (`frontend/.env`)

```
VITE_API_URL=http://localhost:8000
```

For GitHub Pages set `VITE_API_URL` in repository secrets (see CI workflow).

## Deploy (Frontend + Backend)

See **[DEPLOY-RAILWAY.md](./DEPLOY-RAILWAY.md)** for full demo deployment guide (Railway + GitHub Pages).

Frontend deploys automatically via `.github/workflows/deploy-frontend.yml` on push to `main`.
Backend tests run via `.github/workflows/ci.yml`.

## API Endpoints

```
POST   /api/v1/register
POST   /api/v1/login
POST   /api/v1/logout            [auth]
GET    /api/v1/me                [auth]

GET    /api/v1/domains           [auth]
POST   /api/v1/domains           [auth]
GET    /api/v1/domains/:id       [auth]
PUT    /api/v1/domains/:id       [auth]
DELETE /api/v1/domains/:id       [auth]

GET    /api/v1/domains/:id/logs  [auth]
POST   /api/v1/domains/:id/check [auth]  ← manual trigger (queued, 202)
```

## How Monitoring Works

1. Laravel Scheduler runs `domains:check` every minute (via `scheduler` container)
2. The command fetches all active domains where `TIMESTAMPDIFF(MINUTE, last_checked_at, NOW()) >= check_interval`
3. Dispatches a `DomainPingJob` per domain to the queue
4. `queue` container processes jobs via `PingService`
5. Results are saved to `check_logs` table
6. If domain status changed (up→down or down→up), a `DomainStatusChanged` event sends email
7. `check-logs:prune` runs daily to remove logs older than 90 days

## Useful Commands

```bash
make logs            # view all container logs
make shell-php       # bash into the PHP container
make migrate         # run migrations
make fresh           # fresh migrate (destroys data)
make check-domains   # manually trigger domain checks
make tinker          # Laravel Tinker REPL
```
