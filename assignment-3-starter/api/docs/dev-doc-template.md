# Assignment 3 – Developer Documentation
Sajad Mirpour Assignment 3 Web enterprise documentation. All steps and schemas are as following below.
## 1. Overview

Provides authenticated access to mail messages using JWT authentication, RBAC, request logging with unique request IDs, rate limiting, and centralized error handling.
The main use case is allowing users to securely access mail data while enforcing permissions and protecting the API from over population.

## 2. Authentication
Tokens are valid for 1 hour and must be included in all protected routes using the Authorization header.
### 2.1 Auth Method

- Scheme: Bearer token (JWT)
- How to obtain a token:
  - Endpoint: `POST /auth/login`
  - Request body format:
    ```json
    {
      "username": "user1",
      "password": "user123"
    }
    ```
  - Example success response:
    ```json
    {
      "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6..."
    }
    ```

### 2.2 Using the Token

- Required header for authenticated requests:
  - `Authorization: Bearer <token>`

Mention any expiry behavior (e.g., tokens are valid for 1 hour).

---

## 3. Roles & Access Rules

Describe each role and what it can do.

Example:

- `admin`
  - Can view any mail message.
- `user`
  - Can only view their own mail messages.

You can include a simple matrix:

| Endpoint        | Method | admin | user |
|----------------|--------|-------|------|
| `/mail/:id`    | GET    | ✅ all mail | ✅ own mail only |
| `/auth/login`  | POST   | ✅ | ✅ |
| `/status`      | GET    | ✅ | ✅ |

---

## 4. Endpoints
**Notes:**
Missing fields results in 400 BadRequest
Invalid credentials results in 401 Unauthorized
### 4.1 `POST /auth/login`

**Description:**  
Authenticate with username/password and receive a JWT.

**Request Body:**

```json
{
  "username": "user1",
  "password": "user123"
}
```

**Success Response (200):**

```json
{
  "token": "..."
}
```

**Notes:**
Document any common failure reasons (invalid credentials, missing fields).

---

### 4.2 `GET /mail/:id`

**Description:**
Retrieve a single mail message by ID.

**Authentication:**

* Requires `Authorization: Bearer <token>` header.

**Access Rules:**

* `admin`: may view any mail ID.
* `user`: may view only mail where `mail.userId` matches their own `userId`.

**Example Request:**

```bash
curl http://localhost:3000/mail/2 \
  -H "Authorization: Bearer <token>"
```

**Example Success Response (200):**

```json
{
  "id": 2,
  "userId": 2,
  "subject": "Hello User1",
  "body": "Your report is ready."
}
```

**Example Forbidden Response (when user tries to access someone else’s mail):**

```json
{
  "error": "Forbidden",
  "message": "User does not have permission to access this resource.",
  "statusCode": 403,
  "requestId": "req-12345",
  "timestamp": "2025-11-30T14:22:00Z"
}
```

---

### 4.3 `GET /status`

**Description:**
Simple health check to confirm the API is running.

**Authentication:**

* None required.

**Example Response (200):**

```json
{
  "status": "ok"
}
```

---

## 5. Rate Limiting
Keyed by: IP address (req.ip)
Limit: RATE_LIMIT_MAX requests per RATE_LIMIT_WINDOW_SECONDS
Applies globally to all routes

When exceeded:
Returns 429 TooManyRequests
Includes Retry After header
## 6. Error Response Format

Briefly describe the standard error JSON returned by your centralized error handler.

Example:

{
  "error": "TooManyRequests",
  "message": "Rate limit exceeded. Please try again later.",
  "statusCode": 429,
  "requestId": "req-67890",
  "timestamp": "2026-04-20T14:30:00Z"
}

List a few common error categories you use (`BadRequest`, `Unauthorized`, `Forbidden`, `NotFound`, `TooManyRequests`, `InternalServerError`, etc.).

---

Common error categories:
BadRequest (400)
Unauthorized (401)
Forbidden (403)
NotFound (404)
TooManyRequests (429)
InternalServerError (500)

## 7. Example Flows

Step 1: Login

curl -X POST http://localhost:3000/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"user1","password":"user123"}'

Response:
{
  "token": "<jwt-token>"
}

Step 2: Access mail

curl http://localhost:3000/mail/2 \
  -H "Authorization: Bearer <jwt-token>"

Response:
{
  "id": 2,
  "userId": 2,
  "subject": "Hello User1",
  "body": "Your report is ready."
}

### 7.1 Happy Path: Login + Access Own Mail

1. `POST /auth/login` as `user1` → receive token.
2. `GET /mail/2` with that token → receive mail details.

Include the exact curl commands and example responses.

### 7.2 Error Path: User Accessing Someone Else’s Mail
Login as user1, then:

curl http://localhost:3000/mail/1 \
  -H "Authorization: Bearer <jwt-token>"

Response:
{
  "error": "Forbidden",
  "message": "User does not have permission to access this resource.",
  "statusCode": 403,
  "requestId": "req-12345",
  "timestamp": "2026-04-20T14:22:00Z"
}

After exceeding the allowed requests:

Response:
{
  "error": "TooManyRequests",
  "message": "Rate limit exceeded. Please try again later.",
  "statusCode": 429,
  "requestId": "req-67890",
  "timestamp": "2026-04-20T14:30:00Z"
}