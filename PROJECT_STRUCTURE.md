# SportConnect - Структура проекта

Полная структура созданного веб-приложения для организации спортивных мероприятий.

## 📁 Структура директорий

```
sport-org/
│
├── 📂 includes/              # Общие компоненты
│   ├── db.php               # Подключение к БД и helper-функции
│   ├── functions.php        # Утилиты и вспомогательные функции
│   ├── header.php           # Шапка сайта с навигацией
│   └── footer.php           # Подвал сайта
│
├── 📂 css/                   # Стили
│   └── style.css            # Пользовательские стили
│
├── 📂 js/                    # JavaScript
│   └── script.js            # Интерактивность и AJAX
│
├── 📂 ajax/                  # AJAX обработчики
│   ├── join_event.php       # Присоединиться к событию
│   ├── leave_event.php      # Покинуть событие
│   ├── add_comment.php      # Добавить комментарий
│   ├── mark_notification_read.php  # Отметить уведомление
│   ├── get_unread_count.php # Получить счётчик уведомлений
│   └── search.php           # Живой поиск
│
├── 📂 uploads/               # Загруженные файлы
│   ├── avatars/             # Аватары пользователей
│   │   └── .gitkeep
│   └── locations/           # Фото мест
│       └── .gitkeep
│
├── 📄 index.php             # Главная: список событий
├── 📄 register.php          # Регистрация
├── 📄 login.php             # Вход
├── 📄 logout.php            # Выход
├── 📄 profile.php           # Профиль пользователя
├── 📄 create_event.php      # Создание события
├── 📄 event.php             # Страница события
├── 📄 my_events.php         # Мои события
├── 📄 locations.php         # Список мест
├── 📄 add_location.php      # Добавить место
├── 📄 notifications.php     # Уведомления
│
├── 📄 database.sql          # SQL схема БД
├── 📄 config.example.php    # Пример конфигурации
├── 📄 .htaccess             # Настройки Apache
├── 📄 .gitignore            # Исключения Git
│
└── 📂 docs/                  # Документация
    ├── README.md            # Главный README
    ├── INSTALL.md           # Инструкция по установке
    ├── COMPONENTS.md        # Справочник компонентов
    └── QUICKSTART.md        # Быстрый старт
```

## 📊 База данных (7 таблиц)

### Таблицы:
1. **users** - Пользователи (18 полей)
2. **events** - События (15 полей)
3. **locations** - Места проведения (9 полей)
4. **participants** - Участники событий (5 полей)
5. **ratings** - Оценки пользователей (6 полей)
6. **notifications** - Уведомления (7 полей)
7. **comments** - Комментарии (5 полей)

### Дополнительно:
- 2 триггера (обновление счётчиков)
- 2 представления (events_full, user_stats)
- 15+ индексов для производительности
- Foreign keys с каскадным удалением

## 🎯 Основные страницы

### Публичные:
- **index.php** - Список событий с фильтрами
- **event.php** - Детали события
- **locations.php** - Места для игр
- **profile.php** - Профиль (просмотр любого)

### Требующие авторизации:
- **create_event.php** - Создать событие
- **my_events.php** - Мои события (3 вкладки)
- **add_location.php** - Добавить место
- **notifications.php** - Уведомления

### Аутентификация:
- **register.php** - Регистрация
- **login.php** - Вход (с "Запомнить меня")
- **logout.php** - Выход

## 🔌 AJAX API (6 эндпоинтов)

1. **ajax/join_event.php** - POST: Вступить в событие
2. **ajax/leave_event.php** - POST: Покинуть событие
3. **ajax/add_comment.php** - POST: Добавить комментарий
4. **ajax/mark_notification_read.php** - POST: Отметить прочитанным
5. **ajax/get_unread_count.php** - GET: Счётчик непрочитанных
6. **ajax/search.php** - GET: Живой поиск

Все эндпоинты возвращают JSON и проверяют CSRF токены.

## 🛠 Вспомогательные компоненты

### includes/functions.php (30+ функций):

#### Аутентификация:
- `isLoggedIn()` - Проверка авторизации
- `requireAuth()` - Требовать авторизацию
- `getUserId()` - Получить ID текущего юзера
- `getUser()` - Получить данные юзера

#### Безопасность:
- `clean($text)` - XSS защита (htmlspecialchars)
- `generateCsrfToken()` - Генерация CSRF токена
- `verifyCsrfToken()` - Проверка CSRF
- `csrfField()` - HTML поле с токеном

#### Валидация:
- `validateUsername($username)` - Валидация имени
- `validateEmail($email)` - Валидация email
- `validatePassword($password)` - Валидация пароля
- `validatePhone($phone)` - Валидация телефона

#### Форматирование:
- `formatDate($date)` - Форматировать дату (дд.мм.гггг)
- `formatTime($time)` - Форматировать время (чч:мм)
- `timeAgo($datetime)` - Относительное время
- `plural($number, $forms)` - Склонение (1 событие, 2 события...)

#### Flash сообщения:
- `setFlashMessage($type, $message)` - Установить сообщение
- `getFlashMessage()` - Получить и удалить сообщение
- `hasFlashMessage()` - Проверить наличие

