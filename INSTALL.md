# 📖 Подробная инструкция по установке

## Пошаговая установка приложения "Спортивные Игры"

### ✅ Шаг 1: Проверка требований

Убедитесь, что у вас установлено:

- **PHP 7.4+** (рекомендуется PHP 8.0+)
  ```bash
  php -v
  ```

- **MySQL 5.7+** или **MariaDB 10.2+**
  ```bash
  mysql --version
  ```

- **Apache** с mod_rewrite или **Nginx**
  ```bash
  apache2 -v
  # или
  nginx -v
  ```

### ✅ Шаг 2: Загрузка проекта

Скачайте проект в директорию веб-сервера:

**Для XAMPP (Windows):**
```
C:\xampp\htdocs\sport-org\
```

**Для MAMP (Mac):**
```
/Applications/MAMP/htdocs/sport-org/
```

**Для Linux:**
```
/var/www/html/sport-org/
```

### ✅ Шаг 3: Создание базы данных

#### Вариант 1: Через phpMyAdmin

1. Откройте phpMyAdmin: `http://localhost/phpmyadmin`
2. Создайте новую базу данных:
   - Нажмите "Создать базу данных"
   - Имя: `sport_org`
   - Кодировка: `utf8mb4_unicode_ci`
3. Импортируйте SQL:
   - Выберите созданную БД
   - Перейдите на вкладку "Импорт"
   - Выберите файл `database.sql`
   - Нажмите "Вперёд"

#### Вариант 2: Через командную строку

```bash
# Вход в MySQL
mysql -u root -p

# Создание базы данных
CREATE DATABASE sport_org CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Выход
exit

# Импорт SQL файла
mysql -u root -p sport_org < database.sql
```

### ✅ Шаг 4: Настройка подключения к БД

Откройте файл `includes/db.php` и измените следующие строки:

```php
define('DB_HOST', 'localhost');      // Обычно localhost
define('DB_NAME', 'sport_org');      // Имя вашей БД
define('DB_USER', 'root');           // Ваш пользователь MySQL
define('DB_PASS', '');               // Ваш пароль MySQL (если есть)
```

**Для XAMPP:** обычно пароль пустой  
**Для MAMP:** пароль часто `root`  
**Для production:** используйте надёжный пароль

### ✅ Шаг 5: Настройка прав доступа

#### Windows (XAMPP/MAMP):
Права обычно устанавливаются автоматически. Пропустите этот шаг.

#### Linux/Mac:
```bash
# Переход в директорию проекта
cd /var/www/html/sport-org

# Установка владельца (замените www-data на вашего пользователя)
sudo chown -R www-data:www-data .

# Установка прав на папки
sudo find . -type d -exec chmod 755 {} \;

# Установка прав на файлы
sudo find . -type f -exec chmod 644 {} \;

# Особые права для папки uploads
sudo chmod -R 775 uploads/
```

### ✅ Шаг 6: Настройка веб-сервера

#### Apache (.htaccess уже создан)

Убедитесь, что mod_rewrite включён:

**Linux:**
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

**Windows (XAMPP):**
1. Откройте `C:\xampp\apache\conf\httpd.conf`
2. Найдите строку `#LoadModule rewrite_module modules/mod_rewrite.so`
3. Уберите `#` в начале строки
4. Перезапустите Apache

Также убедитесь, что AllowOverride установлен в All:
```apache
<Directory "/путь/к/проекту">
    AllowOverride All
</Directory>
```

#### Nginx

Добавьте в конфигурацию сайта:

```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/html/sport-org;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Перезапустите Nginx:
```bash
sudo systemctl restart nginx
```

### ✅ Шаг 7: Проверка установки

Откройте в браузере:
```
http://localhost/sport-org/
```

Вы должны увидеть главную страницу приложения.

### ✅ Шаг 8: Создание первого пользователя

1. Перейдите на страницу регистрации:
   ```
   http://localhost/sport-org/register.php
   ```

2. Заполните форму:
   - **Имя пользователя:** admin
   - **Email:** admin@example.com
   - **Пароль:** admin123
   - **Телефон:** +7 (999) 123-45-67 (необязательно)

3. Нажмите "Зарегистрироваться"

4. После успешной регистрации вы автоматически войдёте в систему

### ✅ Шаг 9: Добавление тестовых данных (опционально)

SQL скрипт уже содержит несколько тестовых локаций. Для добавления тестового события:

1. Войдите в систему
2. Нажмите "Создать событие"
3. Заполните данные и создайте первое событие

### 🔧 Решение проблем

#### Ошибка подключения к БД

**Ошибка:** "Ошибка подключения к базе данных"

**Решение:**
1. Проверьте, что MySQL запущен
2. Проверьте настройки в `includes/db.php`
3. Убедитесь, что БД создана и SQL скрипт импортирован

#### Ошибка 500 (Internal Server Error)

**Решение:**
1. Проверьте логи ошибок:
   - Apache: `/var/log/apache2/error.log`
   - Nginx: `/var/log/nginx/error.log`
   - PHP: проверьте php.ini для пути к логам

2. Включите отображение ошибок (только для разработки):
   ```php
   // Добавьте в начало index.php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

#### Не загружаются стили

**Решение:**
1. Проверьте, что файл `css/style.css` существует
2. Проверьте права доступа к файлу
3. Откройте консоль браузера (F12) и проверьте ошибки загрузки

#### Проблемы с загрузкой файлов

**Решение:**
1. Убедитесь, что папка `uploads/` существует
2. Проверьте права доступа: `chmod 775 uploads/`
3. Увеличьте лимиты в php.ini:
   ```ini
   upload_max_filesize = 10M
   post_max_size = 10M
   ```

### 📝 Конфигурация для production

**⚠️ ВАЖНО для production-сервера:**

1. **Отключите отображение ошибок:**
   ```php
   // includes/db.php
   error_reporting(0);
   ini_set('display_errors', 0);
   ```

2. **Используйте HTTPS:**
   - Получите SSL сертификат (Let's Encrypt)
   - Настройте редирект с HTTP на HTTPS

3. **Измените пароль БД:**
   - Создайте отдельного пользователя MySQL
   - Используйте сложный пароль

4. **Настройте регулярные бэкапы:**
   ```bash
   # Пример бэкапа БД
   mysqldump -u root -p sport_org > backup_$(date +%Y%m%d).sql
   ```

5. **Защитите важные файлы:**
   ```bash
   chmod 600 includes/db.php
   ```

### 🎉 Готово!

Приложение установлено и готово к использованию!

**Следующие шаги:**
1. Создайте несколько пользователей
2. Добавьте места для игр
3. Создайте события
4. Протестируйте запись на события
5. Оцените участников после завершения события

### 📞 Помощь

Если возникли проблемы:
1. Проверьте [README.md](README.md)
2. Откройте issue на GitHub
3. Напишите на email: info@sportgames.ru

---

**Успешной установки!** 🚀
