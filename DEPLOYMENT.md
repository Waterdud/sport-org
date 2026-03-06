# 🚀 Руководство по развёртыванию SportConnect

Подробное руководство по установке и настройке приложения на различных платформах.

## 📋 Содержание

1. [Локальная разработка (XAMPP/WAMP)](#локальная-разработка)
2. [Локальная разработка (Docker)](#docker)
3. [Shared хостинг](#shared-хостинг)
4. [VPS/VDS (Ubuntu/Debian)](#vpsvds)
5. [Troubleshooting](#troubleshooting)

---

## 🖥 Локальная разработка

### Вариант 1: XAMPP (Windows, macOS, Linux)

#### Установка XAMPP:

**Windows:**
1. Скачайте с https://www.apachefriends.org/
2. Запустите установщик
3. Выберите компоненты: Apache, MySQL, PHP, phpMyAdmin
4. Установите в `C:\xampp`

**macOS/Linux:**
```bash
# macOS (через Homebrew)
brew install xampp

# Linux
wget https://www.apachefriends.org/xampp-files/[version]/xampp-linux-x64-[version]-installer.run
chmod +x xampp-linux-*.run
sudo ./xampp-linux-*.run
```

#### Настройка проекта:

1. **Скопируйте проект:**
   ```bash
   # Windows
   xcopy /E /I sport-org C:\xampp\htdocs\sport-org
   
   # macOS/Linux
   cp -r sport-org /Applications/XAMPP/htdocs/sport-org
   ```

2. **Запустите сервисы:**
   - Откройте XAMPP Control Panel
   - Нажмите "Start" для Apache и MySQL

3. **Создайте базу данных:**
   - Откройте http://localhost/phpmyadmin
   - Создайте БД `sport_events`
   - Импортируйте `database.sql`

4. **Настройте конфигурацию:**
   ```bash
   cp config.example.php config.php
   ```
   
   Отредактируйте `config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'sport_events');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // Обычно пустой пароль в XAMPP
   define('SITE_URL', 'http://localhost/sport-org');
   ```

5. **Установите права на папки:**
   ```bash
   # Windows (PowerShell от имени администратора)
   icacls C:\xampp\htdocs\sport-org\uploads /grant Users:F /T
   
   # macOS/Linux
   chmod -R 755 /Applications/XAMPP/htdocs/sport-org/uploads
   ```

6. **Откройте приложение:**
   - http://localhost/sport-org

---

### Вариант 2: WAMP (только Windows)

#### Установка WAMP:

1. Скачайте с https://www.wampserver.com/
2. Установите в `C:\wamp64`
3. Запустите WampServer

#### Настройка:

1. **Скопируйте проект:**
   ```bash
   xcopy /E /I sport-org C:\wamp64\www\sport-org
   ```

2. **Создайте базу:**
   - Откройте http://localhost/phpmyadmin
   - Логин: `root`, пароль: пустой
   - Создайте БД `sport_events`
   - Импортируйте `database.sql`

3. **Настройте config.php** (аналогично XAMPP)

4. **Установите права:**
   ```bash
   icacls C:\wamp64\www\sport-org\uploads /grant Users:F /T
   ```

5. **Откройте:** http://localhost/sport-org

---

## 🐳 Docker

### Быстрый старт с Docker Compose:

1. **Создайте `docker-compose.yml` в корне проекта:**

```yaml
version: '3.8'

services:
  web:
    image: php:8.0-apache
    container_name: sport-org-web
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html
    depends_on:
      - db
    environment:
      - DB_HOST=db
      - DB_NAME=sport_events
      - DB_USER=sport_user
      - DB_PASS=sport_pass

  db:
    image: mysql:8.0
    container_name: sport-org-db
    ports:
      - "3306:3306"
    environment:
      MYSQL_ROOT_PASSWORD: root_password
      MYSQL_DATABASE: sport_events
      MYSQL_USER: sport_user
      MYSQL_PASSWORD: sport_pass
    volumes:
      - db_data:/var/lib/mysql
      - ./database.sql:/docker-entrypoint-initdb.d/database.sql

  phpmyadmin:
    image: phpmyadmin:latest
    container_name: sport-org-phpmyadmin
    ports:
      - "8081:80"
    environment:
      PMA_HOST: db
      PMA_USER: root
      PMA_PASSWORD: root_password
    depends_on:
      - db

volumes:
  db_data:
```

2. **Создайте `Dockerfile` (опционально, для кастомизации PHP):**

```dockerfile
FROM php:8.0-apache

# Установка расширений PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Включение mod_rewrite
RUN a2enmod rewrite

# Установка прав
RUN chown -R www-data:www-data /var/www/html/uploads

EXPOSE 80
```

3. **Настройте config.php:**
```php
define('DB_HOST', 'db'); // Имя сервиса из docker-compose.yml
define('DB_NAME', 'sport_events');
define('DB_USER', 'sport_user');
define('DB_PASS', 'sport_pass');
define('SITE_URL', 'http://localhost:8080');
```

4. **Запустите:**
```bash
# Запуск
docker-compose up -d

# Проверка статуса
docker-compose ps

# Логи
docker-compose logs -f web

# Остановка
docker-compose down
```

5. **Откройте:**
   - Приложение: http://localhost:8080
   - phpMyAdmin: http://localhost:8081

---

## 🌐 Shared Хостинг (Beget, Timeweb, RU-CENTER)

### Требования:
- PHP 7.4+
- MySQL 5.7+
- Доступ по FTP/SFTP
- phpMyAdmin или аналог

### Установка:

1. **Загрузите файлы:**
   - Используйте FileZilla, WinSCP или встроенный файловый менеджер
   - Загрузите все файлы в `public_html` или `www`

2. **Создайте базу данных:**
   - Зайдите в панель управления хостингом
   - Создайте БД (например, `u123456_sport`)
   - Создайте пользователя БД
   - Запомните имя БД, пользователя и пароль

3. **Импортируйте SQL:**
   - Откройте phpMyAdmin
   - Выберите созданную БД
   - Вкладка "Импорт"
   - Загрузите `database.sql`

4. **Настройте config.php:**
```php
define('DB_HOST', 'localhost'); // Иногда нужен другой хост
define('DB_NAME', 'u123456_sport');
define('DB_USER', 'u123456_sport_user');
define('DB_PASS', 'ваш_пароль');
define('SITE_URL', 'https://your-domain.com');
define('DEBUG_MODE', false); // ВАЖНО в продакшене!
```

5. **Установите права:**
   - Через файловый менеджер или FTP
   - `uploads/` → 755 или 777
   - `uploads/avatars/` → 755 или 777
   - `uploads/locations/` → 755 или 777

6. **Проверьте .htaccess:**
   - Должен быть в корне
   - Если не работает, проверьте поддержку mod_rewrite

### Особенности хостингов:

**Beget:**
```php
define('DB_HOST', 'localhost');
// База обычно: имя_аккаунта_имя_бд
```

**Timeweb:**
```php
define('DB_HOST', 'localhost');
// Или хост вида mysql123.timeweb.ru
```

**RU-CENTER:**
```php
define('DB_HOST', 'localhost');
// Иногда требуется полный путь к socket
```

---

## 🖧 VPS/VDS (Ubuntu 20.04/22.04, Debian 10/11)

### Полная установка LAMP стека:

#### 1. Обновите систему:
```bash
sudo apt update
sudo apt upgrade -y
```

#### 2. Установите Apache:
```bash
sudo apt install apache2 -y
sudo systemctl start apache2
sudo systemctl enable apache2
```

#### 3. Установите MySQL:
```bash
sudo apt install mysql-server -y
sudo mysql_secure_installation
```

Создайте базу и пользователя:
```sql
sudo mysql -u root -p

CREATE DATABASE sport_events CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sport_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON sport_events.* TO 'sport_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Импортируйте данные:
```bash
mysql -u sport_user -p sport_events < database.sql
```

#### 4. Установите PHP:
```bash
sudo apt install php php-mysql php-gd php-mbstring php-xml libapache2-mod-php -y
```

Проверьте версию:
```bash
php -v
```

#### 5. Настройте Apache:

Создайте виртуальный хост:
```bash
sudo nano /etc/apache2/sites-available/sport-org.conf
```

Содержимое:
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    DocumentRoot /var/www/sport-org
    
    <Directory /var/www/sport-org>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/sport-org-error.log
    CustomLog ${APACHE_LOG_DIR}/sport-org-access.log combined
</VirtualHost>
```

Активируйте:
```bash
sudo a2ensite sport-org.conf
sudo a2enmod rewrite
sudo systemctl reload apache2
```

#### 6. Разверните проект:
```bash
sudo mkdir -p /var/www/sport-org
sudo chown -R $USER:$USER /var/www/sport-org

# Загрузите файлы (через git, scp, ftp)
# Например, через git:
cd /var/www/sport-org
git clone https://github.com/your-repo/sport-org.git .

# Или через scp с локальной машины:
# scp -r sport-org/* user@your-server:/var/www/sport-org/
```

#### 7. Настройте права:
```bash
sudo chown -R www-data:www-data /var/www/sport-org
sudo chmod -R 755 /var/www/sport-org
sudo chmod -R 777 /var/www/sport-org/uploads
```

#### 8. Настройте config.php:
```bash
cd /var/www/sport-org
cp config.example.php config.php
nano config.php
```

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sport_events');
define('DB_USER', 'sport_user');
define('DB_PASS', 'strong_password');
define('SITE_URL', 'https://your-domain.com');
define('DEBUG_MODE', false);
```

#### 9. Настройте SSL (Let's Encrypt):
```bash
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d your-domain.com -d www.your-domain.com
```

Автообновление:
```bash
sudo certbot renew --dry-run
```

#### 10. Настройте Firewall:
```bash
sudo ufw allow 'Apache Full'
sudo ufw allow OpenSSH
sudo ufw enable
```

#### 11. Оптимизация PHP:
```bash
sudo nano /etc/php/8.0/apache2/php.ini
```

Измените:
```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 60
memory_limit = 256M
```

Перезапустите:
```bash
sudo systemctl restart apache2
```

---

## 🔧 Troubleshooting

### Проблема: Ошибка подключения к БД

**Решение:**
1. Проверьте параметры в `config.php`
2. Убедитесь, что MySQL запущен:
   ```bash
   # Windows (XAMPP)
   netstat -an | findstr 3306
   
   # Linux
   sudo systemctl status mysql
   ```
3. Проверьте права пользователя БД

---

### Проблема: 404 Not Found для всех страниц

**Решение:**
1. Проверьте наличие `.htaccess`
2. Включите `mod_rewrite`:
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```
3. Проверьте `AllowOverride All` в конфиге Apache

---

### Проблема: Не загружаются изображения

**Решение:**
1. Проверьте права на папку `uploads/`:
   ```bash
   # Windows
   icacls uploads /grant Users:F /T
   
   # Linux
   sudo chmod -R 777 uploads/
   sudo chown -R www-data:www-data uploads/
   ```

2. Проверьте настройки PHP:
   ```ini
   upload_max_filesize = 10M
   post_max_size = 10M
   ```

---

### Проблема: Белый экран (WSOD)

**Решение:**
1. Включите отображение ошибок:
   ```php
   // В начале index.php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

2. Проверьте логи:
   ```bash
   # XAMPP Windows
   C:\xampp\apache\logs\error.log
   
   # XAMPP Linux/macOS
   /Applications/XAMPP/logs/error_log
   
   # Linux VPS
   sudo tail -f /var/log/apache2/error.log
   ```

---

### Проблема: CSRF токен не совпадает

**Решение:**
1. Проверьте, что сессии работают:
   ```php
   <?php
   session_start();
   var_dump($_SESSION);
   ?>
   ```

2. Убедитесь, что cookies включены в браузере

3. Проверьте настройки сессий в `php.ini`:
   ```ini
   session.cookie_httponly = On
   session.use_strict_mode = 1
   ```

---

### Проблема: Медленная работа

**Решение:**
1. Включите кеширование в `.htaccess` (уже настроено)
2. Оптимизируйте MySQL:
   ```sql
   ANALYZE TABLE events, users, participants;
   ```
3. Включите OpCache в `php.ini`:
   ```ini
   opcache.enable=1
   opcache.memory_consumption=128
   ```

---

## 📊 Мониторинг производительности

### Установка инструментов мониторинга:

```bash
# Linux
sudo apt install htop iotop nethogs -y

# Мониторинг Apache
sudo apt install apache2-utils -y
ab -n 1000 -c 10 http://localhost/
```

### Настройка логирования:

```php
// В config.php
define('LOG_ERRORS', true);
define('LOG_FILE', __DIR__ . '/logs/app.log');

// Создайте папку
mkdir logs
chmod 777 logs
```

---

## 🔐 Дополнительная безопасность

### 1. Скрыть версию PHP:
```ini
# php.ini
expose_php = Off
```

### 2. Защита от DDoS:
```bash
# Установите Fail2Ban
sudo apt install fail2ban -y
```

### 3. Регулярные бэкапы:
```bash
#!/bin/bash
# backup.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/sport-org"

mkdir -p $BACKUP_DIR

# Бэкап БД
mysqldump -u sport_user -p sport_events > $BACKUP_DIR/db_$DATE.sql

# Бэкап файлов
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/sport-org

echo "Backup completed: $DATE"
```

Добавьте в cron:
```bash
sudo crontab -e
# Каждый день в 3:00
0 3 * * * /path/to/backup.sh
```

---

## 📱 Тестирование

### Локальное тестирование:
```bash
# PHP встроенный сервер
cd sport-org
php -S localhost:8000

# Откройте http://localhost:8000
```

### Тестовые учётные записи:
- **Admin**: `admin` / `123456`
- **User1**: `user1` / `123456`
- **User2**: `user2` / `123456`

---

## 📞 Поддержка

При возникновении проблем:
1. Проверьте логи ошибок
2. Убедитесь в соответствии требованиям
3. Проверьте права на файлы и папки
4. Протестируйте подключение к БД
5. Очистите кеш браузера

---

**Успешного развёртывания! 🚀**
