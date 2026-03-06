# 📚 Справочник по компонентам

## Описание ключевых файлов и их назначение

### 🗄️ База данных

#### **database.sql**
SQL скрипт для создания всей структуры базы данных.

**Содержит:**
- 7 таблиц с полными определениями
- Внешние ключи и индексы для оптимизации
- Триггеры для автоматического обновления данных
- Представления (Views) для удобных выборок
- Тестовые данные (4 локации)

**Триггеры:**
- `update_participants_count_insert` - обновление счётчика участников при записи
- `update_participants_count_update` - обновление при изменении статуса
- `update_participants_count_delete` - обновление при отмене записи
- `update_user_rating` - пересчёт рейтинга пользователя

---

### 🔧 Подключение к БД

#### **includes/db.php**
Безопасное подключение к MySQL через PDO.

**Основные функции:**

```php
// Выполнить запрос
$stmt = query($pdo, "SELECT * FROM users WHERE id = ?", [$userId]);

// Получить одну запись
$user = fetchOne($pdo, "SELECT * FROM users WHERE id = ?", [$userId]);

// Получить все записи
$events = fetchAll($pdo, "SELECT * FROM events WHERE status = ?", ['Открыто']);

// Вставить запись и получить ID
$newId = insert($pdo, "INSERT INTO users (username, email) VALUES (?, ?)", [$username, $email]);

// Обновить/удалить записи
$affected = execute($pdo, "UPDATE users SET rating = ? WHERE id = ?", [9.5, $userId]);
```

**Безопасность:**
- Использует PDO с подготовленными запросами
- Защита от SQL-инъекций
- Логирование ошибок без показа деталей пользователю

---

### 🛠️ Вспомогательные функции

#### **includes/functions.php**
Набор полезных функций для всего приложения.

**Функции авторизации:**
```php
isLoggedIn()              // Проверка авторизации
getCurrentUserId()         // ID текущего пользователя
getCurrentUser()           // Данные текущего пользователя
requireAuth()              // Требовать авторизацию (редирект если нет)
```

**Функции безопасности:**
```php
clean($data)               // Защита от XSS
generateCsrfToken()        // Генерация CSRF токена
verifyCsrfToken()          // Проверка CSRF токена
csrfField()                // HTML поле с CSRF токеном
```

**Функции валидации:**
```php
isValidEmail($email)       // Проверка email
isValidPhone($phone)       // Проверка телефона
```

**Flash-сообщения:**
```php
setFlashMessage('success', 'Операция успешна')
getFlashMessage()          // Получить и удалить
displayFlashMessage()      // HTML для отображения
```

**Форматирование:**
```php
formatDate($date)          // "18 февраля 2026"
formatTime($time)          // "14:30"
formatDateTime($date, $time) // "18 февраля 2026 в 14:30"
timeAgo($datetime)         // "2 часа назад"
plural($n, 'день', 'дня', 'дней') // Склонение
```

**Работа с файлами:**
```php
uploadFile($file, $dir, $types, $maxSize) // Загрузка файла
deleteFile($path)          // Удаление файла
```

**Пагинация:**
```php
paginate($total, $perPage, $current) // Данные для пагинации
renderPagination($total, $current, $url) // HTML пагинации
```

---

### 🎨 Шапка и подвал

#### **includes/header.php**
Верхняя часть всех страниц.

**Содержит:**
- DOCTYPE и мета-теги
- Подключение Bootstrap 5 и стилей
- Навигационное меню
- Логика для авторизованных/неавторизованных пользователей
- Счётчик непрочитанных уведомлений

**Использование:**
```php
<?php
$pageTitle = 'Название страницы';
require_once 'includes/header.php';
?>
```

#### **includes/footer.php**
Нижняя часть всех страниц.

**Содержит:**
- Информация о проекте
- Быстрые ссылки
- Контакты и соцсети
- Копирайт
- Кнопка "Наверх"
- Подключение JavaScript

**Использование:**
```php
<?php require_once 'includes/footer.php'; ?>
```

---

### 👤 Регистрация и авторизация

#### **register.php**
Регистрация нового пользователя.

**Валидация:**
- Имя пользователя: 3-50 символов, латиница, цифры, подчёркивание
- Email: стандартная валидация
- Пароль: минимум 6 символов
- Телефон: опционально, 10-11 цифр
- Проверка уникальности username и email

**Безопасность:**
- CSRF защита
- Хеширование пароля (password_hash)
- XSS защита
- SQL-инъекции защита (PDO)

#### **login.php**
Вход в систему.

**Функции:**
- Авторизация по email и паролю
- Проверка пароля (password_verify)
- "Запомнить меня" на 30 дней
- Редирект на сохранённый URL после входа

#### **logout.php**
Выход из системы.

**Действия:**
- Удаление всех данных сессии
- Удаление cookies "Запомнить меня"
- Уничтожение сессии
- Редирект на главную

---

### 🎨 Стили

#### **css/style.css**
Основные стили приложения.

**Основные секции:**
- Общие стили и переменные
- Навигация и меню
- Карточки событий
- Формы и кнопки
- Бейджи и рейтинги
- Фильтры и пагинация
- Адаптивные стили для мобильных
- Анимации

