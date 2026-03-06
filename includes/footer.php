        </div> <!-- Закрываем container -->
    </main> <!-- Закрываем main-content -->
    
    <!-- Подвал сайта -->
    <footer class="bg-dark text-white mt-5">
        <div class="container py-4">
            <div class="row">
                <!-- О проекте -->
                <div class="col-md-4 mb-3 mb-md-0">
                    <h5 class="mb-3">
                        <i class="bi bi-trophy-fill me-2"></i>
                        Спортивные Игры
                    </h5>
                    <p class="text-white-50">
                        Платформа для организации спортивных мероприятий. 
                        Находите игроков, создавайте команды и играйте вместе!
                    </p>
                </div>
                
                <!-- Быстрые ссылки -->
                <div class="col-md-4 mb-3 mb-md-0">
                    <h5 class="mb-3">Быстрые ссылки</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="index.php" class="text-white-50 text-decoration-none">
                                <i class="bi bi-chevron-right"></i> Все события
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="locations.php" class="text-white-50 text-decoration-none">
                                <i class="bi bi-chevron-right"></i> Места для игр
                            </a>
                        </li>
                        <?php if (isLoggedIn()): ?>
                        <li class="mb-2">
                            <a href="create_event.php" class="text-white-50 text-decoration-none">
                                <i class="bi bi-chevron-right"></i> Создать событие
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="my_events.php" class="text-white-50 text-decoration-none">
                                <i class="bi bi-chevron-right"></i> Мои события
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <!-- Контакты и социальные сети -->
                <div class="col-md-4">
                    <h5 class="mb-3">Контакты</h5>
                    <ul class="list-unstyled text-white-50">
                        <li class="mb-2">
                            <i class="bi bi-envelope me-2"></i>
                            info@sportgames.ru
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-telephone me-2"></i>
                            +7 (999) 123-45-67
                        </li>
                    </ul>
                    
                    <!-- Социальные сети -->
                    <div class="mt-3">
                        <a href="#" class="text-white me-3 fs-4" title="VK">
                            <i class="bi bi-vk"></i>
                        </a>
                        <a href="#" class="text-white me-3 fs-4" title="Telegram">
                            <i class="bi bi-telegram"></i>
                        </a>
                        <a href="#" class="text-white me-3 fs-4" title="Instagram">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="#" class="text-white fs-4" title="YouTube">
                            <i class="bi bi-youtube"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Копирайт -->
            <hr class="border-secondary my-4">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="text-white-50 mb-0">
                        &copy; <?php echo date('Y'); ?> Спортивные Игры. Все права защищены.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="text-white-50 text-decoration-none me-3">Политика конфиденциальности</a>
                    <a href="#" class="text-white-50 text-decoration-none">Пользовательское соглашение</a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Кнопка "Наверх" -->
    <button id="scrollTopBtn" class="btn btn-primary rounded-circle shadow" 
            style="display: none; position: fixed; bottom: 20px; right: 20px; z-index: 1000; width: 50px; height: 50px;">
        <i class="bi bi-arrow-up-short fs-4"></i>
    </button>
    
    <!-- Bootstrap 5 JS Bundle (включает Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Собственные скрипты -->
    <script src="js/script.js"></script>
</body>
</html>
