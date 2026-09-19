const savedTheme = localStorage.getItem('hr-theme');
if (savedTheme === 'dark') {
    document.documentElement.classList.add('dark');
} else {
    document.documentElement.classList.remove('dark');
}

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

window.renderDashboardCharts = ({ attendanceStatus, departments, trend }) => {
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const headingColor = isDark ? '#f8fafc' : '#0f172a';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.06)' : 'rgba(226, 232, 240, 0.7)';

    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    color: textColor,
                    usePointStyle: true,
                    pointStyleWidth: 8,
                    boxHeight: 8,
                    padding: 16,
                    font: {
                        family: "'Plus Jakarta Sans', sans-serif",
                        size: 12,
                        weight: 500,
                    },
                },
            },
            tooltip: {
                backgroundColor: isDark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                titleColor: headingColor,
                bodyColor: textColor,
                borderColor: isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(226, 232, 240, 1)',
                borderWidth: 1,
                padding: 12,
                cornerRadius: 10,
                boxPadding: 6,
                usePointStyle: true,
                shadowOffsetX: 0,
                shadowOffsetY: 8,
                shadowBlur: 16,
                shadowColor: 'rgba(0,0,0,0.15)',
                bodyFont: {
                    family: "'Plus Jakarta Sans', sans-serif",
                    size: 12,
                },
                titleFont: {
                    family: "'Plus Jakarta Sans', sans-serif",
                    size: 13,
                    weight: 600,
                },
            },
        },
    };

    // 1. Doughnut Chart - Attendance Status
    const doughnut = document.getElementById('attendance-status-chart');
    if (doughnut) {
        new Chart(doughnut, {
            type: 'doughnut',
            data: {
                labels: attendanceStatus.labels,
                datasets: [{
                    data: attendanceStatus.values,
                    backgroundColor: [
                        '#10b981', // Present - Emerald
                        '#f59e0b', // Late - Amber
                        '#ef4444', // Absent - Rose
                        '#8b5cf6', // Leave - Violet
                    ],
                    borderColor: isDark ? '#111827' : '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 6,
                }],
            },
            options: {
                ...commonOptions,
                cutout: '72%',
                plugins: {
                    ...commonOptions.plugins,
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: textColor,
                            usePointStyle: true,
                            padding: 16,
                            font: {
                                family: "'Plus Jakarta Sans', sans-serif",
                                size: 12,
                                weight: 500,
                            },
                        },
                    },
                },
            },
        });
    }

    // 2. Bar Chart - Employees by Department
    const department = document.getElementById('department-chart');
    if (department) {
        const ctx = department.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, '#6366f1');
        gradient.addColorStop(1, '#818cf8');

        new Chart(department, {
            type: 'bar',
            data: {
                labels: departments.labels,
                datasets: [{
                    label: 'Số nhân viên',
                    data: departments.values,
                    backgroundColor: gradient,
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 38,
                }],
            },
            options: {
                ...commonOptions,
                scales: {
                    x: {
                        ticks: {
                            color: textColor,
                            font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 },
                        },
                        grid: { display: false },
                        border: { display: false },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: textColor,
                            precision: 0,
                            font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 },
                        },
                        grid: { color: gridColor },
                        border: { dash: [4, 4], display: false },
                    },
                },
            },
        });
    }

    // 3. Line Chart - Attendance Trend 6 Months
    const trendCanvas = document.getElementById('attendance-trend-chart');
    if (trendCanvas) {
        const ctx = trendCanvas.getContext('2d');
        const presentGradient = ctx.createLinearGradient(0, 0, 0, 260);
        presentGradient.addColorStop(0, 'rgba(16, 185, 129, 0.25)');
        presentGradient.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

        new Chart(trendCanvas, {
            type: 'line',
            data: {
                labels: trend.map((item) => item.label),
                datasets: [
                    {
                        label: 'Có mặt',
                        data: trend.map((item) => item.present),
                        borderColor: '#10b981',
                        backgroundColor: presentGradient,
                        borderWidth: 2.5,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: isDark ? '#111827' : '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.35,
                        fill: true,
                    },
                    {
                        label: 'Đi muộn',
                        data: trend.map((item) => item.late),
                        borderColor: '#f59e0b',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        pointBackgroundColor: '#f59e0b',
                        pointBorderColor: isDark ? '#111827' : '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 3.5,
                        pointHoverRadius: 5,
                        tension: 0.35,
                    },
                    {
                        label: 'Vắng',
                        data: trend.map((item) => item.absent),
                        borderColor: '#ef4444',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        pointBackgroundColor: '#ef4444',
                        pointBorderColor: isDark ? '#111827' : '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 3.5,
                        pointHoverRadius: 5,
                        tension: 0.35,
                    },
                ],
            },
            options: {
                ...commonOptions,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    x: {
                        ticks: {
                            color: textColor,
                            font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 },
                        },
                        grid: { display: false },
                        border: { display: false },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: textColor,
                            precision: 0,
                            font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 },
                        },
                        grid: { color: gridColor },
                        border: { dash: [4, 4], display: false },
                    },
                },
            },
        });
    }
};

const animatePageContent = () => {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduceMotion || typeof Element.prototype.animate !== 'function') {
        return;
    }

    document.querySelectorAll('.app-main > *').forEach((element, index) => {
        element.animate([
            { opacity: 0, transform: 'translateY(8px)' },
            { opacity: 1, transform: 'translateY(0)' },
        ], {
            duration: 360,
            delay: Math.min(index, 4) * 45,
            easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
            fill: 'backwards',
        });
    });
};

animatePageContent();
Alpine.start();
