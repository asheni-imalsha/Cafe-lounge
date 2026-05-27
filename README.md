# Cafe Lounge

Project scaffold for Cafe Lounge application (PHP backend + Tailwind frontend).

Quick start

- Edit `config/application.properties` with your DB settings.
- Create the database and seed schema using the script in `scripts/` or the SQL in `sql/migrations/init.sql`.

Provided structure

- server/ — PHP backend (MVC: `app/`, `views/`, `public/`, `config/`, `sql/`, `src/`)
- client/ — frontend app placeholder (for future SPA)
- scripts/ — helper scripts to setup DB
- docs/ — documentation
- .github/workflows — CI
- logs/ — runtime logs

JDBC connection

You provided: `jdbc:mysql://127.0.0.1:3306/?user=root password=123456`

Recommended (set `jdbc.url` in `config/application.properties`):

`jdbc.url=jdbc:mysql://127.0.0.1:3306/cafe_lounge?useSSL=false&serverTimezone=UTC`
`jdbc.username=root`
`jdbc.password=123456`

Security note: prefer environment variables or a secrets manager for production.
