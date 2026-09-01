import './bootstrap';

const savedTheme = localStorage.getItem('hr-theme');
if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
}

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

window.renderDashboardCharts = ({ attendanceStatus, departments, trend }) => {
    const dark = document.documentElement.classList.contains('dark');
    const textColor = dark ? '#d1e9f7' : '#475569';
    const gridColor = dark ? 'rgba(148, 197, 222, .22)' : 'rgba(148, 163, 184, .2)';
    const common = { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: textColor, usePointStyle: true, padding: 18 } } } };
    const doughnut = document.getElementById('attendance-status-chart');
    if (doughnut) new Chart(doughnut, { type: 'doughnut', data: { labels: attendanceStatus.labels, datasets: [{ data: attendanceStatus.values, backgroundColor: ['#22c55e', '#f59e0b', '#ef4444', '#8b5cf6'], borderColor: dark ? '#20577d' : '#fff', borderWidth: 3 }] }, options: { ...common, cutout: '66%' } });
    const department = document.getElementById('department-chart');
    if (department) new Chart(department, { type: 'bar', data: { labels: departments.labels, datasets: [{ label: 'Số nhân viên', data: departments.values, backgroundColor: '#f97316', borderRadius: 8, maxBarThickness: 42 }] }, options: { ...common, scales: { x: { ticks: { color: textColor }, grid: { display: false } }, y: { beginAtZero: true, ticks: { color: textColor, precision: 0 }, grid: { color: gridColor } } } } });
    const trendCanvas = document.getElementById('attendance-trend-chart');
    if (trendCanvas) new Chart(trendCanvas, { type: 'line', data: { labels: trend.map((item) => item.label), datasets: [{ label: 'Có mặt', data: trend.map((item) => item.present), borderColor: '#22c55e', backgroundColor: 'rgba(34, 197, 94, .12)', tension: .35, fill: true }, { label: 'Đi muộn', data: trend.map((item) => item.late), borderColor: '#f59e0b', backgroundColor: 'transparent', tension: .35 }, { label: 'Vắng', data: trend.map((item) => item.absent), borderColor: '#ef4444', backgroundColor: 'transparent', tension: .35 }] }, options: { ...common, scales: { x: { ticks: { color: textColor }, grid: { color: gridColor } }, y: { beginAtZero: true, ticks: { color: textColor, precision: 0 }, grid: { color: gridColor } } } } });
};

Alpine.start();
