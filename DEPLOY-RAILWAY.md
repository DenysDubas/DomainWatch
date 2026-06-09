# Deploy: Railway (backend) + GitHub Actions (frontend)

**Git** — хранит код.  
**GitHub Actions** — при `push` собирает фронт и публикует на **GitHub Pages** (бесплатно).  
**Railway** — backend + MySQL + workers (подключается к тому же репо).

---

## Схема

```
git push → main
    ├── GitHub Actions  →  frontend/dist  →  GitHub Pages
    └── Railway (watch) →  backend Docker →  API + MySQL + workers
```

---

## 1. Залить код на GitHub

```bash
cd domain-monitoring
git init
git add .
git commit -m "Domain monitoring app"
git remote add origin https://github.com/YOUR_USER/domain-monitoring.git
git push -u origin main
```

После этого у ревьюера будет:
- ссылка на **репозиторий** (код)
- ссылка на **demo** (живое приложение)

---

## 2. Railway — Backend + MySQL + Workers

### 2.1 Создать проект

1. [railway.app](https://railway.app) → **New Project**
2. **Deploy from GitHub repo** → выбрать репозиторий
3. Railway создаст первый сервис — настроить его как **API (web)**

### 2.2 MySQL

1. В проекте: **+ New** → **Database** → **MySQL**
2. Дождаться статуса `Active`

### 2.3 Сервис `api` (web)

**Settings → General:**
- Root Directory: `backend`
- Watch Paths: `backend/**`

**Settings → Deploy:**
- Builder: Dockerfile (`backend/Dockerfile`)
- Healthcheck: `/up`

**Variables** (Settings → Variables):

```env
APP_NAME=Domain Monitor
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...          # сгенерировать локально: php artisan key:generate --show
APP_URL=https://YOUR-API.up.railway.app

FRONTEND_URL=https://YOUR-GITHUB-USERNAME.github.io/YOUR-REPO-NAME

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

QUEUE_CONNECTION=database
SESSION_DRIVER=cookie
CACHE_DRIVER=file
LOG_CHANNEL=stderr
MAIL_MAILER=log

RAILWAY_SERVICE_ROLE=web
```

**Networking → Generate Domain** → скопировать URL → вставить в `APP_URL`

**Settings → Deploy → Healthcheck Path:** `/up` (только для `api`!)

### 2.4 Сервис `worker` (queue)

1. **+ New** → **GitHub Repo** → тот же репозиторий
2. **Settings → Source:** Root Directory = `backend`, Builder = Dockerfile
3. **Settings → Deploy → Healthcheck:** **отключить** (worker не отвечает на HTTP — иначе `Healthcheck failed`)
4. **Settings → Networking:** Public Networking — **off**
5. **Variables** — скопировать **все** переменные с `api` (не из `.env.example`!):

```env
# обязательно Railway MySQL references, НЕ DB_HOST=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

APP_KEY=...          # тот же что у api
APP_ENV=production
QUEUE_CONNECTION=database
# ... остальные как у api

RAILWAY_SERVICE_ROLE=worker
```

> **Ошибка `getaddrinfo for mysql failed`** = в Variables стоит `DB_HOST=mysql` (это только для docker-compose). Замени на `${{MySQL.MYSQLHOST}}`.

### 2.5 Сервис `scheduler` (cron)

1. **+ New** → **GitHub Repo** → тот же репозиторий
2. Root Directory: `backend`, Builder = Dockerfile
3. **Healthcheck:** **отключить**
4. Public Networking — **off**
5. **Variables** — те же что у `api` + `RAILWAY_SERVICE_ROLE=scheduler`

### 2.6 Проверка API

```bash
curl https://YOUR-API.up.railway.app/up
# {"status":"ok"} или 200

curl -X POST https://YOUR-API.up.railway.app/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Demo","email":"demo@test.com","password":"password123","password_confirmation":"password123"}'
```

---

## 3. GitHub Actions — Frontend (GitHub Pages)

Workflow уже в репо: `.github/workflows/deploy-frontend.yml`

### 3.1 Включить Pages

1. GitHub repo → **Settings** → **Pages**
2. **Source:** GitHub Actions
3. Сохранить

### 3.2 Secret для API URL

**Settings** → **Secrets and variables** → **Actions** → **New repository secret:**

```
Name:  VITE_API_URL
Value: https://YOUR-API.up.railway.app
```

### 3.3 Первый деплой

```bash
git push origin main
```

**Actions** tab → workflow `Deploy Frontend` → дождаться зелёного статуса.

Frontend URL:
```
https://YOUR_USER.github.io/domain-monitoring/
```
(имя репозитория в пути)

### 3.4 CORS на Railway

В сервисе `api` обновить:

```env
FRONTEND_URL=https://YOUR_USER.github.io/domain-monitoring
```

Redeploy `api`.

### 3.5 Локальная сборка с тем же base (опционально)

```bash
cd frontend
VITE_API_URL=https://your-api.railway.app VITE_BASE_PATH=/domain-monitoring/ npm run build
```

---

## 4. Итоговые ссылки для сдачи

```markdown
## Demo
- Frontend: https://YOUR_USER.github.io/domain-monitoring/
- API:      https://YOUR-API.up.railway.app

## Repo
- https://github.com/YOUR_USER/domain-monitoring
```

---

## 5. Как это работает

| Компонент | Что делает |
|---|---|
| **Git push** | Триггер для Actions и Railway |
| **GitHub Actions** | `npm ci` → `npm run build` → upload на Pages |
| **Railway** | Собирает Docker из `backend/`, запускает API/workers |
| **Ревьюер** | Repo + live demo без локального Docker |

---

## Альтернатива: Netlify (опционально)

Если не хочешь GitHub Pages — Netlify тоже подключается к GitHub:
- Base directory: `frontend`
- Build: `npm run build`
- Env: `VITE_API_URL`
- `VITE_BASE_PATH` не нужен (корень домена)

---

## Troubleshooting

| Проблема | Решение |
|---|---|
| CORS error | `FRONTEND_URL` в Railway = точный GitHub Pages URL (без слэша в конце) |
| 500 на API | Railway logs → проверить `APP_KEY`, DB vars |
| Домены не проверяются | Убедиться что `worker` + `scheduler` сервисы **Running** |
| Миграции | Автоматически при старте web/worker (entrypoint) |
| Cold start | Railway free tier — первый запрос может быть медленным |

---

## Локальная генерация APP_KEY

```bash
cd backend
php artisan key:generate --show
```

Скопировать значение в Railway Variables.
