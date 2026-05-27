# Cafe Lounge (PHP Tailwind)

Quick setup

1. Ensure PHP 7.4+ or 8.x and MySQL/MariaDB are installed.
2. Point your webserver document root to the `public/` folder.
3. Import the DB schema:

```sql
-- from repository root
mysql -u root -p123456 < sql/php_init.sql
```

4. Edit `config/config.php` if you use different DB credentials.
5. Visit `/register.php` to create a user, then use bookings and menu pages.

Notes
- Tailwind is loaded via CDN in `public/header.php` for quick prototyping.
- For production, compile Tailwind and secure DB credentials with env vars.
