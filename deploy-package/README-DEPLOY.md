# RFI Access Control — Production Deployment

## 1) Upload backend files (to rfibackend.ronakfire.com backend root, e.g. /public_html/backend or where app lives)

Copy the CONTENTS of `backend/` into the Laravel app root, preserving paths:

### New files
- `config/access.php`
- `app/Support/Access.php`
- `app/Http/Controllers/Api/V1/AccessControlController.php`
- `app/Console/Commands/AccessSetupCommand.php`
- `database/seeders/CanonicalPermissionsSeeder.php`
- `database/migrations/2026_08_27_000000_add_is_super_admin_to_users_table.php`

### Overwrite these existing files (from `backend-overwrite/`)
- `app/Http/Controllers/Api/V1/AuthController.php`
- `app/Models/User.php`
- `app/Providers/AuthServiceProvider.php`
- `app/Services/AttendanceService.php`   <-- IMPORTANT: old file had a stray 'A' before `<?php` (would crash the app); the new copy is fixed
- `routes/api.php`

## 2) Upload frontend build (to rfi.ronakfire.com)
Upload the contents of `frontend/dist/*` to the frontend web root (replaces old bundle).

## 3) Run commands on the production server (SSH / hosting terminal)

```bash
cd /path/to/backend

# Add the is_super_admin column
php artisan migrate --force

# Seed canonical permissions, copy existing legacy grants, and mark a Super Admin
php artisan access:setup --copy

# Clear Laravel caches (config / routes / views)
php artisan optimize:clear

# Clear PHP opcache (this is what makes PHP pick up the new files):
#   open in browser:  https://rfibackend.ronakfire.com/opcache-reset.php
#   or from CLI if available:  php -r "opcache_reset();"
```

> `access:setup` is safe to run repeatedly. It:
>  - creates all canonical `module.action` permissions (missing rows only),
>  - copies grants from old names (e.g. `view_suppliers`) to new canonical names (`suppliers.view`),
>  - marks the first Admin-role user as Super Admin if none is flagged yet.
>
> To explicitly choose who is the Super Admin, run:
> `php artisan access:setup --email someone@ronakfire.com`

## 4) Verify
- Log in as a Super Admin → the new **Access Control** page appears in the System menu (sidebar).
- It should no longer 404: `GET /api/v1/access-control/definitions` should return 200 JSON.
- Non-admin employees see only modules they've been granted (UI + backend enforced).
