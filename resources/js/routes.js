/**
 * JavaScript Route Helper
 * Provides Laravel route generation for JavaScript
 */

class RouteHelper {
    constructor() {
        this.routes = {};
        this.baseUrl = document.querySelector('meta[name="app-url"]')?.getAttribute('content') || window.location.origin;
    }

    /**
     * Set routes from backend
     */
    setRoutes(routes) {
        this.routes = routes;
    }

    /**
     * Generate route URL with parameters
     */
    route(name, parameters = {}) {
        if (!this.routes[name]) {
            console.error(`Route '${name}' not found`);
            return '#';
        }

        let url = this.routes[name];
        
        // Replace parameters in URL
        Object.keys(parameters).forEach(key => {
            const placeholder = `:${key}`;
            const encodedValue = encodeURIComponent(parameters[key]);
            url = url.replace(placeholder, encodedValue);
        });

        // Remove any remaining placeholders
        url = url.replace(/:[\w]+/g, '');
        
        return url;
    }

    /**
     * Get CSRF token
     */
    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    /**
     * Generate AJAX headers with CSRF
     */
    getAjaxHeaders() {
        return {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': this.getCsrfToken(),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };
    }
}

// Global instance
window.routeHelper = new RouteHelper();

// Backward compatibility
window.route = (name, parameters) => window.routeHelper.route(name, parameters);