# Admin Panel Guide

## Access the Admin Panel

Admin dashboard can be accessed at: `http://localhost:8000?page=admin-dashboard`

You need to be logged in as an admin to access the admin panel.

## Making a User an Admin

### Method 1: Database Query
```sql
UPDATE users SET role = 'admin' WHERE id = 1;
```

### Method 2: Using the Users Management Page
1. Go to Admin Dashboard → Users
2. Click "Toggle Role" next to a user to make them admin
3. Click again to remove admin privileges

## Admin Features

### Dashboard
- View basic statistics (total users, events, participants)
- Quick navigation to management pages

### User Management
- View all users with their roles
- Toggle admin privileges
- Delete users

### Event Management
- View all events with details
- See event status, location, date
- Delete events
- View full event details

## Accessing Admin Pages

- **Dashboard**: `?page=admin-dashboard`
- **Manage Users**: `?page=admin-users`
- **Manage Events**: `?page=admin-events`
