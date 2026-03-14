<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import { Line, Doughnut, Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
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
ChartJS.register(
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

const props = defineProps({
    stats: Object,
    chartData: Object,
    monthlyRevenueSummary: Array,
    recentBookings: Array,
    recentReviews: Array,
});

// Helper
const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const formatNumber = (value) => {
    return new Intl.NumberFormat('id-ID').format(value);
};

const getStatusClass = (status) => {
    switch (status) {
        case 'pending': return 'bg-yellow-500/80 text-yellow-100 border border-yellow-600';
        case 'waiting_confirmation': return 'bg-cyan-500/80 text-cyan-100 border border-cyan-600';
        case 'paid': return 'bg-blue-500/80 text-blue-100 border border-blue-600';
        case 'confirmed': return 'bg-green-500/80 text-green-100 border border-green-600';
        case 'cancelled': return 'bg-red-500/80 text-red-100 border border-red-600';
        default: return 'bg-gray-500/80 text-gray-100 border border-gray-600';
    }
};

// Revenue Line Chart Data
const revenueChartData = computed(() => ({
    labels: props.chartData.monthlyRevenue.map(item => item.month),
    datasets: [{
        label: 'Pendapatan',
        data: props.chartData.monthlyRevenue.map(item => item.revenue),
        borderColor: '#22d3ee',
        backgroundColor: 'rgba(34, 211, 238, 0.1)',
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#22d3ee',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointRadius: 4,
    }]
}));

const revenueChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            callbacks: {
                label: (context) => formatCurrency(context.raw)
            }
        }
    },
    scales: {
        x: {
            grid: { color: 'rgba(75, 85, 99, 0.3)' },
            ticks: { color: '#9ca3af' }
        },
        y: {
            grid: { color: 'rgba(75, 85, 99, 0.3)' },
            ticks: { 
                color: '#9ca3af',
                callback: (value) => formatNumber(value / 1000000) + 'jt'
            }
        }
    }
};

// Booking Status Doughnut Chart
const statusLabels = {
    pending: 'Pending',
    waiting_confirmation: 'Menunggu Konfirmasi',
    paid: 'Dibayar',
    confirmed: 'Dikonfirmasi',
    cancelled: 'Dibatalkan',
    completed: 'Selesai',
};

const statusColors = {
    pending: '#eab308',
    waiting_confirmation: '#22d3ee',
    paid: '#3b82f6',
    confirmed: '#22c55e',
    cancelled: '#ef4444',
    completed: '#8b5cf6',
};

const bookingStatusChartData = computed(() => {
    const data = props.chartData.bookingStatusDistribution;
    return {
        labels: Object.keys(data).map(key => statusLabels[key] || key),
        datasets: [{
            data: Object.values(data),
            backgroundColor: Object.keys(data).map(key => statusColors[key] || '#6b7280'),
            borderWidth: 0,
        }]
    };
});

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                color: '#9ca3af',
                padding: 15,
                usePointStyle: true,
            }
        }
    },
    cutout: '60%',
};

// Top Packages Bar Chart
const topPackagesChartData = computed(() => ({
    labels: props.chartData.topPackages.map(pkg => pkg.name.length > 20 ? pkg.name.substring(0, 20) + '...' : pkg.name),
    datasets: [{
        label: 'Revenue',
        data: props.chartData.topPackages.map(pkg => pkg.total_revenue),
        backgroundColor: 'rgba(34, 211, 238, 0.8)',
        borderRadius: 4,
    }]
}));

const barChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    indexAxis: 'y',
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: (context) => formatCurrency(context.raw)
            }
        }
    },
    scales: {
        x: {
            grid: { color: 'rgba(75, 85, 99, 0.3)' },
            ticks: { 
                color: '#9ca3af',
                callback: (value) => formatNumber(value / 1000000) + 'jt'
            }
        },
        y: {
            grid: { display: false },
            ticks: { color: '#9ca3af' }
        }
    }
};
</script>

