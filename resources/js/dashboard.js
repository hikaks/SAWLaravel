/**
 * Dashboard JavaScript
 * Enhanced interactivity for SPK SAW Dashboard
 */

class Dashboard {
    constructor() {
        this.initialized = false;
        this.refreshInterval = null;
        this.charts = {};
        this.init();
    }

    init() {
        if (this.initialized) return;
        
        this.bindEvents();
        this.initializeCharts();
        this.startAutoRefresh();
        this.initializeTooltips();
        this.initializeAnimations();
        
        this.initialized = true;
        console.log('Dashboard initialized successfully');
    }

    bindEvents() {
        // Refresh button
        $(document).on('click', '.refresh-dashboard', (e) => {
            e.preventDefault();
            this.refreshDashboard();
        });

        // Chart type toggle
        $(document).on('change', '.chart-type-selector', (e) => {
            const chartType = $(e.target).val();
            const chartId = $(e.target).data('chart-id');
            this.updateChartType(chartId, chartType);
        });

        // Period selector
        $(document).on('change', '.period-selector', (e) => {
            const period = $(e.target).val();
            this.updateDashboardPeriod(period);
        });

        // Stats card click
        $(document).on('click', '.stats-card', (e) => {
            const card = $(e.currentTarget);
            this.animateStatsCard(card);
        });

        // Export functionality
        $(document).on('click', '.export-dashboard', (e) => {
            e.preventDefault();
            const format = $(e.target).data('format');
            this.exportDashboard(format);
        });
    }

    refreshDashboard() {
        const refreshBtn = $('.refresh-dashboard');
        const originalIcon = refreshBtn.find('i').attr('class');
        
        refreshBtn.find('i').attr('class', 'fas fa-spinner fa-spin');
        refreshBtn.prop('disabled', true);

        // Simulate API call
        $.ajax({
            url: '/dashboard/stats',
            method: 'GET',
            success: (data) => {
                this.updateStatsCards(data.stats);
                this.updateCharts(data.charts);
                this.showNotification('Dashboard refreshed successfully!', 'success');
            },
            error: () => {
                this.showNotification('Failed to refresh dashboard', 'error');
            },
            complete: () => {
                refreshBtn.find('i').attr('class', originalIcon);
                refreshBtn.prop('disabled', false);
            }
        });
    }

    updateStatsCards(stats) {
        Object.keys(stats).forEach(key => {
            const card = $(`.stats-card[data-stat="${key}"]`);
            if (card.length) {
                const numberElement = card.find('.stats-number');
                const currentValue = parseInt(numberElement.text()) || 0;
                const newValue = stats[key];
                
                this.animateNumber(numberElement, currentValue, newValue);
            }
        });
    }

