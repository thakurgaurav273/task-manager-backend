#!/bin/bash
# Script to help set up MySQL database for Django

echo "🔧 Django MySQL Database Setup"
echo ""
echo "You need to provide your MySQL root password to create the database."
echo "If you don't know your MySQL root password, you can reset it."
echo ""
read -sp "Enter MySQL root password (or press Enter to skip): " MYSQL_PASSWORD
echo ""

if [ -z "$MYSQL_PASSWORD" ]; then
    echo "⚠️  No password provided. You'll need to:"
    echo "   1. Find your MySQL root password"
    echo "   2. Update backend-django/.env with DB_PASSWORD=your_password"
    echo "   3. Create the database manually:"
    echo "      mysql -u root -p -e 'CREATE DATABASE IF NOT EXISTS taskmanager;'"
else
    # Try to create database
    mysql -u root -p"$MYSQL_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS taskmanager;" 2>/dev/null
    if [ $? -eq 0 ]; then
        echo "✅ Database 'taskmanager' created successfully!"
        # Update .env file with password
        if [ -f .env ]; then
            sed -i '' "s/^DB_PASSWORD=$/DB_PASSWORD=$MYSQL_PASSWORD/" .env
            echo "✅ Updated .env file with database password"
        fi
    else
        echo "❌ Failed to create database. Please check your password."
        echo "   You can manually update backend-django/.env with:"
        echo "   DB_PASSWORD=$MYSQL_PASSWORD"
    fi
fi
