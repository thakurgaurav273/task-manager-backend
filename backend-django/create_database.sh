#!/bin/bash
# Script to create MySQL database for Django

echo "🔧 Creating MySQL database 'taskmanager'..."
echo ""

# Read password from .env file if it exists
if [ -f .env ]; then
    DB_PASSWORD=$(grep "^DB_PASSWORD=" .env | cut -d '=' -f2)
    DB_USER=$(grep "^DB_USERNAME=" .env | cut -d '=' -f2 | head -1)
    DB_USER=${DB_USER:-root}
else
    DB_PASSWORD=""
    DB_USER="root"
fi

if [ -z "$DB_PASSWORD" ]; then
    echo "⚠️  No password found in .env file"
    echo "Attempting to create database without password..."
    mysql -u "$DB_USER" -e "CREATE DATABASE IF NOT EXISTS taskmanager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
    if [ $? -eq 0 ]; then
        echo "✅ Database 'taskmanager' created successfully!"
    else
        echo "❌ Failed. Please run manually:"
        echo "   mysql -u root -p -e \"CREATE DATABASE IF NOT EXISTS taskmanager;\""
    fi
else
    mysql -u "$DB_USER" -p"$DB_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS taskmanager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
    if [ $? -eq 0 ]; then
        echo "✅ Database 'taskmanager' created successfully!"
    else
        echo "❌ Failed. Please check your password in .env file"
        echo "   Or run manually:"
        echo "   mysql -u root -p -e \"CREATE DATABASE IF NOT EXISTS taskmanager;\""
    fi
fi
