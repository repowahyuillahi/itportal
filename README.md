# IT Portal Helpdesk

Aplikasi **IT Helpdesk / Ticketing** menggunakan PHP Native + Tabler.io (Bootstrap 5).

## Requirements

- PHP 8.0+
- MySQL / MariaDB
- Apache (mod_rewrite) — XAMPP atau aaPanel

## Instalasi (XAMPP)

1. **Clone / copy** folder `itportal` ke `C:\xampp\htdocs\itportal`

2. **Buat database** `itportal` di phpMyAdmin

3. **Import SQL:**
   ```bash
   # Import schema utama
   mysql -u root itportal < itportal.sql

   # Import tabel ticketing
   mysql -u root itportal < database/schema_addon.sql
   ```

4. **Copy & edit `.env`:**
   ```bash
   cp .env.example .env
   ```
   Edit `DB_USER`, `DB_PASS`, `APP_URL` sesuai environment.

5. **Pastikan mod_rewrite aktif** di Apache (uncomment `LoadModule rewrite_module` di `httpd.conf`)

6. **Buka browser:** `http://localhost/itportal/login`

7. **Login:**
   - Username: `admin`
   - Password: `admin123`

## Instalasi (aaPanel / Production)

1. Upload file ke web root
2. Buat database MySQL, import `itportal.sql` lalu `database/schema_addon.sql`
3. Copy `.env.example` ke `.env`, edit konfigurasi
4. Untuk **Nginx**, tambahkan rewrite rule:
   ```nginx
   location /itportal {
       try_files $uri $uri/ /itportal/index.php?$query_string;
   }
   ```
5. Pastikan folder `storage/uploads` dan `storage/logs` writable:
   ```bash
   chmod -R 775 storage/
   ```

## Fitur

- ✅ Dashboard dengan statistik ticket
- ✅ CRUD Ticket (create, view, edit, reply)
- ✅ Assign ticket ke staff
- ✅ Update status ticket
- ✅ Upload attachment
- ✅ Public tracking link (tanpa login)
- ✅ WA update link (stub)
- ✅ Manajemen user (admin)
- ✅ Role-based access (admin, staff, user, dealer)
- ✅ CSRF protection
- ✅ Audit log
