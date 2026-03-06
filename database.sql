-- ============================================
-- Скрипт создания базы данных для приложения
-- организации спортивных мероприятий
-- ============================================

-- Создание базы данных
CREATE DATABASE IF NOT EXISTS sport_org 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE sport_org;

-- ============================================
-- Таблица пользователей
-- ============================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    avatar VARCHAR(255) DEFAULT 'default-avatar.png',
    rating DECIMAL(3,2) DEFAULT 5.00, -- Рейтинг от 0 до 10
    total_events INT DEFAULT 0, -- Всего событий
    attended_events INT DEFAULT 0, -- Посещённых событий
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Таблица локаций (мест для игр)
-- ============================================
CREATE TABLE locations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    sport_types VARCHAR(255), -- Футбол, Волейбол, Баскетбол (через запятую)
    description TEXT,
    latitude DECIMAL(10, 8), -- Координаты для карты
    longitude DECIMAL(11, 8),
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_city (city),
    INDEX idx_sport_types (sport_types(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Таблица событий
-- ============================================
CREATE TABLE events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    creator_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    sport_type ENUM('Футбол', 'Волейбол', 'Баскетбол') NOT NULL,
    location_id INT,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    duration INT DEFAULT 120, -- Длительность в минутах
    max_participants INT NOT NULL,
    current_participants INT DEFAULT 0,
    skill_level ENUM('Начинающий', 'Любитель', 'Продвинутый', 'Профессионал') DEFAULT 'Любитель',
    description TEXT,
    status ENUM('Открыто', 'Закрыто', 'Завершено', 'Отменено') DEFAULT 'Открыто',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL,
    INDEX idx_event_date (event_date),
    INDEX idx_sport_type (sport_type),
    INDEX idx_status (status),
    INDEX idx_creator (creator_id),
    INDEX idx_location (location_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Таблица участников событий
-- ============================================
CREATE TABLE participants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('Записан', 'Подтвержден', 'Не пришёл', 'Пришёл', 'Отменил') DEFAULT 'Записан',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT, -- Заметки участника
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_participation (event_id, user_id),
    INDEX idx_event (event_id),
    INDEX idx_user (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Таблица рейтингов (оценок участников)
-- ============================================
CREATE TABLE ratings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    rated_user_id INT NOT NULL, -- Кого оценивают
    rater_user_id INT NOT NULL, -- Кто оценивает
    rating TINYINT CHECK (rating >= 1 AND rating <= 10), -- Оценка от 1 до 10
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (rated_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (rater_user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_rating (event_id, rated_user_id, rater_user_id),
    INDEX idx_rated_user (rated_user_id),
    INDEX idx_event (event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Таблица уведомлений
-- ============================================
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    event_id INT,
    type ENUM('Запись', 'Отмена', 'Напоминание', 'Изменение', 'Оценка', 'Комментарий') NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_read (is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Таблица комментариев к событиям
-- ============================================
CREATE TABLE comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_event (event_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Триггер для обновления количества участников
-- ============================================
DELIMITER //

CREATE TRIGGER update_participants_count_insert
AFTER INSERT ON participants
FOR EACH ROW
BEGIN
    UPDATE events 
    SET current_participants = (
        SELECT COUNT(*) FROM participants 
        WHERE event_id = NEW.event_id 
        AND status IN ('Записан', 'Подтвержден', 'Пришёл')
    )
    WHERE id = NEW.event_id;
END//

CREATE TRIGGER update_participants_count_update
AFTER UPDATE ON participants
FOR EACH ROW
BEGIN
    UPDATE events 
    SET current_participants = (
        SELECT COUNT(*) FROM participants 
        WHERE event_id = NEW.event_id 
        AND status IN ('Записан', 'Подтвержден', 'Пришёл')
    )
    WHERE id = NEW.event_id;
END//

CREATE TRIGGER update_participants_count_delete
AFTER DELETE ON participants
FOR EACH ROW
BEGIN
    UPDATE events 
    SET current_participants = (
        SELECT COUNT(*) FROM participants 
        WHERE event_id = OLD.event_id 
        AND status IN ('Записан', 'Подтвержден', 'Пришёл')
    )
    WHERE id = OLD.event_id;
END//

-- ============================================
-- Триггер для обновления рейтинга пользователя
-- ============================================
CREATE TRIGGER update_user_rating
AFTER INSERT ON ratings
FOR EACH ROW
BEGIN
    UPDATE users 
    SET rating = (
        SELECT AVG(rating) 
        FROM ratings 
        WHERE rated_user_id = NEW.rated_user_id
    )
    WHERE id = NEW.rated_user_id;
END//

DELIMITER ;

-- ============================================
-- Вставка тестовых данных
-- ============================================

-- Тестовые локации
INSERT INTO locations (name, address, city, sport_types, description) VALUES
('Спортивный комплекс "Олимп"', 'ул. Ленина, 45', 'Москва', 'Футбол,Волейбол,Баскетбол', 'Современный спортивный комплекс с открытыми и закрытыми площадками'),
('Стадион "Центральный"', 'пр. Мира, 12', 'Москва', 'Футбол', 'Футбольное поле с искусственным покрытием'),
('Площадка "Дружба"', 'ул. Спортивная, 8', 'Санкт-Петербург', 'Баскетбол,Волейбол', 'Открытая площадка в парке'),
('Фитнес-центр "Энергия"', 'ул. Гагарина, 23', 'Казань', 'Волейбол,Баскетбол', 'Закрытый зал с профессиональным покрытием');

-- ============================================
-- Полезные представления (Views)
-- ============================================

-- Представление: События с информацией о создателе и локации
CREATE VIEW events_full AS
SELECT 
    e.*,
    u.username as creator_name,
    u.rating as creator_rating,
    l.name as location_name,
    l.address as location_address,
    l.city as location_city
FROM events e
LEFT JOIN users u ON e.creator_id = u.id
LEFT JOIN locations l ON e.location_id = l.id;

-- Представление: Статистика пользователей
CREATE VIEW user_stats AS
SELECT 
    u.id,
    u.username,
    u.email,
    u.rating,
    u.total_events,
    u.attended_events,
    CASE 
        WHEN u.total_events > 0 THEN ROUND((u.attended_events / u.total_events) * 100, 2)
        ELSE 0 
    END as attendance_percentage,
    COUNT(DISTINCT e.id) as created_events_count
FROM users u
LEFT JOIN events e ON u.id = e.creator_id
GROUP BY u.id;

-- ============================================
-- Завершение скрипта
-- ============================================
