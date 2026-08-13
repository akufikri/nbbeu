# NBBEU — North Borneo Banking Executive Union

Website & membership management system for North Borneo Banking Executive Union.

## Tech Stack

**Backend:** PHP 8.3 · Laravel 13 · Filament 5 · MySQL  
**Frontend:** Tailwind CSS 3 · Alpine.js · Vite 8  
**Queue:** Database driver · Scheduled jobs  
**PDF:** DomPDF · endroid/qr-code  
**Storage:** Local disk (PDFs) · Cloudinary (photos)  
**Auth:** Laravel Breeze · Firebase Google Sign-In · Spatie Permission  
**Payments:** Toyyibpay gateway

## Quick Start

```bash
# Clone
git clone <repo-url>
cd nbbeu

# Install
composer install
npm install

# Environment
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate
php artisan db:seed

# Queue worker (required for approval flow)
php artisan queue:work

# Scheduler (add to crontab)
* * * * * php artisan schedule:run

# Dev
npm run dev
php artisan serve
```

## Documentation

| Document | Description |
|---|---|
| [Tech Stack](docs/TECH_STACK.md) | UI & backend technologies |
| [Third-Party](docs/THIRD_PARTY.md) | External service integrations |
| [Functions](docs/FUNCTIONS.md) | Features & routes |
| [Data Sets](docs/DATASETS.md) | Models & database schema |
| [Security](docs/SECURITY.md) | Auth, encryption, PDPA compliance |

## Architecture

- **Dual guard:** `web` (members) + `admin` (Filament)
- **Event-driven approval:** `UserApproved` → queued listeners → member number → card → certificate → email
- **Toyyibpay:** Bill creation → webhook callback → re-verify via API (never trust callback payload)
- **PDPA:** IC numbers & salaries encrypted at rest; IC hashed for lookup; masked display

## Environment Variables

See [`.env.example`](.env.example) for full list. Key variables:

```env
APP_NAME=NBBEU
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_DATABASE=nbbeu

TOYYIBPAY_SECRET_KEY=
TOYYIBPAY_CATEGORY_CODE=
TOYYIBPAY_BASE_URL=https://dev.toyyibpay.com

FIREBASE_CREDENTIALS=
CLOUDINARY_URL=
```

## License

Proprietary — see [LICENSE](LICENSE).
