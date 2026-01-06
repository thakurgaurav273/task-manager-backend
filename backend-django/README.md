# Django Backend - Overdue Task Handler

This Django service handles overdue task logic for the Task & Project Management System.

## Requirements

- Python >= 3.8
- MySQL >= 5.7
- Django 6.0+

## Installation

1. **Create and activate virtual environment:**
   ```bash
   python3 -m venv venv
   source venv/bin/activate  # On Windows: venv\Scripts\activate
   ```

2. **Install dependencies:**
   ```bash
   pip install django djangorestframework django-cors-headers pymysql requests
   ```

3. **Configure environment variables:**
   
   Set these environment variables or update `settings.py`:
   ```bash
   export DB_DATABASE=taskmanager
   export DB_USERNAME=root
   export DB_PASSWORD=your_password
   export DB_HOST=localhost
   export DB_PORT=3306
   ```

   Or update `taskmanager/settings.py` directly with your database credentials.

4. **Run migrations (if needed):**
   ```bash
   python manage.py migrate
   ```

## Running the Server

```bash
python manage.py runserver 8001
```

The service will be available at `http://localhost:8001`

## API Endpoints

### Overdue Tasks
- `POST /api/overdue/mark` - Mark overdue tasks (called by Laravel)

### Task Validation
- `POST /api/tasks/{id}/validate-status` - Validate task status changes

### Health Check
- `GET /api/health` - Health check endpoint

## Business Rules Implemented

1. **Overdue Task Detection:**
   - Tasks with `due_date` in the past and status not `DONE` are marked as `OVERDUE`

2. **Status Change Rules:**
   - Overdue tasks cannot move back to `IN_PROGRESS`
   - Only Admins can close overdue tasks (change status to `DONE`)

3. **Automatic Marking:**
   - Tasks past their due date are automatically marked as `OVERDUE` when status is changed

## Database Connection

This service connects to the same MySQL database as the Laravel backend. It directly queries the `tasks` table to:
- Mark tasks as OVERDUE
- Validate status changes
- Enforce business rules

## Notes

- This service is called by the Laravel backend when tasks are created or updated
- The service uses raw SQL queries to interact with the shared database
- Make sure the database credentials match your Laravel `.env` configuration

