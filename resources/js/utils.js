/**
 * JavaScript Utilities
 * Common utility functions for the application
 */

/**
 * HTML Escape utility to prevent XSS
 */
function escapeHtml(text) {
    if (text === null || text === undefined) {
        return '';
    }
    
    const div = document.createElement('div');
    div.textContent = String(text);
    return div.innerHTML;
}

/**
 * Safe HTML template function
 */
function safeHtml(strings, ...values) {
    let result = strings[0];
    for (let i = 0; i < values.length; i++) {
        result += escapeHtml(values[i]) + strings[i + 1];
    }
    return result;
}

/**
 * Show loading toast
 */
function showLoadingToast(message = 'Loading...') {
    return Swal.fire({
        title: message,
        html: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        customClass: {
            popup: 'animated fadeIn'
        }
    });
}

/**
 * Show success toast
 */
function showSuccessToast(message, timer = 3000) {
    return Swal.fire({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: timer,
        timerProgressBar: true,
        icon: 'success',
        title: message,
        customClass: {
            popup: 'animated slideInRight'
        }
    });
}

/**
 * Show error toast
 */
function showErrorToast(message, timer = 4000) {
    return Swal.fire({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: timer,
        timerProgressBar: true,
        icon: 'error',
        title: message,
        customClass: {
            popup: 'animated slideInRight'
        }
    });
}

/**
 * Show warning toast
 */
function showWarningToast(message, timer = 4000) {
    return Swal.fire({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: timer,
        timerProgressBar: true,
        icon: 'warning',
        title: message,
        customClass: {
            popup: 'animated slideInRight'
        }
    });
}

/**
 * Standardized AJAX error handler
 */
function handleAjaxError(xhr, defaultMessage = 'An error occurred') {
    let message = defaultMessage;
    
    if (xhr.responseJSON && xhr.responseJSON.message) {
        message = xhr.responseJSON.message;
    } else if (xhr.responseText) {
        try {
            const response = JSON.parse(xhr.responseText);
            message = response.message || message;
        } catch (e) {
            message = xhr.responseText || message;
        }
    }
    
    showErrorToast(message);
    console.error('AJAX Error:', xhr);
}

/**
 * Standardized AJAX wrapper
 */
function makeAjaxRequest(options) {
    const defaults = {
        type: 'GET',
        dataType: 'json',
        headers: window.routeHelper.getAjaxHeaders(),
        beforeSend: function() {
            if (options.showLoading !== false) {
                showLoadingToast(options.loadingMessage || 'Processing...');
            }
        },
        complete: function() {
            if (options.showLoading !== false) {
                Swal.close();
            }
        },
        error: function(xhr) {
            handleAjaxError(xhr, options.errorMessage);
        }
    };

    return $.ajax($.extend({}, defaults, options));
}

/**
 * Initialize tooltips
 */
function initializeTooltips() {
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    } else {
        // Fallback for older Bootstrap
        $('[data-bs-toggle="tooltip"]').tooltip('dispose').tooltip();
    }
}

/**
 * Debounce function
 */
function debounce(func, wait, immediate) {
    let timeout;
    return function executedFunction() {
        const context = this;
        const args = arguments;
        const later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

/**
 * Format number with thousand separators
 */
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

/**
 * Validate email format
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Generate random ID
 */
function generateId(prefix = 'id') {
    return prefix + '_' + Math.random().toString(36).substr(2, 9);
}

// Global utilities
window.utils = {
    escapeHtml,
    safeHtml,
    showLoadingToast,
    showSuccessToast,
    showErrorToast,
    showWarningToast,
    handleAjaxError,
    makeAjaxRequest,
    initializeTooltips,
    debounce,
    formatNumber,
    isValidEmail,
    generateId
};