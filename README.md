# SportOrg - Sports Event Platform

A clean, modern PHP application for organizing sports events and finding training partners.



### Installation

1. Clone the repository:
```bash
git clone https://github.com/Waterdud/sport-org.git
cd sport-org
```

2. Start the PHP development server:
```bash
php -S localhost:8000
```

3. Open your browser to `http://localhost:8000`

## 📁 Project Structure

```
sport-org/
├── src/                      # Application source code
│   ├── ajax/                # AJAX endpoint handlers
│   ├── components/          # Reusable UI components (Header, Footer, etc.)
│   ├── config/              # Configuration and bootstrap initialization
│   ├── helpers/             # Utility functions with Estonian translations
│   ├── pages/               # Page templates organized by feature
│   │   ├── auth/           # Authentication (login, register, logout)
│   │   ├── events/         # Event management
│   │   ├── locations/      # Sports venue management
│   │   └── user/           # User profile and notifications
│   └── services/           # Business logic services (future)
│
├── public/                   # Web-accessible files
│   ├── assets/              # CSS, JS, images
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   └── index.php           # Entry point for /public folder
│
├── database/                # Database files
│   └── sport_events.db     # SQLite database
│
├── uploads/                 # User uploads
│   ├── avatars/            # User profile images
│   └── locations/          # Sports venue images
│
├── index.php               # Main router (root level)
└── .htaccess              # Apache configuration

```

### User Authentication
- User registration with email validation
- Secure login with password hashing
- Session-based authentication
- Remember Me functionality

### Event Management
- Create and manage sports events (treeningud)
- Join/leave events
- Filter events by sport type, city, date
- View participant lists and comments

### Location Management
- Browse sports venues (kohad)
- Add new locations with sport type filters
- Automatic image generation for venues
- Display location details and associated events

### User Dashboard
- User profile management
- Notification system
- Your events and participations
- User statistics

## 🌐 Pages & Routes

| Page | Route | Description |
|------|-------|-------------|
| Home | `/` | Event listings and filters |
| Login | `/login` | User authentication |
| Register | `/register` | Create new account |
| Events | `/events` | All events list |
| My Events | `/events/my` | User's events |
| Locations | `/locations` | Sports venues list |
| Profile | `/profile` | User profile |
| Notifications | `/notifications` | User notifications |

## 💾 Database

SQLite database with tables for:
- `users` - User accounts and authentication
- `events` - Sports events and trainings
- `event_participants` - Event participation records
- `locations` - Sports venues
- `comments` - Event comments
- `notifications` - User notifications

## 🛠️ Development

### Key Components

**`src/config/bootstrap.php`**
- Initializes application
- Loads database connection
- Sets up error handling

**`src/helpers/functions.php`**
- Authentication helpers (isLoggedIn, getCurrentUser)
- Security functions (clean, isValidEmail)
- Translation functions (translateEventStatus, formatDateEt)
- Estonian localization

**`src/components/Header.php`**
- Navigation bar with language in Estonian
- User menu for logged-in users

**`src/components/Footer.php`**
- Site footer with contact information
- Navigation links

## 🔒 Security Features

- XSS protection via `clean()` function
- Password hashing with PHP's `password_hash()`
- SQL injection prevention through PDO prepared statements
- Session-based authentication
- CSRF validation (implement middleware)

## 🌍 Internationalization

Currently in **Estonian**. To add more languages:
1. Create new translation functions in `src/helpers/functions.php`
2. Update component templates with locale-specific text
3. Add language switcher in Header.php

## 📋 API Endpoints (AJAX)

- `GET /ajax/unread-count` - Get unread notification count (JSON)
- `POST /ajax/join-event` - Join an event
- `POST /ajax/leave-event` - Leave an event
- `POST /ajax/add-comment` - Add event comment
- `POST /ajax/mark-read` - Mark notification as read



---

**Last Updated:** March 2026  
**Version:** 1.0.0 - Migration Complete
