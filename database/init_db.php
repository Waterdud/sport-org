<?php
require_once dirname(__DIR__) . '/src/config/bootstrap.php';

$queries = [
    // Add role to users table
    "ALTER TABLE users ADD COLUMN role TEXT DEFAULT 'user'",
    
    // Extend users table
    "ALTER TABLE users ADD COLUMN reliability_rating REAL DEFAULT 5.0",
    "ALTER TABLE users ADD COLUMN games_attended INT DEFAULT 0",
    "ALTER TABLE users ADD COLUMN games_organized INT DEFAULT 0",
    "ALTER TABLE users ADD COLUMN avatar_url TEXT DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",

    // Extend events table
    "ALTER TABLE events ADD COLUMN status TEXT DEFAULT 'planned'",
    "ALTER TABLE events ADD COLUMN duration_minutes INT DEFAULT 60",
    "ALTER TABLE events ADD COLUMN reminder_sent BOOLEAN DEFAULT 0",
    "ALTER TABLE events ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
    "ALTER TABLE events ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",

    // Create game_participants table
    "CREATE TABLE IF NOT EXISTS game_participants (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        event_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        rsvp_status TEXT DEFAULT 'going',
        joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE(event_id, user_id)
    )",

    // Create ratings table
    "CREATE TABLE IF NOT EXISTS ratings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        rated_user_id INTEGER NOT NULL,
        rater_user_id INTEGER NOT NULL,
        event_id INTEGER NOT NULL,
        attendance_score INTEGER CHECK (attendance_score >= 1 AND attendance_score <= 5),
        cooperation_score INTEGER CHECK (cooperation_score >= 1 AND cooperation_score <= 5),
        sportsmanship_score INTEGER CHECK (sportsmanship_score >= 1 AND sportsmanship_score <= 5),
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (rated_user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (rater_user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
        UNIQUE(event_id, rater_user_id, rated_user_id)
    )",

    // Create notifications table
    "CREATE TABLE IF NOT EXISTS notifications (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        event_id INTEGER,
        notification_type TEXT NOT NULL,
        title TEXT NOT NULL,
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT 0,
        action_link TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
    )",

    // Create follows table
    "CREATE TABLE IF NOT EXISTS follows (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        follower_id INTEGER NOT NULL,
        followee_id INTEGER NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (followee_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE(follower_id, followee_id)
    )",

    // Create reminders table
    "CREATE TABLE IF NOT EXISTS reminders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        event_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        minutes_before INTEGER DEFAULT 1440,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        sent_at TIMESTAMP DEFAULT NULL,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE(event_id, user_id)
    )"
];

$count = 0;
foreach ($queries as $query) {
    try {
        $pdo->exec($query);
        $count++;
    } catch (Exception $e) {
        // Skipped - already exists
    }
}

echo "Database initialized with $count changes applied.\n";
?>
