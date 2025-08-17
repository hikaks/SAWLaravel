/**
 * Charts JavaScript for SPK SAW Dashboard
 * Chart.js implementation with modern styling
 */

import {
    Chart,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler
} from 'chart.js';

// Register Chart.js components
Chart.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

class ChartManager {
    constructor() {
        this.charts = {};
        this.defaultOptions = this.getDefaultOptions();
        this.colors = this.getColorPalette();
    }

    getDefaultOptions() {
        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            family: 'Inter, sans-serif',
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: true,
                    intersect: false,
                    mode: 'index'
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeInOutQuart'
            }
        };
    }

    getColorPalette() {
        return {
            primary: '#0d6efd',
            success: '#198754',
            warning: '#ffc107',
            danger: '#dc3545',
            info: '#0dcaf0',
            secondary: '#6c757d',
            gradients: {
                primary: 'linear-gradient(135deg, #0d6efd 0%, #0056b3 100%)',
                success: 'linear-gradient(135deg, #198754 0%, #146c43 100%)',
                warning: 'linear-gradient(135deg, #ffc107 0%, #d39e00 100%)',
                danger: 'linear-gradient(135deg, #dc3545 0%, #b02a37 100%)',
                info: 'linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%)'
            }
        };
    }

    createPerformanceChart(canvasId, data = null) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return null;

        const defaultData = data || {
            labels: ['Excellent', 'Good', 'Average', 'Needs Improvement'],
            datasets: [{
                data: [25, 35, 30, 10],
                backgroundColor: [
                    this.colors.success,
                    this.colors.info,
                    this.colors.warning,
                    this.colors.danger
                ],
                borderWidth: 3,
                borderColor: '#ffffff',
                hoverBorderWidth: 4,
                hoverOffset: 10
            }]
        };

        const options = {
            ...this.defaultOptions,
            plugins: {
                ...this.defaultOptions.plugins,
                legend: {
                    ...this.defaultOptions.plugins.legend,
                    position: 'right'
                },
                tooltip: {
                    ...this.defaultOptions.plugins.tooltip,
                    callbacks: {
                        label: (context) => {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} employees (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '60%',
            radius: '80%'
        };

        this.charts[canvasId] = new Chart(ctx, {
            type: 'doughnut',
            data: defaultData,
            options: options
        });

        return this.charts[canvasId];
    }

    createDepartmentChart(canvasId, data = null) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return null;

        const defaultData = data || {
            labels: ['IT', 'HR', 'Finance', 'Marketing', 'Operations', 'Sales'],
            datasets: [{
                label: 'Average Score',
                data: [85, 78, 82, 75, 88, 80],
                backgroundColor: [
                    this.colors.primary,
                    this.colors.success,
                    this.colors.info,
                    this.colors.warning,
                    this.colors.danger,
                    this.colors.secondary
                ],
                borderRadius: 6,
                borderSkipped: false,
                maxBarThickness: 60
            }]
        };

        const options = {
            ...this.defaultOptions,
            plugins: {
                ...this.defaultOptions.plugins,
                legend: {
                    display: false
                },
                tooltip: {
                    ...this.defaultOptions.plugins.tooltip,
                    callbacks: {
                        label: (context) => {
                            return `Average Score: ${context.parsed.y}%`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: {
                        color: 'rgba(0,0,0,0.1)',
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            family: 'Inter, sans-serif'
                        },
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            family: 'Inter, sans-serif'
                        }
                    }
                }
            }
        };

        this.charts[canvasId] = new Chart(ctx, {
            type: 'bar',
            data: defaultData,
            options: options
        });

        return this.charts[canvasId];
    }

    createTrendChart(canvasId, data = null) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return null;

        const defaultData = data || {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Average Performance',
                data: [75, 78, 82, 85, 83, 87, 89, 86, 88, 91, 89, 92],
                borderColor: this.colors.primary,
                backgroundColor: this.colors.primary + '20',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: this.colors.primary,
                pointBorderColor: '#ffffff',
                pointBorderWidth: 3,
                pointRadius: 6,
                pointHoverRadius: 8,
                pointHoverBorderWidth: 4
            }]
        };

        const options = {
            ...this.defaultOptions,
            plugins: {
                ...this.defaultOptions.plugins,
                legend: {
                    display: false
                },
                tooltip: {
                    ...this.defaultOptions.plugins.tooltip,
                    callbacks: {
                        label: (context) => {
                            return `Performance: ${context.parsed.y}%`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: {
                        color: 'rgba(0,0,0,0.1)',
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            family: 'Inter, sans-serif'
                        },
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            family: 'Inter, sans-serif'
                        }
                    }
                }
            },
            elements: {
                point: {
                    hoverBackgroundColor: this.colors.primary
                }
            }
        };

        this.charts[canvasId] = new Chart(ctx, {
            type: 'line',
            data: defaultData,
            options: options
        });

        return this.charts[canvasId];
    }

    createRankingChart(canvasId, data = null) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return null;

        const defaultData = data || {
            labels: ['Employee A', 'Employee B', 'Employee C', 'Employee D', 'Employee E'],
            datasets: [{
                label: 'SAW Score',
                data: [0.92, 0.88, 0.85, 0.82, 0.78],
                backgroundColor: [
                    this.colors.success,
                    this.colors.info,
                    this.colors.primary,
                    this.colors.warning,
                    this.colors.secondary
                ],
                borderRadius: 6,
                borderSkipped: false
            }]
        };

        const options = {
            ...this.defaultOptions,
            indexAxis: 'y',
            plugins: {
                ...this.defaultOptions.plugins,
                legend: {
                    display: false
                },
                tooltip: {
                    ...this.defaultOptions.plugins.tooltip,
                    callbacks: {
                        label: (context) => {
                            return `SAW Score: ${(context.parsed.x * 100).toFixed(1)}%`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    max: 1,
                    grid: {
                        color: 'rgba(0,0,0,0.1)',
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            family: 'Inter, sans-serif'
                        },
                        callback: function(value) {
                            return (value * 100).toFixed(0) + '%';
                        }
                    }
                },
                y: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            family: 'Inter, sans-serif'
                        }
                    }
                }
            }
        };

        this.charts[canvasId] = new Chart(ctx, {
            type: 'bar',
            data: defaultData,
            options: options
        });

        return this.charts[canvasId];
    }

    createCriteriaRadarChart(canvasId, data = null) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return null;

        const defaultData = data || {
            labels: ['Technical Skills', 'Communication', 'Leadership', 'Problem Solving', 'Teamwork', 'Innovation'],
            datasets: [{
                label: 'Average Department Score',
                data: [85, 78, 82, 88, 75, 80],
                backgroundColor: this.colors.primary + '30',
                borderColor: this.colors.primary,
                borderWidth: 3,
                pointBackgroundColor: this.colors.primary,
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 6
            }]
        };

        const options = {
            ...this.defaultOptions,
            plugins: {
                ...this.defaultOptions.plugins,
                legend: {
                    display: false
                }
            },
            scales: {
                r: {
                    beginAtZero: true,
                    max: 100,
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    },
                    pointLabels: {
                        font: {
                            family: 'Inter, sans-serif',
                            size: 12
                        }
                    },
                    ticks: {
                        display: false
                    }
                }
            }
        };

        this.charts[canvasId] = new Chart(ctx, {
            type: 'radar',
            data: defaultData,
            options: options
        });

        return this.charts[canvasId];
    }

    updateChart(canvasId, newData) {
        const chart = this.charts[canvasId];
        if (chart) {
            chart.data = newData;
            chart.update('active');
        }
    }

    destroyChart(canvasId) {
        const chart = this.charts[canvasId];
        if (chart) {
            chart.destroy();
            delete this.charts[canvasId];
        }
    }

    destroyAllCharts() {
        Object.keys(this.charts).forEach(canvasId => {
            this.destroyChart(canvasId);
        });
    }

    getChart(canvasId) {
        return this.charts[canvasId];
    }

    getAllCharts() {
        return this.charts;
    }

    // Utility method to generate gradient backgrounds
    createGradient(ctx, color1, color2) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, color1);
        gradient.addColorStop(1, color2);
        return gradient;
    }

    // Export chart as image
    exportChart(canvasId, filename = 'chart.png') {
        const chart = this.charts[canvasId];
        if (chart) {
            const url = chart.toBase64Image();
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            link.click();
        }
    }

    // Animate chart data update
    animateDataUpdate(canvasId, newData, duration = 1000) {
        const chart = this.charts[canvasId];
        if (chart) {
            chart.data = newData;
            chart.update({
                duration: duration,
                easing: 'easeInOutQuart'
            });
        }
    }
}

// Initialize and export
const chartManager = new ChartManager();

// Auto-initialize charts when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Initialize common dashboard charts
    setTimeout(() => {
        chartManager.createPerformanceChart('performanceChart');
        chartManager.createDepartmentChart('departmentChart');
        chartManager.createTrendChart('trendChart');
        chartManager.createRankingChart('rankingChart');
        chartManager.createCriteriaRadarChart('criteriaRadarChart');
    }, 500); // Small delay to ensure DOM is fully ready
});

// Make Chart and chartManager available globally
window.Chart = Chart;
window.chartManager = chartManager;

export default chartManager;