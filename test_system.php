<?php
/**
 * Скрипт проверки системы
 * 
 * Проверяет все требования для работы приложения
 * Запустите: php test_system.php
 */

echo "==============================================\n";
echo "   Проверка системы SportConnect\n";
echo "==============================================\n\n";

$errors = [];
$warnings = [];
$success = [];

// Проверка версии PHP
echo "1. Проверка версии PHP...\n";
$phpVersion = phpversion();
if (version_compare($phpVersion, '7.4.0', '>=')) {
    $success[] = "✓ PHP версия: $phpVersion";
} else {
    $errors[] = "✗ PHP версия слишком старая: $phpVersion (требуется 7.4+)";
}

// Проверка расширений PHP
echo "2. Проверка расширений PHP...\n";
$requiredExtensions = ['pdo', 'pdo_mysql', 'gd', 'mbstring', 'fileinfo'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        $success[] = "✓ Расширение $ext установлено";
    } else {
        $errors[] = "✗ Расширение $ext не найдено";
    }
}

// Проверка подключения к БД
echo "3. Проверка подключения к базе данных...\n";
if (file_exists('config.php')) {
    require_once 'config.php';
    
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        $success[] = "✓ Подключение к БД успешно";
        
        // Проверка таблиц
        $tables = ['users', 'events', 'locations', 'participants', 'ratings', 'notifications', 'comments'];
        $stmt = $pdo->query("SHOW TABLES");
        $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($tables as $table) {
            if (in_array($table, $existingTables)) {
                $success[] = "✓ Таблица $table существует";
            } else {
                $errors[] = "✗ Таблица $table не найдена";
            }
        }
        
    } catch (PDOException $e) {
        $errors[] = "✗ Ошибка подключения к БД: " . $e->getMessage();
    }
} else {
    $errors[] = "✗ Файл config.php не найден. Скопируйте config.example.php";
}

// Проверка файловой структуры
echo "4. Проверка файловой структуры...\n";
$requiredDirs = [
    'includes',
    'css',
    'js',
    'ajax',
    'uploads',
    'uploads/avatars',
    'uploads/locations'
];

foreach ($requiredDirs as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir) || strpos($dir, 'uploads') !== false) {
            if (is_writable($dir)) {
                $success[] = "✓ Директория $dir существует и доступна для записи";
            } else {
                $warnings[] = "⚠ Директория $dir существует, но не доступна для записи";
            }
        } else {
            $success[] = "✓ Директория $dir существует";
        }
    } else {
        $errors[] = "✗ Директория $dir не найдена";
    }
}

// Проверка файлов
$requiredFiles = [
    'index.php',
    'includes/db.php',
    'includes/functions.php',
    'includes/header.php',
    'includes/footer.php',
    'css/style.css',
    'js/script.js',
    'database.sql',
    '.htaccess'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        $success[] = "✓ Файл $file существует";
    } else {
        $errors[] = "✗ Файл $file не найден";
    }
}

// Проверка прав на запись
echo "5. Проверка прав на запись...\n";
$uploadDirs = ['uploads', 'uploads/avatars', 'uploads/locations'];
foreach ($uploadDirs as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            $success[] = "✓ Директория $dir доступна для записи";
        } else {
            $errors[] = "✗ Директория $dir не доступна для записи (установите chmod 777)";
        }
    }
}

// Проверка настроек PHP
echo "6. Проверка настроек PHP...\n";

$uploadMaxFilesize = ini_get('upload_max_filesize');
$postMaxSize = ini_get('post_max_size');
$maxExecutionTime = ini_get('max_execution_time');
$memoryLimit = ini_get('memory_limit');

$success[] = "  upload_max_filesize: $uploadMaxFilesize";
$success[] = "  post_max_size: $postMaxSize";
$success[] = "  max_execution_time: {$maxExecutionTime}s";
$success[] = "  memory_limit: $memoryLimit";

if (intval($uploadMaxFilesize) < 5) {
    $warnings[] = "⚠ upload_max_filesize слишком мал (рекомендуется минимум 5M)";
}

// Проверка Apache модулей (если Apache)
echo "7. Проверка веб-сервера...\n";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    if (in_array('mod_rewrite', $modules)) {
        $success[] = "✓ Apache mod_rewrite включен";
    } else {
        $errors[] = "✗ Apache mod_rewrite не включен";
    }
} else {
    $warnings[] = "⚠ Не удалось проверить модули Apache (возможно, используется другой сервер)";
}

// Проверка timezone
echo "8. Проверка timezone...\n";
$timezone = date_default_timezone_get();
$success[] = "✓ Timezone: $timezone";

// Вывод результатов
echo "\n==============================================\n";
echo "             РЕЗУЛЬТАТЫ ПРОВЕРКИ\n";
echo "==============================================\n\n";

if (!empty($success)) {
    echo "УСПЕШНО:\n";
    foreach ($success as $msg) {
        echo "$msg\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "ПРЕДУПРЕЖДЕНИЯ:\n";
    foreach ($warnings as $msg) {
        echo "$msg\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "ОШИБКИ:\n";
    foreach ($errors as $msg) {
        echo "$msg\n";
    }
    echo "\n";
}

// Итоговая оценка
echo "==============================================\n";
if (empty($errors)) {
    if (empty($warnings)) {
        echo "   ✅ СИСТЕМА ПОЛНОСТЬЮ ГОТОВА К РАБОТЕ!\n";
    } else {
        echo "   ⚠️  СИСТЕМА ГОТОВА (есть предупреждения)\n";
    }
} else {
    echo "   ❌ ТРЕБУЕТСЯ ИСПРАВЛЕНИЕ ОШИБОК\n";
}
echo "==============================================\n\n";

// Дополнительная информация
if (!empty($errors)) {
    echo "Рекомендации по исправлению:\n";
    echo "1. Проверьте установку всех требуемых расширений PHP\n";
    echo "2. Создайте файл config.php из config.example.php\n";
    echo "3. Импортируйте database.sql в MySQL\n";
    echo "4. Установите права 777 на директории uploads/*\n";
    echo "5. Включите mod_rewrite в Apache\n\n";
}

echo "Для получения помощи см. INSTALL.md и DEPLOYMENT.md\n";

exit(empty($errors) ? 0 : 1);
