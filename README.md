# Wico REST API

This is a REST API for the Wico mobile application. The API provides endpoints for managing desires and localization.

## Database Structure

The application uses MySQL database with the following tables:

### Desires
- id (BIGINT AUTO_INCREMENT PRIMARY KEY)
- user_id (BIGINT NOT NULL)
- key (VARCHAR(255) NOT NULL)
- desire (VARCHAR(20) NOT NULL)
- comment (TEXT NULL)
- time (TIMESTAMP DEFAULT CURRENT_TIMESTAMP)
- FOREIGN KEY (user_id) REFERENCES users(id)

### Localization
- message_key (VARCHAR(255) NOT NULL PRIMARY KEY)
- language (VARCHAR(5) NOT NULL)
- message_text (TEXT NOT NULL)

## API Endpoints

### Desires

#### Get Desires
```
GET /api/desires
Required query parameters:
- desire: string (filter by desire type)
- key: string (authentication key)

Optional query parameters:
- limit: number (limit number of results, default 20)
```

#### GET Desire
```
GET /api/desires/{id}
Required query parameters:
- id: number (get specific user's desire) 
- key: string (authentication key)
```



#### Create/Update Desire
```
POST /api/desires
Required query parameters:
- key: string (authentication key)

Request body:
{
    "user_id": "number",
    "key": "string",
    "desire": "string",
    "comment": "string (optional)"
}
```

#### Delete Desire
```
DELETE /api/desires
Required query parameters:
- key: string (authentication key)
- id: number (user_id to delete desire for)
```

### Localization

#### Get Localization Messages
```
GET /api/localization/{language}
Example: /api/localization/cz
```

## Response Format

All responses are in JSON format:

### Success Response
```json
{
    "success": true,
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
- All endpoints require a valid key parameter for authentication
- CORS headers are properly set for cross-origin requests 