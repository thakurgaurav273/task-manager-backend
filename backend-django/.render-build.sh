#!/bin/bash
# Render build script for Django

# Install dependencies
pip install -r requirements.txt

# Collect static files (if needed)
# python manage.py collectstatic --noinput

# Run migrations (optional - can be done in release phase)
# python manage.py migrate --noinput