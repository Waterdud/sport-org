<?php
/**
 * Footer компонент - подвал сайта с контактами
 */
?>
        </div> <!-- Close container -->
    </main> <!-- Close main content -->
    
    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 pt-4 pb-2">
        <div class="container">
            <div class="row mb-4">
                <!-- Kontaktid (Contacts) -->
                <div class="col-md-4 mb-3 mb-md-0">
                    <h5 class="mb-3">
                        <i class="bi bi-telephone me-2"></i>Kontaktid
                    </h5>
                    <ul class="list-unstyled text-white-50">
                        <li class="mb-2">
                            <i class="bi bi-telephone me-2"></i>
                            <a href="tel:+3725551234" class="text-white-50 text-decoration-none">
                                +372 555 1234
                            </a>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-envelope me-2"></i>
                            <a href="mailto:info@sportorg.ee" class="text-white-50 text-decoration-none">
                                info@sportorg.ee
                            </a>
                        </li>
                        <li>
                            <i class="bi bi-geo-alt me-2"></i>
                            Tallinn, Eesti
                        </li>
                    </ul>
                </div>
                
                <!-- Navigatsioon (Navigation) -->
                <div class="col-md-4 mb-3 mb-md-0">
                    <h5 class="mb-3">
                        <i class="bi bi-map me-2"></i>Navigatsioon
                    </h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="<?php echo SITE_URL; ?>/" class="text-white-50 text-decoration-none">
                                <i class="bi bi-chevron-right"></i> Avaleht
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo SITE_URL; ?>/events/list.php" class="text-white-50 text-decoration-none">
                                <i class="bi bi-chevron-right"></i> Treeningud
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo SITE_URL; ?>/locations/list.php" class="text-white-50 text-decoration-none">
                                <i class="bi bi-chevron-right"></i> Kohad
                            </a>
                        </li>
                        <?php if (isLoggedIn()): ?>
                        <li class="mb-2">
                            <a href="<?php echo SITE_URL; ?>/user/profile.php" class="text-white-50 text-decoration-none">
                                <i class="bi bi-chevron-right"></i> Profiil
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <!-- Infot (About) -->
                <div class="col-md-4">
                    <h5 class="mb-3">
                        <i class="bi bi-trophy-fill me-2"></i>SportOrg
                    </h5>
                    <p class="text-white-50 small">
                        SportOrg aitab sul leida treening- ja mängiupartnerid lähimal. 
                        Registreeru, loo üritus ja asu treenima!
                    </p>
                </div>
            </div>
            
            <!-- Copyright -->
            <div class="border-top border-secondary pt-3 mt-3">
                <div class="text-center text-white-50 small">
                    <p class="mb-0">
                        © 2026 SportOrg. Kõik õigused kaitstud.
                    </p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo SITE_URL; ?>/public/assets/js/script.js"></script>
    
    <script>
        // Load notification count
        document.addEventListener('DOMContentLoaded', function() {
            const notificationCountEl = document.getElementById('notificationCount');
            if (notificationCountEl) {
                fetch('<?php echo SITE_URL; ?>/ajax/get_unread_count.php')
                    .then(r => r.json())
                    .then(d => {
                        if (d.count > 0) {
                            notificationCountEl.textContent = d.count;
                        } else {
                            notificationCountEl.style.display = 'none';
                        }
                    });
            }
        });
    </script>
</body>
</html>
