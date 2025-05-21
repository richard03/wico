# Wico REST API

This is a REST API for the Wico mobile application. The API provides endpoints for user management, feelings, desires, and contacts.

## Database Structure

The application uses MySQL database with the following tables:

### Users
- id (UNIQUE, NOT NULL, auto increment)
- key (bigint)
- name (NULLABLE)
- google_id (UNIQUE, NOT NULL)
- username (UNIQUE, NULLABLE)
- profile_picture_url (NULLABLE)
- email (UNIQUE, NOT NULL)
- session_token (VARCHAR, UNIQUE, NULLABLE)
- session_token_expiry (TIMESTAMP, NULLABLE)
- created_at
- updated_at
- last_login
- phone
- gps

### Feelings
- id
- key
- user_id
- feeling
- time

### Desires
- id
- key
- user_id
- desire
- comment
- time

### Contacts
- id
- user_1
- user_2

## API Endpoints

### Authentication
All endpoints except user registration require a Bearer token in the Authorization header.

### Users

#### Register/Login User
```
POST /api/users.php
Content-Type: application/json

{
    "google_id": "string",
    "email": "string",
    "name": "string (optional)",
    "username": "string (optional)",
    "profile_picture_url": "string (optional)",
    "phone": "string (optional)",
    "gps": "string (optional)"
}
```

#### Get User Profile
```
GET /api/users.php
Authorization: Bearer <session_token>
```

#### Update User Profile
```
PUT /api/users.php
Authorization: Bearer <session_token>
Content-Type: application/json

{
    "name": "string (optional)",
    "username": "string (optional)",
    "profile_picture_url": "string (optional)",
    "phone": "string (optional)",
    "gps": "string (optional)"
}
```

#### Logout
```
DELETE /api/users.php
Authorization: Bearer <session_token>
```

### Feelings

#### Create Feeling
```
POST /api/feelings.php
Authorization: Bearer <session_token>
Content-Type: application/json

{
    "feeling": "string"
}
```

#### Get Feelings
```
GET /api/feelings.php
Authorization: Bearer <session_token>

Optional query parameters:
- limit: number
- start_date: YYYY-MM-DD
- end_date: YYYY-MM-DD
```

#### Delete Feeling
```
DELETE /api/feelings.php?id=<feeling_id>
Authorization: Bearer <session_token>
```

### Desires

#### Create Desire
```
POST /api/desires.php
Authorization: Bearer <session_token>
Content-Type: application/json

{
    "desire": "string",
    "comment": "string (optional)"
}
```

#### Get Desires
```
GET /api/desires.php
Authorization: Bearer <session_token>

Optional query parameters:
- limit: number
- start_date: YYYY-MM-DD
- end_date: YYYY-MM-DD
```

#### Delete Desire
```
DELETE /api/desires.php?id=<desire_id>
Authorization: Bearer <session_token>
```

### Contacts

#### Add Contact
```
POST /api/contacts.php
Authorization: Bearer <session_token>
Content-Type: application/json

{
    "user_id": number
}
```

#### Get Contacts
```
GET /api/contacts.php
Authorization: Bearer <session_token>
```

#### Remove Contact
```
DELETE /api/contacts.php?user_id=<user_id>
Authorization: Bearer <session_token>
```

## Response Format

All responses are in JSON format:

### Success Response
```json
{
    "success": true,
    "message": "string",
    "data": object | array | null
}
```

### Error Response
```json
{
    "error": "string"
}
```

## Setup

1. Create a MySQL database named `wico_db`
2. Update database credentials in `config/database.php`
3. Ensure PHP has PDO and MySQL extensions enabled
4. Place the files in your web server directory
5. Make sure the web server has write permissions for the application directory

## Security

- All database queries use prepared statements to prevent SQL injection
- Session tokens are randomly generated using cryptographically secure functions
- Session tokens expire after 30 days
- All endpoints (except registration) require authentication
- CORS headers are properly set for cross-origin requests 