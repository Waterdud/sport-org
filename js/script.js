/**
 * Основные JavaScript функции для приложения "Спортивные Игры"
 */

// ==================== Инициализация при загрузке страницы ====================
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация всех компонентов
    initScrollToTop();
    initAlertAutoClose();
    initConfirmDialogs();
    initFormValidation();
    initTooltips();
    initDateTimeValidation();
});

// ==================== Кнопка "Наверх" ====================
function initScrollToTop() {
    const scrollTopBtn = document.getElementById('scrollTopBtn');
    
    if (!scrollTopBtn) return;
    
    // Показываем/скрываем кнопку при прокрутке
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            scrollTopBtn.style.display = 'block';
            scrollTopBtn.classList.add('fade-in');
        } else {
            scrollTopBtn.style.display = 'none';
        }
    });
    
    // Плавная прокрутка наверх при клике
    scrollTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// ==================== Автоматическое закрытие алертов ====================
function initAlertAutoClose() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    
    alerts.forEach(alert => {
        // Автоматически закрываем через 5 секунд
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
}

// ==================== Подтверждение действий ====================
function initConfirmDialogs() {
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    
    confirmButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });
}

// ==================== Валидация форм Bootstrap ====================
function initFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
}

// ==================== Инициализация подсказок (Tooltips) ====================
function initTooltips() {
    const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// ==================== Валидация даты и времени события ====================
function initDateTimeValidation() {
    const dateInput = document.getElementById('event_date');
    const timeInput = document.getElementById('event_time');
    
    if (!dateInput || !timeInput) return;
    
    // Устанавливаем минимальную дату (сегодня)
    const today = new Date().toISOString().split('T')[0];
    dateInput.setAttribute('min', today);
    
    // Проверка при отправке формы
    const form = dateInput.closest('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const selectedDate = new Date(dateInput.value);
            const selectedTime = timeInput.value;
            const now = new Date();
            
            // Собираем полную дату и время
            const [hours, minutes] = selectedTime.split(':');
            selectedDate.setHours(hours, minutes);
            
            if (selectedDate <= now) {
                e.preventDefault();
                alert('Дата и время события должны быть в будущем');
                return false;
            }
        });
    }
}

// ==================== AJAX запись на событие ====================
function joinEvent(eventId, button) {
    if (!confirm('Вы уверены, что хотите записаться на это событие?')) {
        return;
    }
    
    // Отключаем кнопку во время запроса
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Загрузка...';
    
    fetch('ajax/join_event.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ event_id: eventId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем кнопку
            button.classList.remove('btn-primary');
            button.classList.add('btn-danger');
            button.innerHTML = '<i class="bi bi-x-circle me-2"></i>Отменить запись';
            button.onclick = function() { leaveEvent(eventId, this); };
            
            // Обновляем счётчик участников
            updateParticipantsCount(eventId, data.current_participants);
            
            // Показываем уведомление
            showNotification('Вы успешно записались на событие!', 'success');
        } else {
            showNotification(data.message || 'Ошибка при записи на событие', 'danger');
            button.disabled = false;
            button.innerHTML = '<i class="bi bi-check-circle me-2"></i>Записаться';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Произошла ошибка. Попробуйте позже.', 'danger');
        button.disabled = false;
        button.innerHTML = '<i class="bi bi-check-circle me-2"></i>Записаться';
    });
}

// ==================== AJAX отмена записи на событие ====================
function leaveEvent(eventId, button) {
    if (!confirm('Вы уверены, что хотите отменить запись?')) {
        return;
    }
    
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Загрузка...';
    
    fetch('ajax/leave_event.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ event_id: eventId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.classList.remove('btn-danger');
            button.classList.add('btn-primary');
            button.innerHTML = '<i class="bi bi-check-circle me-2"></i>Записаться';
            button.onclick = function() { joinEvent(eventId, this); };
            
            updateParticipantsCount(eventId, data.current_participants);
            showNotification('Запись отменена', 'info');
        } else {
            showNotification(data.message || 'Ошибка при отмене записи', 'danger');
            button.disabled = false;
            button.innerHTML = '<i class="bi bi-x-circle me-2"></i>Отменить запись';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Произошла ошибка. Попробуйте позже.', 'danger');
        button.disabled = false;
        button.innerHTML = '<i class="bi bi-x-circle me-2"></i>Отменить запись';
    });
}

// ==================== Обновление счётчика участников ====================
function updateParticipantsCount(eventId, currentCount) {
    const countElement = document.querySelector(`[data-event-id="${eventId}"] .participants-count`);
    if (countElement) {
        countElement.textContent = currentCount;
    }
    
    // Обновляем прогресс-бар, если есть
    const progressBar = document.querySelector(`[data-event-id="${eventId}"] .progress-bar`);
    if (progressBar) {
        const maxParticipants = parseInt(progressBar.getAttribute('aria-valuemax'));
        const percentage = (currentCount / maxParticipants) * 100;
        progressBar.style.width = percentage + '%';
        progressBar.setAttribute('aria-valuenow', currentCount);
    }
}

