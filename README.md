# Wico REST API

This is a REST API for the Wico mobile application. The API provides endpoints for user management, desires, and contacts.

## Database Structure

The application uses MySQL database with the following tables:

### Users
- id (BIGINT PRIMARY KEY)
- nickname (VARCHAR(255) NULL)
- email (VARCHAR(255) NOT NULL UNIQUE)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
- last_login (TIMESTAMP NULL)
- phone (VARCHAR(20) NOT NULL)
- gps (VARCHAR(255) NULL)

### Desires
- id (BIGINT AUTO_INCREMENT PRIMARY KEY)
- user_id (BIGINT NOT NULL)
- desire (TEXT NOT NULL)
- comment (TEXT NULL)
- time (TIMESTAMP)

### Contacts
- id (BIGINT AUTO_INCREMENT PRIMARY KEY)
- user_1_id (BIGINT NOT NULL)
- user_2_phone (VARCHAR(20) NOT NULL)
- user_2_alias (VARCHAR(255) NULL)

### Localization
- message_key (VARCHAR(255) NOT NULL PRIMARY KEY)
- language (VARCHAR(5) NOT NULL)
- message_text (TEXT NOT NULL)

## API Endpoints

### Authentication
All endpoints except user registration require a Bearer token in the Authorization header.

### Users

#### Register/Login User
```
POST /api/users
Content-Type: application/json

{
    "email": "string",
    "nickname": "string (optional)",
    "phone": "string",
    "gps": "string (optional)"
}
```

#### Get User Profile
```
GET /api/users/{id}
Authorization: Bearer <session_token>
```

#### Update User Profile
```
PUT /api/users/{id}
Authorization: Bearer <session_token>
Content-Type: application/json

{
    "nickname": "string (optional)",
    "phone": "string (optional)",
    "gps": "string (optional)"
}
```

#### Delete User
```
DELETE /api/users/{id}
Authorization: Bearer <session_token>
```

### Desires

#### Create/Update Desire
```
POST /api/desires
Authorization: Bearer <session_token>
Content-Type: application/json

{
    "desire": "string",
    "comment": "string (optional)"
}
```

#### Get Desires
```
GET /api/desires
Authorization: Bearer <session_token>

Optional query parameters:
- user_id: number
- desire: string (search term)
```

#### Delete Desire
```
DELETE /api/desires/{id}
Authorization: Bearer <session_token>
```

### Contacts

#### Add Contact
```
POST /api/contacts
Authorization: Bearer <session_token>
Content-Type: application/json

{
    "user_1_id": "number",
    "user_2_phone": "string",
    "user_2_alias": "string (optional)"
}
```

#### Get User Contacts
```
GET /api/contacts/user/{user_id}
Authorization: Bearer <session_token>

Optional query parameters:
- desire: string (filter contacts by their current desire)
```

#### Remove Contact
```
DELETE /api/contacts/{id}
Authorization: Bearer <session_token>
```

### Localization

#### Get Localization Messages
```
GET /api/localization/{language}
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

1. Create a MySQL database
2. Copy `config/secret.example.php` to `config/secret.php` and update with your database credentials
3. Ensure PHP has PDO and MySQL extensions enabled
4. Place the files in your web server directory
5. Make sure the web server has write permissions for the application directory

## Security

- All database queries use prepared statements to prevent SQL injection
- All endpoints (except registration) require authentication
- Session tokens are randomly generated using cryptographically secure functions
- Session tokens expire after 30 days
- CORS headers are properly set for cross-origin requests 