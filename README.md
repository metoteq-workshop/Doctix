# Doctix — Enterprise SaaS Document, PDF, Image & AI Productivity Platform

Doctix is a full-stack, production-ready Laravel SaaS platform inspired by modern document productivity leaders such as PDFSimpli. It provides 60+ online PDF, Image, Office Document, AI-powered, and Developer/Productivity tools backed by a strict, concurrency-safe freemium quota system and multi-gateway subscription billing.

---

## 1. Core Freemium & Subscription Business Model

- **Free Plan**:
  - **4 Successful Uses Per Individual Tool Per Calendar Day**: Every tool has its own independent daily allowance (`Merge PDF` 4/day, `Compress PDF` 4/day, `PDF to Word` 4/day, etc.).
  - **Zero Failure Penalty**: Reserved slots are only committed when processing succeeds; failed jobs release their slot automatically.
  - **Concurrency & Idempotency Guard**: `UsageEnforcementService` uses database transactions, pessimistic row locking (`lockForUpdate()`), and unique `idempotency_key` enforcement so concurrent requests cannot bypass the 4-use limit.
  - **Automatic Midnight Reset**: Date-indexed queries (`usage_date` in `config('app.timezone')`) reset allowances automatically at midnight without deleting historical audit logs.
- **Monthly & Annual Premium Plans**:
  - Unlimited daily access across all 60+ tools.
  - Higher file upload limits (250 MB – 500 MB).
  - Priority processing queue and automated tax invoice generation.
  - Activated **exclusively** via verified server-side payment confirmations or HMAC-verified webhooks (Stripe, PayPal, Paystack, Flutterwave).

---

## 2. Seeded Demo Accounts

After running `php artisan migrate:fresh --seed`, the following accounts are ready for immediate testing:

| Role | Email | Password | Entitlement |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@doctix.io` | `Password123!` | Full Admin Panel + Unlimited Override |
| **Premium Subscriber** | `premium@doctix.io` | `Password123!` | Active Monthly Premium (Unlimited) |
| **Free Tier User** | `free@doctix.io` | `Password123!` | Free Plan (4 uses / tool / day) |

---

## 3. Local Installation & Quick Start

### Prerequisites
- PHP 8.3+ (with `pdo_mysql`, `pdo_sqlite`, `mbstring`, `openssl`, `gd`, `zip`, `fileinfo`, `curl`)
- Composer 2.x
- MySQL 8.0+ (or SQLite for automated testing)

### Step-by-Step Setup
```bash
# 1. Install PHP dependencies
composer install

# 2. Configure environment
cp .env.example .env
php artisan key:generate

# 3. Run database migrations and seed 60+ tools, plans, gateways & demo users
php artisan migrate --seed

# 4. Run the automated PHPUnit test suite
php artisan test

# 5. Start the local development server
php artisan serve
```

---

## 4. External Binary & API Adapter Configuration

Doctix includes native PHP processing engines (`setasign/fpdf`, `setasign/fpdi`, GD raster processing, XML Spreadsheet/Word generators, SVG QR/Barcode generators) that work out-of-the-box, plus configurable adapters in `config/doctix.php` and the Admin Panel (`/admin/settings`) for:
- **OpenAI / Google Gemini API**: Configure `OPENAI_API_KEY` in `.env` or encrypted via **Admin -> Settings** for live LLM completions across all 9 AI tools.
- **Ghostscript (`gs`) / LibreOffice (`soffice`) / Tesseract OCR (`tesseract`) / ClamAV (`clamscan`)**: Optional binary paths configurable in `.env` (`GHOSTSCRIPT_BINARY`, `LIBREOFFICE_BINARY`, `TESSERACT_BINARY`, `CLAMAV_BINARY`).

See [DEPLOYMENT.md](file:///c:/xampp/htdocs/Doctix/DEPLOYMENT.md) for full Linux production deployment instructions (Nginx, Supervisor queue workers, Cron scheduler, S3 storage, and SSL).