**Цветовые схемы для видов спорта:**
```css
.sport-football   /* Фиолетовый градиент */
.sport-volleyball /* Розовый градиент */
.sport-basketball /* Оранжевый градиент */
```

**Уровни сложности:**
```css
.badge-beginner     /* Зелёный - начинающий */
.badge-amateur      /* Голубой - любитель */
.badge-advanced     /* Оранжевый - продвинутый */
.badge-professional /* Красный - профессионал */
```

---

### 📜 JavaScript

#### **js/script.js**
Клиентские скрипты.

**Основные функции:**

```javascript
// Кнопка "Наверх"
initScrollToTop()

// Автозакрытие алертов
initAlertAutoClose()

// Подтверждение действий
initConfirmDialogs()

// Валидация форм
initFormValidation()

// Подсказки Bootstrap
initTooltips()

// Валидация даты/времени
initDateTimeValidation()

// AJAX запись на событие
joinEvent(eventId, button)

// AJAX отмена записи
leaveEvent(eventId, button)

// Показ уведомлений
showNotification(message, type)

// Фильтрация событий
filterEvents()

// Предпросмотр изображения
previewImage(input, previewId)

// Добавление комментария
addComment(eventId)

// Форматирование телефона
formatPhone(input)

// Копирование в буфер
copyToClipboard(text)

// Живой поиск
liveSearch(query)
```

---

### 🔒 Безопасность

#### Реализованные меры:

**1. SQL-инъекции:**
- PDO с подготовленными запросами
- Параметризованные запросы везде

**2. XSS (Cross-Site Scripting):**
- htmlspecialchars() для всех выводимых данных
- Функция clean() для очистки

**3. CSRF (Cross-Site Request Forgery):**
- CSRF токены для всех форм
- Проверка токенов при обработке

**4. Пароли:**
- password_hash() с bcrypt
- password_verify() для проверки
- Минимум 6 символов

**5. Сессии:**
- HttpOnly cookies
- Регенерация session ID
- Таймаут сессии

**6. Файлы:**
- Проверка типов файлов
- Проверка размера
- Уникальные имена файлов

---

### 📊 Структура таблиц

#### **users** (пользователи)
```sql
id, username, email, password_hash, phone, 
avatar, rating, total_events, attended_events, created_at
```

#### **events** (события)
```sql
id, creator_id, title, sport_type, location_id, 
event_date, event_time, duration, max_participants,
current_participants, skill_level, description, status
```

#### **locations** (места)
```sql
id, name, address, city, sport_types, 
description, latitude, longitude, image
```

#### **participants** (участники)
```sql
id, event_id, user_id, status, joined_at, notes
```

#### **ratings** (рейтинги)
```sql
id, event_id, rated_user_id, rater_user_id, 
rating, comment, created_at
```

#### **notifications** (уведомления)
```sql
id, user_id, event_id, type, message, is_read, created_at
```

#### **comments** (комментарии)
```sql
id, event_id, user_id, comment, created_at
```

---

### 🎯 Статусы и ENUM

**Статусы событий:**
- `Открыто` - можно записаться
- `Закрыто` - запись закрыта
- `Завершено` - событие прошло
- `Отменено` - событие отменено

**Виды спорта:**
- `Футбол`
- `Волейбол`
- `Баскетбол`

**Уровни игры:**
- `Начинающий`
- `Любитель`
- `Продвинутый`
- `Профессионал`

**Статусы участников:**
- `Записан` - записался на событие
- `Подтвержден` - организатор подтвердил
- `Не пришёл` - не явился на игру
- `Пришёл` - пришёл на игру
- `Отменил` - отменил участие

**Типы уведомлений:**
- `Запись` - кто-то записался
- `Отмена` - отмена записи
- `Напоминание` - напоминание о событии
- `Изменение` - изменение события
- `Оценка` - получена оценка
- `Комментарий` - новый комментарий

---

### 📱 Bootstrap компоненты

**Используемые компоненты:**
- Navbar (навигация)
- Cards (карточки)
- Forms (формы)
- Buttons (кнопки)
- Alerts (уведомления)
- Badges (бейджи)
- Modals (модальные окна)
- Dropdowns (выпадающие меню)
- Pagination (пагинация)
- Progress bars (прогресс-бары)
- Tooltips (подсказки)

---

### 🔗 Полезные ссылки

**Документация:**
- Bootstrap 5: https://getbootstrap.com/docs/5.3/
- PHP PDO: https://www.php.net/manual/ru/book.pdo.php
- MySQL: https://dev.mysql.com/doc/

**Иконки:**
- Bootstrap Icons: https://icons.getbootstrap.com/

---

## 💡 Советы по разработке

1. **Всегда используйте подготовленные запросы**
   ```php
   $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
   $stmt->execute([$userId]);
   ```

2. **Очищайте вывод данных**
   ```php
   echo clean($userData);
   ```

3. **Используйте CSRF токены в формах**
   ```php
   <?php echo csrfField(); ?>
   ```

4. **Проверяйте авторизацию**
   ```php
   requireAuth(); // В начале защищённой страницы
   ```

5. **Используйте flash-сообщения**
   ```php
   setFlashMessage('success', 'Операция выполнена');
   redirect('index.php');
   ```

---

**Удачной разработки!** 🚀
