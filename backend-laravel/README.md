# Laravel Backend - Task & Project Management System

This is the main Laravel backend API for the Task & Project Management System.

## Requirements

- PHP >= 8.2
- Composer
- MySQL >= 5.7
- Laravel 12+

## Installation

1. **Install dependencies:**
   ```bash
   composer install
   ```

2. **Configure environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Update `.env` file with your database credentials:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=taskmanager
   DB_USERNAME=root
   DB_PASSWORD=your_password
   
   # Django service URL (for overdue task handling)
   DJANGO_API_URL=http://localhost:8001
   ```

4. **Run migrations:**
   ```bash
   php artisan migrate
   ```

5. **Seed the database:**
   ```bash
   php artisan db:seed
   ```

## Running the Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

## API Endpoints

### Authentication
- `POST /api/login` - Login user
- `POST /api/logout` - Logout user (requires auth)
- `GET /api/user` - Get current user (requires auth)

### Projects
- `GET /api/projects` - List all projects (requires auth)
- `GET /api/projects/{id}` - Get project details (requires auth)
- `POST /api/projects` - Create project (admin only)

### Tasks
- `GET /api/tasks` - List tasks (requires auth)
- `GET /api/tasks?project_id={id}` - List tasks by project (requires auth)
- `GET /api/tasks/{id}` - Get task details (requires auth)
- `POST /api/tasks` - Create task (admin only)
- `PUT /api/tasks/{id}` - Update task (requires auth)

### Users
- `GET /api/users` - List all users (admin only)

## Test Credentials

After running the seeder, you can use these credentials:

**Admin:**
- Email: `admin@taskflow.com`
- Password: `password123`

**Regular Users:**
- Email: `john@taskflow.com`
- Password: `password123`
- Email: `jane@taskflow.com`
- Password: `password123`
- Email: `bob@taskflow.com`
- Password: `password123`

## JWT Authentication

This API uses JWT (JSON Web Tokens) for authentication. Include the token in the Authorization header:

```
Authorization: Bearer {your_token_here}
```

## Response Format

All API responses follow this format:

```json
{
  "success": true,
  "data": { ... },
  "message": "Optional message"
}
```

Error responses:

```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Validation error"]
  }
}
```

## Database Structure

- **users** - User accounts (admin/user roles)
- **projects** - Projects
- **tasks** - Tasks assigned to users
- **project_user** - Many-to-many relationship between projects and users

## Notes

- The Laravel backend communicates with the Django service for overdue task handling
- Make sure the Django service is running on port 8001 (or update `DJANGO_API_URL` in `.env`)
