/**
 * Dashboard Chart Initialization (Chart.js)
 * Decoupled script reading data attributes from canvas elements.
 */
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js library is not loaded');
        return;
    }

    // 1. Trend Line Chart (Pemasukan vs Pengeluaran)
    const trendCanvas = document.getElementById('trendChart');
    if (trendCanvas) {
        try {
            const labels = JSON.parse(trendCanvas.dataset.labels || '[]');
            const incomeData = JSON.parse(trendCanvas.dataset.income || '[]');
            const expenseData = JSON.parse(trendCanvas.dataset.expense || '[]');

            new Chart(trendCanvas, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Pemasukan (Income)',
                            data: incomeData,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                        },
                        {
                            label: 'Pengeluaran (Expense)',
                            data: expenseData,
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    if (value >= 1000000) {
                                        return 'Rp ' + (value / 1000000) + 'jt';
                                    } else if (value >= 1000) {
                                        return 'Rp ' + (value / 1000) + 'rb';
                                    }
                                    return 'Rp ' + value;
                                }
                            }
                        }
                    }
                }
            });
        } catch (e) {
            console.error('Error rendering trend chart:', e);
        }
    }

    // Helper Doughnut Chart
    function createDoughnutChart(canvasId, palette) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;

        try {
            const labels = JSON.parse(canvas.dataset.labels || '[]');
            const values = JSON.parse(canvas.dataset.values || '[]');

            if (labels.length === 0) return;

            new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: palette,
                        borderWidth: 2,
                        borderColor: '#ffffff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 12,
                                font: { size: 11 }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const val = context.parsed;
                                    return ' ' + context.label + ': ' + new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
                                }
                            }
                        }
                    }
                }
            });
        } catch (e) {
            console.error('Error rendering doughnut chart:', e);
        }
    }

    // 2. Category Doughnut Charts
    createDoughnutChart('incomeCategoryChart', ['#10b981', '#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#06b6d4']);
    createDoughnutChart('expenseCategoryChart', ['#ef4444', '#f97316', '#eab308', '#6366f1', '#14b8a6', '#64748b']);
});