// ==================== Показ уведомлений ====================
function showNotification(message, type = 'info') {
    const alertTypes = {
        success: 'alert-success',
        danger: 'alert-danger',
        warning: 'alert-warning',
        info: 'alert-info'
    };
    
    const alertClass = alertTypes[type] || 'alert-info';
    
    // Создаём элемент уведомления
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert ${alertClass} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Автоматически удаляем через 4 секунды
    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alertDiv);
        bsAlert.close();
    }, 4000);
}

// ==================== Фильтрация событий ====================
function filterEvents() {
    const sportType = document.getElementById('filter_sport')?.value || '';
    const city = document.getElementById('filter_city')?.value || '';
    const date = document.getElementById('filter_date')?.value || '';
    const skillLevel = document.getElementById('filter_skill')?.value || '';
    
    const params = new URLSearchParams();
    if (sportType) params.append('sport', sportType);
    if (city) params.append('city', city);
    if (date) params.append('date', date);
    if (skillLevel) params.append('skill', skillLevel);
    
    window.location.href = 'index.php?' + params.toString();
}

// ==================== Предпросмотр изображения перед загрузкой ====================
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

// ==================== Добавление комментария через AJAX ====================
function addComment(eventId) {
    const commentText = document.getElementById('comment_text')?.value;
    
    if (!commentText || commentText.trim() === '') {
        showNotification('Введите текст комментария', 'warning');
        return;
    }
    
    fetch('ajax/add_comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            event_id: eventId,
            comment: commentText
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Очищаем поле ввода
            document.getElementById('comment_text').value = '';
            
            // Добавляем комментарий в список
            const commentsList = document.getElementById('comments_list');
            const commentHtml = `
                <div class="comment-item fade-in">
                    <div class="d-flex justify-content-between">
                        <span class="comment-author">${data.comment.username}</span>
                        <span class="comment-time">только что</span>
                    </div>
                    <div class="comment-text">${data.comment.text}</div>
                </div>
            `;
            commentsList.insertAdjacentHTML('afterbegin', commentHtml);
            
            showNotification('Комментарий добавлен', 'success');
        } else {
            showNotification(data.message || 'Ошибка при добавлении комментария', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Произошла ошибка. Попробуйте позже.', 'danger');
    });
}

// ==================== Отметка уведомления как прочитанного ====================
function markNotificationRead(notificationId) {
    fetch('ajax/mark_notification_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ notification_id: notificationId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notificationElement) {
                notificationElement.classList.remove('unread');
            }
            
            // Обновляем счётчик непрочитанных
            updateNotificationBadge();
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// ==================== Обновление счётчика уведомлений ====================
function updateNotificationBadge() {
    fetch('ajax/get_unread_count.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('.notification-badge');
            if (badge) {
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'inline';
                } else {
                    badge.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// ==================== Форматирование номера телефона ====================
function formatPhone(input) {
    let value = input.value.replace(/\D/g, '');
    
    if (value.length > 0) {
        if (value[0] === '8' || value[0] === '7') {
            value = '7' + value.slice(1);
        }
        
        let formatted = '+7';
        if (value.length > 1) {
            formatted += ' (' + value.slice(1, 4);
        }
        if (value.length >= 5) {
            formatted += ') ' + value.slice(4, 7);
        }
        if (value.length >= 8) {
            formatted += '-' + value.slice(7, 9);
        }
        if (value.length >= 10) {
            formatted += '-' + value.slice(9, 11);
        }
        
        input.value = formatted;
    }
}

// ==================== Копирование ссылки в буфер обмена ====================
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Ссылка скопирована в буфер обмена', 'success');
    }).catch(err => {
        console.error('Error copying text: ', err);
        showNotification('Не удалось скопировать ссылку', 'danger');
    });
}

// ==================== Дебаунс для поиска ====================
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ==================== Живой поиск ====================
const liveSearch = debounce(function(query) {
    if (query.length < 2) return;
    
    fetch(`ajax/search.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            displaySearchResults(data);
        })
        .catch(error => {
            console.error('Search error:', error);
        });
}, 300);

// ==================== Отображение результатов поиска ====================
function displaySearchResults(results) {
    const resultsContainer = document.getElementById('search_results');
    if (!resultsContainer) return;
    
    if (results.length === 0) {
        resultsContainer.innerHTML = '<p class="text-muted">Ничего не найдено</p>';
        return;
    }
    
    let html = '<div class="list-group">';
    results.forEach(result => {
        html += `
            <a href="event.php?id=${result.id}" class="list-group-item list-group-item-action">
                <div class="d-flex justify-content-between">
                    <h6 class="mb-1">${result.title}</h6>
                    <small>${result.date}</small>
                </div>
                <small class="text-muted">${result.sport_type} • ${result.location}</small>
            </a>
        `;
    });
    html += '</div>';
    
    resultsContainer.innerHTML = html;
}
