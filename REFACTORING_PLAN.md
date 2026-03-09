# 📋 ПЛАН РЕФАКТОРИНГА SPORT-ORG

## ✅ ЗАДАЧА 1: УДАЛЕНИЕ ЛИШНЕГО КОДА

### Найдено дублирования и мусора:
- [ ] `test_create_event.php` - тестовый файл (удалить)
- [ ] `test_system.php` - тестовый файл (удалить)
- [ ] `debug_schema.php` - файл отладки (удалить)
- [ ] `update_location_images.php` - скрипт обновления (удалить)
- [ ] `show_image_paths.php` - скрипт отладки (удалить)
- [ ] `check_images.php` - скрипт отладки (удалить)
- [ ] `create_images.php` - скрипт отладки (удалить)
- [ ] `download_images.php` - скрипт отладки (удалить)
- [ ] `ARCHITECTURE.md` - дублирует PROJECT_STRUCTURE.md (удалить)
- [ ] `COMPONENTS.md` - устаревшая документация (удалить)
- [ ] `COMPLETION.md` - устаревшая документация (удалить)
- [ ] `DEPLOYMENT.md` - устаревшая документация (удалить)

### В functions.php найдена русская и английская документация (переделать на эстонский)
### В footer.php найден русский текст (переделать на эстонский)

---

## ✅ ЗАДАЧА 2: РЕОРГАНИЗАЦИЯ СТРУКТУРЫ

### Новая структура:

```
sport-org/
├── public/
│   ├── assets/
│   │   ├── images/
│   │   │   ├── gyms/           ← НОВАЯ папка для картинок залов
│   │   │   └── icons/
│   │   ├── css/
│   │   │   └── style.css
│   │   ├── js/
│   │   │   └── script.js
│   │   └── favicon.ico
│   ├── uploads/
│   │   ├── avatars/
│   │   └── locations/
│   └── index.php (переместить сюда точку входа)
│
├── src/
│   ├── components/             ← НОВАЯ папка
│   │   ├── Header.php
│   │   ├── Footer.php
│   │   └── Navigation.php
│   ├── pages/                  ← НОВАЯ папка
│   │   ├── auth/
│   │   │   ├── login.php
│   │   │   ├── register.php
│   │   │   └── logout.php
│   │   ├── events/
│   │   │   ├── list.php
│   │   │   ├── create.php
│   │   │   ├── view.php
│   │   │   └── my.php
│   │   ├── locations/
│   │   │   ├── list.php
│   │   │   └── add.php
│   │   ├── user/
│   │   │   ├── profile.php
│   │   │   └── notifications.php
│   │   └── home.php
│   ├── services/               ← НОВАЯ папка
│   │   ├── Database.php
│   │   ├── Auth.php
│   │   ├── Event.php
│   │   └── User.php
│   ├── config/
│   │   └── config.php
│   ├── ajax/
│   │   ├── join.php
│   │   ├── leave.php
│   │   ├── comment.php
│   │   ├── notification.php
│   │   └── search.php
│   └── helpers/
│       ├── functions.php
│       ├── security.php
│       └── translation.php
│
├── docs/
│   ├── DATABASE.md
│   └── SETUP.md
│
├── database.sql
├── .htaccess
├── .gitignore
├── composer.json
└── README.md
```

---

## ✅ ЗАДАЧА 3: ЭСТОНСКИЙ UI

### Переводы (примеры):

| Русский | Английский | Эстонский |
|---------|-----------|-----------|
| Создать событие | Create event | Loo üritus |
| Присоединиться | Join | Liitu |
| Тренировка | Training | Treening |
| Мои события | My events | Minu üritused |
| Запросы | Requests | Kutsed |
| Пользователи | Users | Kasutajad |
| Профиль | Profile | Profiil |
| Вход | Login | Logi sisse |
| Регистрация | Register | Registreeru |
| Места | Locations | Kohad |
| Отзывы | Reviews | Arvustused |
| Рейтинг | Rating | Reiting |
| Вышли | Leave | Välju |
| Отмена | Cancel | Tühista |
| Сохранить | Save | Salvesta |

---

## ✅ ЗАДАЧА 4: НОВЫЙ FOOTER

### Структура:

```
Kontaktid
─────────
Telefon: +372 555 1234
Email: info@sportorg.ee
Asukoht: Tallinn, Eesti

Navigatsioon
─────────
Avaleht | Treeningud | Profiil | Kontakt

© 2026 SportOrg. Kõik õigused kaitstud.
```

---

## ✅ ЗАДАЧА 5: УПРОЩЕНИЕ КАРТИНОК ЗАЛОВ

### Текущее состояние:
- Картинки хранятся в БД как URLs
- Сложный скрипт для скачивания/создания

### Новое состояние:
- Картинки в `/assets/gyms/gym1.jpg`, `gym2.jpg` и т.д.
- Простой компонент `<GymImage name="Kadriorg Stadium" />`
- В БД просто name локации, картинки подбираются автоматически

---

## 📊 ФАЙЛЫ К УДАЛЕНИЮ:

```
❌ test_create_event.php
❌ test_system.php
❌ debug_schema.php
❌ update_location_images.php
❌ show_image_paths.php
❌ check_images.php
❌ create_images.php
❌ download_images.php
❌ ARCHITECTURE.md
❌ COMPONENTS.md
❌ COMPLETION.md
❌ DEPLOYMENT.md
❌ QUICKSTART.md
```

---

## 📝 ФАЙЛЫ К ИЗМЕНЕНИЮ:

```
✏️  includes/functions.php → src/helpers/functions.php
✏️  includes/header.php → src/components/Header.php
✏️  includes/footer.php → src/components/Footer.php
✏️  includes/db.php → src/services/Database.php
✏️  css/style.css → public/assets/css/style.css
✏️  js/script.js → public/assets/js/script.js
✏️  Все PHP страницы → переместить и изменить пути
```

---

## 🎯 ПОРЯДОК ВЫПОЛНЕНИЯ:

1. ✅ Создать новую структуру папок
2. ✅ Перенести файлы в нужные места
3. ✅ Обновить все пути подключения
4. ✅ Перевести UI на эстонский
5. ✅ Создать новый Footer компонент
6. ✅ Добавить систему картинок залов
7. ✅ Удалить ненужные файлы
8. ✅ Тестирование

---

## ⚠️ РИСКИ:

- Функциональность должна остаться ИДЕНТИЧНОЙ
- Все пути обновить осторожно
- Тестировать на localhost перед коммитом
