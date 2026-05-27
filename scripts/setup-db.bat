@echo off
REM Creates the cafe_lounge database using local mysql client (Windows)
REM Requires mysql client on PATH (mysql.exe)
mysql -u root -p123456 -e "CREATE DATABASE IF NOT EXISTS cafe_lounge;"
echo Database created or already exists.
