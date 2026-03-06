<?php
require_once 'includes/db.php';

echo "=== ПУТИ К КАРТИНКАМ ===\n\n";

echo "1. В КОДЕ (locations.php, строка 196):\n";
echo "   uploads/locations/[имя_файла]\n\n";

echo "2. В БАЗЕ ДАННЫХ (таблица locations, колонка 'image'):\n";
$locs = fetchAll($pdo, 'SELECT id, name, image FROM locations');
foreach ($locs as $loc) {
    echo "   ID {$loc['id']}: {$loc['name']}\n";
    echo "       Image: {$loc['image']}\n";
}

echo "\n3. ФИЗИЧЕСКИЙ ПУТЬ НА СЕРВЕРЕ:\n";
echo "   " . realpath(__DIR__ . '/uploads/locations') . "\n\n";

echo "4. ФАЙЛЫ В ПАПКЕ:\n";
$files = scandir(__DIR__ . '/uploads/locations');
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        $size = filesize(__DIR__ . '/uploads/locations/' . $file);
        echo "   - " . $file . " (" . $size . " bytes)\n";
    }
}
?>
