#!/usr/bin/env bash
# Creates the cafe_lounge database using local mysql client (Unix)
# Requires mysql client installed and in PATH
mysql -u root -p123456 -e "CREATE DATABASE IF NOT EXISTS cafe_lounge;"
echo "Database created or already exists."