<template>
    <Head title="Admin Dashboard" />
    <AdminLayout>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-brand-cyan">Dashboard</h1>
            <div class="flex gap-2">
                <a :href="route('admin.export.revenue')" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg transition flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export Revenue
                </a>
                <a :href="route('admin.export.bookings')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export Bookings
                </a>
                <a :href="route('admin.export.packages')" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm rounded-lg transition flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export Packages
                </a>
            </div>
        </div>

        <!-- Stats Cards - Row 1 -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <Link :href="route('admin.bookings.index')" class="bg-gray-800 rounded-lg shadow-lg p-6 border border-brand-border hover:border-yellow-500 transition-colors group">
                <div class="flex justify-between items-start">
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-400">Booking Perlu Konfirmasi</p>
                        <p class="text-3xl font-bold text-white">{{ stats.pendingBookings }}</p>
                    </div>
                    <div class="p-3 bg-yellow-500/20 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-yellow-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                </div>
            </Link>

            <Link :href="route('admin.reviews.index', { status: 'pending' })" class="bg-gray-800 rounded-lg shadow-lg p-6 border border-brand-border hover:border-cyan-500 transition-colors group">
                <div class="flex justify-between items-start">
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-400">Review Perlu Moderasi</p>
                        <p class="text-3xl font-bold text-white">{{ stats.pendingReviews }}</p>
                    </div>
                    <div class="p-3 bg-cyan-500/20 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-cyan-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.31h5.418a.562.562 0 0 1 .321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-3.356a.562.562 0 0 0-.652 0l-4.725 3.356a.562.562 0 0 1-.84-.61l1.285-5.385a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988h5.418a.563.563 0 0 0 .475-.31l2.125-5.111Z" />
                        </svg>
                    </div>
                </div>
            </Link>

            <div class="bg-gray-800 rounded-lg shadow-lg p-6 border border-brand-border">
                <div class="flex justify-between items-start">
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-400">Total Pendapatan</p>
                        <p class="text-2xl font-bold text-white">{{ formatCurrency(stats.totalRevenue) }}</p>
                    </div>
                    <div class="p-3 bg-green-500/20 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-green-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.768 0-1.536.219-2.121.659-.922.69-1.428 1.74-1.428 2.828v.005Z" />
                        </svg>
                    </div>
                </div>
            </div>

            <Link :href="route('admin.users.index')" class="bg-gray-800 rounded-lg shadow-lg p-6 border border-brand-border hover:border-blue-500 transition-colors group">
                <div class="flex justify-between items-start">
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-400">Total Pelanggan</p>
                        <p class="text-3xl font-bold text-white">{{ stats.totalCustomers }}</p>
                    </div>
                    <div class="p-3 bg-blue-500/20 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-blue-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                    </div>
                </div>
            </Link>
        </div>

        <!-- Stats Cards - Row 2 -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-gray-800 rounded-lg shadow-lg p-6 border border-brand-border">
                <div class="flex justify-between items-start">
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-400">Total Booking</p>
                        <p class="text-3xl font-bold text-white">{{ stats.totalBookings }}</p>
                    </div>
                    <div class="p-3 bg-indigo-500/20 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-indigo-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <Link :href="route('admin.packages.index')" class="bg-gray-800 rounded-lg shadow-lg p-6 border border-brand-border hover:border-purple-500 transition-colors">
                <div class="flex justify-between items-start">
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-400">Paket Aktif</p>
                        <p class="text-3xl font-bold text-white">{{ stats.activePackages }}</p>
                    </div>
                    <div class="p-3 bg-purple-500/20 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-purple-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                    </div>
                </div>
            </Link>

            <div class="bg-gray-800 rounded-lg shadow-lg p-6 border border-brand-border">
                <div class="flex justify-between items-start">
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-400">Rata-rata Nilai Booking</p>
                        <p class="text-2xl font-bold text-white">{{ formatCurrency(stats.avgBookingValue) }}</p>
                    </div>
                    <div class="p-3 bg-orange-500/20 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-orange-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg shadow-lg p-6 border border-brand-border">
                <div class="flex justify-between items-start">
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-400">Pertumbuhan Bulan Ini</p>
                        <p class="text-3xl font-bold" :class="stats.monthlyGrowth >= 0 ? 'text-green-400' : 'text-red-400'">
                            {{ stats.monthlyGrowth >= 0 ? '+' : '' }}{{ stats.monthlyGrowth }}%
                        </p>
                    </div>
                    <div class="p-3 rounded-full" :class="stats.monthlyGrowth >= 0 ? 'bg-green-500/20' : 'bg-red-500/20'">
                        <svg v-if="stats.monthlyGrowth >= 0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-green-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-red-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Revenue Trend Chart -->
            <div class="lg:col-span-2 bg-gray-800 rounded-lg shadow-lg p-6 border border-brand-border">
                <h2 class="text-xl font-bold text-brand-cyan mb-4 border-b border-gray-700 pb-2">Tren Pendapatan (12 Bulan Terakhir)</h2>
                <div class="h-72">
                    <Line :data="revenueChartData" :options="revenueChartOptions" />
                </div>
            </div>

            <!-- Booking Status Distribution -->
            <div class="bg-gray-800 rounded-lg shadow-lg p-6 border border-brand-border">
                <h2 class="text-xl font-bold text-brand-cyan mb-4 border-b border-gray-700 pb-2">Distribusi Status Booking</h2>
                <div class="h-72">
                    <Doughnut :data="bookingStatusChartData" :options="doughnutOptions" />
                </div>
            </div>
        </div>

        <!-- Top Packages & Monthly Summary -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Top Packages Chart -->
            <div class="bg-gray-800 rounded-lg shadow-lg p-6 border border-brand-border">
                <h2 class="text-xl font-bold text-brand-cyan mb-4 border-b border-gray-700 pb-2">Top 5 Paket Berdasarkan Revenue</h2>
                <div v-if="chartData.topPackages.length > 0" class="h-64">
                    <Bar :data="topPackagesChartData" :options="barChartOptions" />
                </div>
                <div v-else class="text-gray-500 italic text-center py-8">Belum ada data.</div>
            </div>

            <!-- Monthly Revenue Summary Table -->
            <div class="bg-gray-800 rounded-lg shadow-lg p-6 border border-brand-border">
                <h2 class="text-xl font-bold text-brand-cyan mb-4 border-b border-gray-700 pb-2">Ringkasan Pendapatan Bulanan</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-700/50 text-xs uppercase text-gray-400">
                            <tr>
                                <th class="px-4 py-3 text-left">Bulan</th>
                                <th class="px-4 py-3 text-center">Booking</th>
                                <th class="px-4 py-3 text-right">Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            <tr v-for="item in monthlyRevenueSummary" :key="item.month" class="hover:bg-gray-700/30">
                                <td class="px-4 py-3 text-white">{{ item.month }}</td>
                                <td class="px-4 py-3 text-center text-gray-300">{{ item.bookings }}</td>
                                <td class="px-4 py-3 text-right text-green-400 font-medium">{{ formatCurrency(item.revenue) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Bookings & Reviews -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-gray-800 rounded-lg shadow-lg p-6 border border-brand-border">
                <h2 class="text-xl font-bold text-brand-cyan mb-4 border-b border-gray-700 pb-2">Booking Perlu Aksi</h2>
                <div v-if="recentBookings.length === 0" class="text-gray-500 italic">Tidak ada booking yang perlu aksi.</div>
                <div v-else class="space-y-4">
                    <div v-for="booking in recentBookings" :key="booking.id" class="flex justify-between items-center">
                        <div>
                            <Link :href="route('admin.bookings.show', booking.id)" class="font-medium text-white hover:text-brand-cyan transition">{{ booking.user.name }} - {{ booking.tour_package.name }}</Link>
                            <p class="text-sm text-gray-400">{{ new Date(booking.departure_date).toLocaleDateString('id-ID', {day: '2-digit', month: 'long', year: 'numeric'}) }}</p>
                        </div>
                        <span class="px-3 py-1 text-xs font-bold rounded-full uppercase" :class="getStatusClass(booking.status)">
                            {{ booking.status.replace('_', ' ') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg shadow-lg p-6 border border-brand-border">
                <h2 class="text-xl font-bold text-brand-cyan mb-4 border-b border-gray-700 pb-2">Review Perlu Moderasi</h2>
                <div v-if="recentReviews.length === 0" class="text-gray-500 italic">Tidak ada review yang perlu dimoderasi.</div>
                <div v-else class="space-y-4">
                     <div v-for="review in recentReviews" :key="review.id" class="flex justify-between items-center">
                        <div>
                            <Link :href="route('admin.reviews.index')" class="font-medium text-white hover:text-brand-cyan transition">{{ review.user.name }} - {{ review.tour_package.name }}</Link>
                            <p class="text-sm text-yellow-400 font-bold">{{ '★'.repeat(review.rating) }}</p>
                        </div>
                        <Link :href="route('admin.reviews.index')" class="px-3 py-1 text-xs font-bold rounded-full uppercase bg-yellow-500/80 text-yellow-100 border border-yellow-600">
                            Pending
                        </Link>
                    </div>
                </div>
            </div>
        </div>

    </AdminLayout>
</template>