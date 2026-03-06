<?php
/**
 * Файл подключения к базе данных с поддержкой SQLite
 * 
 * Используется PDO для безопасной работы с MySQL или SQLite
 * Настройки подключения берутся из config.php
 */

require_once __DIR__ . '/../config.php';

try {
    if (defined('DB_TYPE') && DB_TYPE === 'sqlite') {
        // SQLite подключение для быстрого тестирования
        $dbDir = dirname(SQLITE_DB_PATH);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }
        
        $pdo = new PDO('sqlite:' . SQLITE_DB_PATH, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        
        // Включаем foreign keys для SQLite
        $pdo->exec('PRAGMA foreign_keys = ON');
        
        // Создаем таблицы если их нет
        initializeSQLiteDatabase($pdo);
        
    } else {
        // MySQL подключение (стандартное)
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        
        // Установка часового пояса
        $pdo->exec("SET time_zone = '+03:00'");
    }
    
} catch (PDOException $e) {
    // Логирование ошибки
    error_log("Database Connection Error: " . $e->getMessage());
    
    // Показать пользователю общую ошибку
    if (DEBUG_MODE) {
        die("Ошибка подключения к базе данных: " . $e->getMessage());
    } else {
        die("Ошибка подключения к базе данных. Пожалуйста, попробуйте позже.");
    }
}

/**
 * Инициализация SQLite базы данных с тестовыми данными
 */
