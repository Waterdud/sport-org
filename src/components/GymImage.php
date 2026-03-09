<?php
/**
 * GymImage компонент - управление картинками залов
 * 
 * Использование:
 * <?php renderGymImage('Kadriorg Stadium'); ?>
 */

/**
 * Получить путь к картинке зала (автоматическое соответствие)
 * 
 * @param string $locationName - название места
 * @return string путь к картинке или placeholder
 */
function getGymImagePath($locationName) {
    // Нормализуем название
    $normalized = strtolower(trim($locationName));
    
    // Маппинг названий к файлам
    $imageMap = [
        'kadriorg stadium' => 'kadriorg.jpg',
        'kalev sports hall' => 'kalev.jpg',
        'tartu university sports ground' => 'tartu.jpg',
        'narva beach volleyball court' => 'narva.jpg',
        'pärnu basketball arena' => 'parnu.jpg',
    ];
    
    // Ищем точное совпадение
    if (isset($imageMap[$normalized])) {
        $filename = $imageMap[$normalized];
        $filepath = GYMS_PATH . '/' . $filename;
        
        if (file_exists($filepath)) {
            return SITE_URL . '/assets/images/gyms/' . $filename;
        }
    }
    
    // Если точного совпадения нет, ищем по частичному совпадению
    foreach ($imageMap as $name => $filename) {
        if (strpos($normalized, $name) !== false || strpos($name, $normalized) !== false) {
            $filepath = GYMS_PATH . '/' . $filename;
            if (file_exists($filepath)) {
                return SITE_URL . '/assets/images/gyms/' . $filename;
            }
        }
    }
    
    // Fallback - цветной placeholder с текстом
    return null;
}

/**
 * Отрендерить изображение зала
 * 
 * @param array $location - данные локации
 * @param string $cssClass - дополнительные CSS классы
 * @param int $height - высота изображения
 */
function renderGymImage($location, $cssClass = '', $height = 250) {
    $imagePath = getGymImagePath($location['name']);
    $altText = clean($location['name']);
    
    if ($imagePath) {
        // Есть картинка
        echo '<img src="' . $imagePath . '" 
                  class="card-img-top ' . $cssClass . '" 
                  alt="' . $altText . '"
                  style="height: ' . $height . 'px; object-fit: cover;"
                  loading="lazy">';
    } else {
        // Placeholder с цветом и текстом
        $colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8'];
        $color = $colors[abs(crc32($location['name'])) % count($colors)];
        
        echo '<div class="card-img-top d-flex align-items-center justify-content-center ' . $cssClass . '"
                  style="height: ' . $height . 'px; 
                         background-color: ' . $color . '; 
                         color: white;
                         text-align: center;">';
        echo '  <div>';
        echo '    <i class="bi bi-image display-1 mb-2" style="display: block; opacity: 0.7;"></i>';
        echo '    <p class="mb-0" style="font-size: 0.9rem; opacity: 0.9;">' . $altText . '</p>';
        echo '  </div>';
        echo '</div>';
    }
}

/**
 * Получить URL картинки с fallback
 */
function getGymImage($locationName, $fallback = null) {
    $imagePath = getGymImagePath($locationName);
    
    if ($imagePath) {
        return $imagePath;
    }
    
    return $fallback ?: SITE_URL . '/assets/images/placeholder.jpg';
}
?>
