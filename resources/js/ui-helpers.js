/**
 * UI Helper Functions for SPK SAW
 * Enhanced loading states and user feedback
 */

class UIHelpers {
    constructor() {
        this.loadingButtons = new Set();
        this.init();
    }

    init() {
        this.bindGlobalEvents();
        this.initializeTooltips();
    }

    bindGlobalEvents() {
        // Auto-initialize tooltips
        document.addEventListener('DOMContentLoaded', () => {
            this.initializeTooltips();
        });

        // Handle form submissions with loading states
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form.tagName === 'FORM') {
                this.handleFormSubmission(form);
            }
        });

        // Handle AJAX button clicks
        document.addEventListener('click', (e) => {
            const button = e.target.closest('button[data-loading]');
            if (button) {
                this.setButtonLoading(button, true);
            }
        });
    }

    initializeTooltips() {
        // Initialize Bootstrap tooltips if available
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    }

    setButtonLoading(button, loading = true, loadingText = null) {
        if (!button) return;

        const buttonId = button.id || 'btn_' + Math.random().toString(36).substr(2, 9);
        button.id = buttonId;

        if (loading) {
            this.loadingButtons.add(buttonId);
            
            // Store original content
            if (!button.dataset.originalContent) {
                button.dataset.originalContent = button.innerHTML;
            }
            
            // Set loading state
            button.disabled = true;
            button.classList.add('opacity-75', 'cursor-not-allowed');
            
            // Update content with spinner
            const text = loadingText || button.dataset.loadingText || 'Loading...';
            button.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                ${text}
            `;
        } else {
            this.loadingButtons.delete(buttonId);
            
            // Restore original state
            button.disabled = false;
            button.classList.remove('opacity-75', 'cursor-not-allowed');
            
            if (button.dataset.originalContent) {
                button.innerHTML = button.dataset.originalContent;
            }
        }
    }

    handleFormSubmission(form) {
        const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
        if (submitButton) {
            this.setButtonLoading(submitButton, true, submitButton.dataset.loadingText || 'Processing...');
            
            // Reset loading state after a timeout (fallback)
            setTimeout(() => {
                this.setButtonLoading(submitButton, false);
            }, 10000); // 10 seconds fallback
        }
    }

    showNotification(message, type = 'info', duration = 5000) {
        const notification = this.createNotification(message, type);
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.add('translate-x-0');
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Auto dismiss
        setTimeout(() => {
            this.dismissNotification(notification);
        }, duration);
    }

    createNotification(message, type) {
        const typeConfig = {
            success: {
                bg: 'bg-success-50 border-success-200',
                text: 'text-success-800',
                icon: 'fas fa-check-circle text-success-400'
            },
            error: {
                bg: 'bg-danger-50 border-danger-200',
                text: 'text-danger-800',
                icon: 'fas fa-exclamation-circle text-danger-400'
            },
            warning: {
                bg: 'bg-warning-50 border-warning-200',
                text: 'text-warning-800',
                icon: 'fas fa-exclamation-triangle text-warning-400'
            },
            info: {
                bg: 'bg-blue-50 border-blue-200',
                text: 'text-blue-800',
                icon: 'fas fa-info-circle text-blue-400'
            }
        };

        const config = typeConfig[type] || typeConfig.info;
        
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 max-w-sm w-full ${config.bg} border rounded-lg p-4 shadow-lg transform translate-x-full transition-transform duration-300 ease-in-out`;
        
        notification.innerHTML = `
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="${config.icon} text-xl"></i>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium ${config.text}">${message}</p>
                </div>
                <div class="ml-4 flex-shrink-0">
                    <button type="button" class="inline-flex rounded-md p-1.5 hover:bg-opacity-20 focus:outline-none transition-colors duration-200" onclick="this.closest('.fixed').remove()">
                        <i class="fas fa-times text-sm opacity-60 hover:opacity-80"></i>
                    </button>
                </div>
            </div>
        `;
        
        return notification;
    }

    dismissNotification(notification) {
        notification.classList.add('translate-x-full');
        notification.classList.remove('translate-x-0');
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }

    confirmAction(message, title = 'Confirm Action', type = 'warning') {
        return new Promise((resolve) => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: title,
                    text: message,
                    icon: type,
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, continue',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'rounded-lg',
                        confirmButton: 'rounded-lg px-4 py-2',
                        cancelButton: 'rounded-lg px-4 py-2'
                    }
                }).then((result) => {
                    resolve(result.isConfirmed);
                });
            } else {
                resolve(confirm(message));
            }
        });
    }

    showLoadingOverlay(text = 'Loading...') {
        const overlay = document.createElement('div');
        overlay.id = 'global-loading-overlay';
        overlay.className = 'fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50';
        
        overlay.innerHTML = `
            <div class="bg-white rounded-lg p-6 flex flex-col items-center space-y-4 shadow-xl">
                <svg class="animate-spin h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-gray-700 text-sm font-medium">${text}</p>
            </div>
        `;
        
        document.body.appendChild(overlay);
    }

    hideLoadingOverlay() {
        const overlay = document.getElementById('global-loading-overlay');
        if (overlay) {
            overlay.remove();
        }
    }

    // Enhanced AJAX helper with loading states
    async makeRequest(url, options = {}) {
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            ...options
        };

        // Show loading if specified
        if (options.showLoading) {
            this.showLoadingOverlay(options.loadingText);
        }

        // Set button loading if specified
        if (options.button) {
            this.setButtonLoading(options.button, true, options.buttonLoadingText);
        }

        try {
            const response = await fetch(url, defaultOptions);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || `HTTP error! status: ${response.status}`);
            }

            // Show success notification if specified
            if (options.successMessage) {
                this.showNotification(options.successMessage, 'success');
            }

            return data;
        } catch (error) {
            // Show error notification
            const errorMessage = error.message || 'An unexpected error occurred';
            this.showNotification(errorMessage, 'error');
            throw error;
        } finally {
            // Hide loading states
            if (options.showLoading) {
                this.hideLoadingOverlay();
            }
            if (options.button) {
                this.setButtonLoading(options.button, false);
            }
        }
    }
}

// Initialize UI helpers
const uiHelpers = new UIHelpers();

// Make it globally available
window.uiHelpers = uiHelpers;

// Export for module usage
export default UIHelpers;