    animateNumber(element, from, to, duration = 1000) {
        const startTime = performance.now();
        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const currentValue = Math.floor(from + (to - from) * this.easeOutCubic(progress));
            element.text(currentValue);
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };
        requestAnimationFrame(animate);
    }

    easeOutCubic(t) {
        return 1 - Math.pow(1 - t, 3);
    }

    animateStatsCard(card) {
        card.addClass('stats-card-pulse');
        setTimeout(() => {
            card.removeClass('stats-card-pulse');
        }, 600);
    }

    initializeCharts() {
        // Initialize Chart.js charts if available
        if (typeof Chart !== 'undefined') {
            this.initPerformanceChart();
            this.initDepartmentChart();
            this.initTrendChart();
        }
    }

    initPerformanceChart() {
        const ctx = document.getElementById('performanceChart');
        if (!ctx) return;

        this.charts.performance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Excellent', 'Good', 'Average', 'Needs Improvement'],
                datasets: [{
                    data: [25, 35, 30, 10],
                    backgroundColor: [
                        '#28a745',
                        '#17a2b8',
                        '#ffc107',
                        '#dc3545'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    duration: 2000
                }
            }
        });
    }

    initDepartmentChart() {
        const ctx = document.getElementById('departmentChart');
        if (!ctx) return;

        this.charts.department = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['IT', 'HR', 'Finance', 'Marketing', 'Operations'],
                datasets: [{
                    label: 'Average Score',
                    data: [85, 78, 82, 75, 88],
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }

    initTrendChart() {
        const ctx = document.getElementById('trendChart');
        if (!ctx) return;

        this.charts.trend = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Average Performance',
                    data: [75, 78, 82, 85, 83, 87],
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: 'rgba(255, 99, 132, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }

    updateChartType(chartId, type) {
        const chart = this.charts[chartId];
        if (chart) {
            chart.config.type = type;
            chart.update('active');
        }
    }

    updateDashboardPeriod(period) {
        // Show loading state
        this.showLoading();

        // Simulate API call to update data for specific period
        $.ajax({
            url: `/dashboard/period/${period}`,
            method: 'GET',
            success: (data) => {
                this.updateStatsCards(data.stats);
                this.updateCharts(data.charts);
                this.hideLoading();
                this.showNotification(`Dashboard updated for period: ${period}`, 'info');
            },
            error: () => {
                this.hideLoading();
                this.showNotification('Failed to update dashboard period', 'error');
            }
        });
    }

    updateCharts(chartData) {
        Object.keys(chartData).forEach(chartId => {
            const chart = this.charts[chartId];
            if (chart && chartData[chartId]) {
                chart.data = chartData[chartId];
                chart.update('active');
            }
        });
    }

    startAutoRefresh() {
        // Auto-refresh every 5 minutes
        this.refreshInterval = setInterval(() => {
            this.refreshDashboard();
        }, 300000);
    }

    stopAutoRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
    }

    initializeTooltips() {
        // Initialize Bootstrap tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
        
        // Custom tooltips for stats cards
        $('.stats-card').hover(
            function() {
                $(this).addClass('stats-card-hover');
            },
            function() {
                $(this).removeClass('stats-card-hover');
            }
        );
    }

    initializeAnimations() {
        // Animate stats cards on page load
        $('.stats-card').each((index, element) => {
            setTimeout(() => {
                $(element).addClass('stats-card-animate');
            }, index * 200);
        });

        // Animate charts on scroll
        this.initScrollAnimations();
    }

    initScrollAnimations() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    $(entry.target).addClass('animate-in');
                }
            });
        }, {
            threshold: 0.1
        });

        $('.chart-container, .stats-section').each(function() {
            observer.observe(this);
        });
    }

    showLoading() {
        if ($('#dashboard-loading').length === 0) {
            $('body').append(`
                <div id="dashboard-loading" class="dashboard-loading">
                    <div class="loading-spinner">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p>Updating dashboard...</p>
                    </div>
                </div>
            `);
        }
        $('#dashboard-loading').fadeIn();
    }

    hideLoading() {
        $('#dashboard-loading').fadeOut(() => {
            $('#dashboard-loading').remove();
        });
    }

    exportDashboard(format) {
        const exportBtn = $(`.export-dashboard[data-format="${format}"]`);
        const originalText = exportBtn.html();
        
        exportBtn.html('<i class="fas fa-spinner fa-spin me-1"></i>Exporting...').prop('disabled', true);

        // Simulate export process
        setTimeout(() => {
            if (format === 'pdf') {
                this.exportToPDF();
            } else if (format === 'excel') {
                this.exportToExcel();
            } else if (format === 'image') {
                this.exportToImage();
            }
            
            exportBtn.html(originalText).prop('disabled', false);
            this.showNotification(`Dashboard exported as ${format.toUpperCase()}`, 'success');
        }, 2000);
    }

    exportToPDF() {
        // Implement PDF export using jsPDF or similar
        console.log('Exporting dashboard to PDF...');
    }

    exportToExcel() {
        // Implement Excel export
        console.log('Exporting dashboard to Excel...');
    }

    exportToImage() {
        // Implement image export using html2canvas
        console.log('Exporting dashboard to image...');
    }

    showNotification(message, type = 'info') {
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';

        const iconClass = {
            'success': 'fa-check-circle',
            'error': 'fa-exclamation-circle',
            'warning': 'fa-exclamation-triangle',
            'info': 'fa-info-circle'
        }[type] || 'fa-info-circle';

        const notification = $(`
            <div class="alert ${alertClass} alert-dismissible fade show dashboard-notification" role="alert">
                <i class="fas ${iconClass} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);

        $('#notification-container').append(notification);

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            notification.alert('close');
        }, 5000);
    }

    destroy() {
        this.stopAutoRefresh();
        
        // Destroy charts
        Object.values(this.charts).forEach(chart => {
            if (chart && typeof chart.destroy === 'function') {
                chart.destroy();
            }
        });
        
        // Remove event listeners
        $(document).off('.dashboard');
        
        this.initialized = false;
    }
}

// Initialize dashboard when DOM is ready
$(document).ready(() => {
    // Create notification container if it doesn't exist
    if ($('#notification-container').length === 0) {
        $('body').prepend('<div id="notification-container" class="notification-container"></div>');
    }
    
    // Initialize dashboard
    window.dashboard = new Dashboard();
});

// Export for global use
window.Dashboard = Dashboard;