function initializeSQLiteDatabase($pdo) {
    // Проверяем, существуют ли таблицы
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        // Таблицы существуют, проверяем структуру и добавляем недостающие колонки
        try {
            // Проверяем наличие колонки attended_events
            $columns = $pdo->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_ASSOC);
            $hasAttendedEvents = false;
            foreach ($columns as $column) {
                if ($column['name'] === 'attended_events') {
                    $hasAttendedEvents = true;
                    break;
                }
            }
            
            if (!$hasAttendedEvents) {
                $pdo->exec("ALTER TABLE users ADD COLUMN attended_events INTEGER DEFAULT 0");
            }
        } catch (PDOException $e) {
            // Игнорируем ошибки миграции
        }
        return;
    }
    
    // Создаем таблицы
    $pdo->exec("CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        phone TEXT,
        avatar TEXT,
        rating REAL DEFAULT 0,
        total_ratings INTEGER DEFAULT 0,
        total_events INTEGER DEFAULT 0,
        participated_events INTEGER DEFAULT 0,
        attended_events INTEGER DEFAULT 0,
        remember_token TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    $pdo->exec("CREATE TABLE locations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        address TEXT NOT NULL,
        city TEXT NOT NULL,
        sport_types TEXT NOT NULL,
        description TEXT,
        image TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    $pdo->exec("CREATE TABLE events (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        description TEXT,
        sport_type TEXT NOT NULL,
        location_id INTEGER,
        creator_id INTEGER NOT NULL,
        event_date DATE NOT NULL,
        event_time TIME NOT NULL,
        duration INTEGER DEFAULT 120,
        max_participants INTEGER NOT NULL,
        current_participants INTEGER DEFAULT 1,
        skill_level TEXT NOT NULL,
        status TEXT DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL,
        FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    
    $pdo->exec("CREATE TABLE participants (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        event_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        status TEXT DEFAULT 'confirmed',
        joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE(event_id, user_id)
    )");
    
    $pdo->exec("CREATE TABLE ratings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        event_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        rating INTEGER NOT NULL,
        comment TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    
    $pdo->exec("CREATE TABLE notifications (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        event_id INTEGER,
        type TEXT NOT NULL,
        message TEXT NOT NULL,
        is_read INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
    )");
    
    $pdo->exec("CREATE TABLE comments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        event_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        comment TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    
    // Индексы
    $pdo->exec("CREATE INDEX idx_events_date ON events(event_date, event_time)");
    $pdo->exec("CREATE INDEX idx_events_sport ON events(sport_type)");
    $pdo->exec("CREATE INDEX idx_participants_user ON participants(user_id)");
    $pdo->exec("CREATE INDEX idx_participants_event ON participants(event_id)");
    
    // Тестовые данные
    $password = password_hash('123456', PASSWORD_DEFAULT);
    
    $pdo->exec("INSERT INTO users (username, email, password, phone, rating, total_ratings, total_events) VALUES
        ('admin', 'admin@sport.com', '$password', '+7 (999) 123-45-67', 4.8, 15, 12),
        ('user1', 'user1@sport.com', '$password', '+7 (999) 234-56-78', 4.5, 8, 5),
        ('user2', 'user2@sport.com', '$password', '+7 (999) 345-67-89', 4.2, 10, 7)
    ");
    
    $pdo->exec("INSERT INTO locations (name, address, city, sport_types, description) VALUES
        ('Kadriorg Stadium', 'Roheline aas 10', 'Tallinn', 'Jalgpall,Korvpall', 'Suur staadion suurepäraste tingimustega'),
        ('Kalev Sports Hall', 'Juhkentali 12', 'Tallinn', 'Võrkpall,Korvpall', 'Siseruumidega spordikompleks'),
        ('Tartu University Sports Ground', 'Jaama 67', 'Tartu', 'Jalgpall', 'Avaväljak ülikooli lähedal'),
        ('Narva Beach Volleyball Court', 'Pushkini 28', 'Narva', 'Võrkpall', 'Liivaväljakrand mere ääres'),
        ('Pärnu Basketball Arena', 'Ringi 35', 'Pärnu', 'Korvpall', 'Kaasaegne sisekorvpalliväljak')
    ");
    
    $pdo->exec("INSERT INTO events (title, description, sport_type, location_id, creator_id, event_date, event_time, max_participants, current_participants, skill_level, status) VALUES
        ('Jalgpall laupäeval', 'Sõbralik jalgpallimäng', 'Jalgpall', 1, 1, '2026-02-21', '14:00', 10, 3, 'Harrastaja', 'active'),
        ('Võrkpall algajatele', 'Mängime ja õpime', 'Võrkpall', 2, 2, '2026-02-22', '16:00', 8, 2, 'Algaja', 'active'),
        ('Korvpall 3x3', 'Kiire mäng', 'Korvpall', 5, 1, '2026-02-23', '18:00', 6, 4, 'Edasijõudnu', 'active'),
        ('Jalgpall õhtul', 'Pärast tööd', 'Jalgpall', 3, 3, '2026-02-24', '19:00', 12, 5, 'Harrastaja', 'active')
    ");
    
    $pdo->exec("INSERT INTO participants (event_id, user_id, status) VALUES
        (1, 1, 'confirmed'), (1, 2, 'confirmed'), (1, 3, 'confirmed'),
        (2, 2, 'confirmed'), (2, 3, 'confirmed'),
        (3, 1, 'confirmed'), (3, 2, 'confirmed'), (3, 3, 'confirmed'),
        (4, 3, 'confirmed'), (4, 1, 'confirmed'), (4, 2, 'confirmed')
    ");
    
    $pdo->exec("INSERT INTO comments (event_id, user_id, comment) VALUES
        (1, 2, 'Suurepärane idee! Tulen!'),
        (1, 3, 'Mis kell täpselt algus?'),
        (2, 1, 'Kas sobib algajatele?')
    ");
}

/**
 * Функция для безопасного выполнения запросов
 * 
 * @param PDO $pdo - объект подключения
 * @param string $sql - SQL запрос
 * @param array $params - параметры для подготовленного запроса
 * @return PDOStatement|false
 */
function query($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Query Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Функция для получения одной записи
 * 
 * @param PDO $pdo - объект подключения
 * @param string $sql - SQL запрос
 * @param array $params - параметры
 * @return array|false
 */
function fetchOne($pdo, $sql, $params = []) {
    $stmt = query($pdo, $sql, $params);
    return $stmt ? $stmt->fetch() : false;
}

/**
 * Функция для получения всех записей
 * 
 * @param PDO $pdo - объект подключения
 * @param string $sql - SQL запрос
 * @param array $params - параметры
 * @return array
 */
function fetchAll($pdo, $sql, $params = []) {
    $stmt = query($pdo, $sql, $params);
    return $stmt ? $stmt->fetchAll() : [];
}

/**
 * Функция для вставки записи и получения ID
 * 
 * @param PDO $pdo - объект подключения
 * @param string $sql - SQL запрос
 * @param array $params - параметры
 * @return string|false - ID вставленной записи или false
 */
function insert($pdo, $sql, $params = []) {
    $stmt = query($pdo, $sql, $params);
    return $stmt ? $pdo->lastInsertId() : false;
}

/**
 * Функция для обновления/удаления и получения количества затронутых строк
 * 
 * @param PDO $pdo - объект подключения
 * @param string $sql - SQL запрос
 * @param array $params - параметры
 * @return int|false - количество затронутых строк
 */
function execute($pdo, $sql, $params = []) {
    $stmt = query($pdo, $sql, $params);
    return $stmt ? $stmt->rowCount() : false;
}