#### Файлы:
- `uploadFile($file, $dir, $allowed, $maxSize)` - Загрузка файла
- `deleteFile($path)` - Удаление файла

#### Пагинация:
- `paginate($totalItems, $perPage, $currentPage)` - Данные пагинации
- `renderPagination($pagination, $baseUrl)` - HTML пагинации

#### Другое:
- `redirect($url)` - Редирект
- `getSportEmoji($sport)` - Эмодзи для спорта
- `getSkillLevelBadge($level)` - Badge для уровня

### includes/db.php:
- `query($pdo, $sql, $params)` - Выполнить запрос
- `fetchAll($pdo, $sql, $params)` - Получить все строки
- `fetchOne($pdo, $sql, $params)` - Получить одну строку
- `insert($pdo, $sql, $params)` - Вставка (возвращает ID)
- `execute($pdo, $sql, $params)` - Выполнить (UPDATE/DELETE)

## 🎨 Дизайн

### Фреймворк:
- **Bootstrap 5.3** - Сетка и компоненты
- **Bootstrap Icons** - Иконки

### Цветовая схема (по видам спорта):
- **Футбол**: `#6f42c1` (фиолетовый градиент)
- **Волейбол**: `#e83e8c` (розовый)
- **Баскетбол**: `#fd7e14` (оранжевый)

### Кастомизация:
- Градиентные карточки событий
- Анимации при наведении
- Прогресс-бары для участников
- Адаптивная вёрстка (мобильные, планшеты, десктопы)

## 🔒 Безопасность

1. **SQL Injection** - PDO Prepared Statements везде
2. **XSS** - Функция `clean()` для всего вывода
3. **CSRF** - Токены во всех формах
4. **Пароли** - `password_hash()` bcrypt
5. **Сессии** - HttpOnly cookies, регенерация ID
6. **Загрузки** - Проверка типов и размеров файлов

## 📦 Зависимости

### PHP:
- PHP 7.4+
- PDO extension
- GD или ImageMagick (для обработки изображений)

### База данных:
- MySQL 5.7+ или MariaDB 10.2+

### Веб-сервер:
- Apache 2.4+ с mod_rewrite
- Nginx 1.18+ (с правильным конфигом)

### Frontend (CDN):
- Bootstrap 5.3.0
- Bootstrap Icons 1.10.0

## 🚀 Функционал

### ✅ Реализовано:

1. **Регистрация и авторизация**
   - Валидация полей
   - Хеширование паролей
   - "Запомнить меня"
   
2. **События**
   - Создание с валидацией
   - Просмотр с фильтрами
   - Присоединение/отказ
   - Комментарии
   - История
   
3. **Пользователи**
   - Профили с аватарами
   - Статистика (рейтинг, события, посещаемость)
   - Редактирование профиля
   
4. **Места**
   - Добавление с фото
   - Список с фильтрами
   - Привязка к событиям
   
5. **Уведомления**
   - Типы: участие, отмена, комментарий, оценка
   - Непрочитанные счётчики
   - Фильтрация
   
6. **Рейтинг**
   - Оценки после событий
   - Автоматический расчёт среднего
   - История оценок

### 📱 Адаптивность:
- Мобильные (< 768px)
- Планшеты (768px - 1024px)
- Десктопы (> 1024px)

## 📝 Документация

Полная документация находится в папке проекта:

1. **README.md** - Общее описание проекта
2. **INSTALL.md** - Пошаговая установка
3. **COMPONENTS.md** - Справочник компонентов
4. **QUICKSTART.md** - Быстрый старт для разработчиков

## 🧪 Тестовые данные

В `database.sql` включены тестовые данные:
- 3 пользователя (admin, user1, user2)
- 5 мест проведения
- 8 событий
- Участники и комментарии

Все пароли: `123456`

## 📈 Статистика проекта

- **Всего файлов**: 30+
- **Строк кода PHP**: ~4500
- **Строк CSS**: ~800
- **Строк JavaScript**: ~500
- **SQL таблиц**: 7
- **AJAX эндпоинтов**: 6
- **Функций-хелперов**: 30+

## 🎓 Технологии

- **Backend**: Pure PHP 7.4+ (без фреймворков)
- **Database**: MySQL 5.7+ с triggers и views
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **UI Framework**: Bootstrap 5.3
- **Icons**: Bootstrap Icons
- **AJAX**: Fetch API
- **Security**: PDO, password_hash, CSRF tokens

## 🔧 Конфигурация

Скопируйте `config.example.php` в `config.php` и настройте:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sport_events');
define('DB_USER', 'your_user');
define('DB_PASS', 'your_password');
define('SITE_URL', 'http://your-domain.com');
```

## 📞 Поддержка

При возникновении проблем проверьте:
1. Права на папку `uploads/` (777 или 755)
2. Подключение к базе данных
3. Настройки PHP (upload_max_filesize, post_max_size)
4. Apache mod_rewrite включен
5. Логи ошибок PHP

---

**Версия**: 1.0.0  
**Дата создания**: 2024  
**Статус**: Production Ready ✅
