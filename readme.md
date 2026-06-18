# Developer Internship Portal

A full-stack web application built as part of the GUVI Developer Internship assignment.

## Project Overview

This application allows users to:

- Register a new account
- Log in using registered credentials
- View and update profile information
- Maintain login sessions using Local Storage and Redis

### Application Flow

Register → Login → Profile

---

## Features

### User Registration
- Create a new account
- Secure password storage
- Form validation
- AJAX-based communication

### User Login
- Authenticate registered users
- Session token generation
- Local Storage session management
- Redis-backed session storage

### User Profile
- View profile details
- Update personal information
- Store additional details:
  - Age
  - Date of Birth
  - Contact Number
  - Address
  - Skills
  - Education

### Responsive Design
- Built with Bootstrap
- Mobile-friendly UI
- Modern and clean interface

---

## Tech Stack

### Frontend
- HTML5
- CSS3
- JavaScript
- jQuery AJAX
- Bootstrap 5

### Backend
- PHP

### Databases
- MySQL (User Authentication)
- MongoDB (User Profile Data)

### Session Management
- Redis

---

## Folder Structure

```bash
project/
│
├── frontend/
│   ├── login.html
│   ├── register.html
│   ├── profile.html
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   ├── login.js
│   │   ├── register.js
│   │   └── profile.js
│
├── backend/
│   ├── config/
│   │   ├── mysql.php
│   │   ├── mongo.php
│   │   └── redis.php
│   │
│   ├── api/
│   │   ├── register.php
│   │   ├── login.php
│   │   ├── profile.php
│   │   └── logout.php
│
├── database/
│   ├── mysql_schema.sql
│   └── mongo_collections.json
│
└── README.md
```

---

## Database Design

### MySQL Table: Users

| Field | Type |
|---------|---------|
| id | INT |
| name | VARCHAR |
| email | VARCHAR |
| password | VARCHAR |
| created_at | TIMESTAMP |

### MongoDB Collection: Profiles

```json
{
  "user_id": 1,
  "age": 22,
  "dob": "2004-01-01",
  "contact": "9876543210",
  "address": "India",
  "skills": ["PHP", "JavaScript"]
}
```

---

## Session Flow

1. User logs in.
2. Backend generates a session token.
3. Session token is stored in Redis.
4. Token is returned to the frontend.
5. Frontend stores token in Local Storage.
6. Protected pages validate token through API calls.

---

## Installation

### Clone Repository

```bash
git clone https://github.com/yourusername/developer-internship-portal.git
cd developer-internship-portal
```

### Install Dependencies

#### PHP Server

```bash
composer install
```

#### MySQL

Create database:

```sql
CREATE DATABASE internship_portal;
```

Import schema:

```bash
mysql -u root -p internship_portal < mysql_schema.sql
```

#### MongoDB

Start MongoDB service.

```bash
mongod
```

#### Redis

Start Redis server.

```bash
redis-server
```

---

## Running the Application

Start PHP server:

```bash
php -S localhost:8000
```

Open browser:

```text
http://localhost:8000
```

---

## API Endpoints

### Register

```http
POST /api/register.php
```

### Login

```http
POST /api/login.php
```

### Get Profile

```http
GET /api/profile.php
```

### Update Profile

```http
PUT /api/profile.php
```

### Logout

```http
POST /api/logout.php
```

---

## Security Features

- Password hashing
- Prepared Statements
- Input validation
- AJAX-only communication
- Redis session storage
- Local Storage authentication
- SQL injection prevention
- XSS protection

---

## Screenshots

### Login Page
- User authentication interface
- Responsive Bootstrap design

### Registration Page
- New account creation form

### Profile Page
- User information management dashboard

---

## Assignment Requirements Covered
✅ Separate HTML, CSS, JS, and PHP files
✅ Bootstrap-based responsive forms
✅ jQuery AJAX communication
✅ MySQL database integration
✅ MongoDB profile storage
✅ Redis session management
✅ Local Storage authentication
✅ Prepared Statements used
✅ Register → Login → Profile workflow


## Author

**Ananya**

Developer Internship Project – GUVI
