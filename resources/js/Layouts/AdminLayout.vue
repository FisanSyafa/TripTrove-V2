
<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, computed, getCurrentInstance } from 'vue';

const sidebarOpen = ref(false);
const app = getCurrentInstance();
const page = computed(() => app.appContext.config.globalProperties.$page);
const userName = computed(() => page.value.props.auth?.user?.name || 'Admin');
</script>

<template>
    <div class="min-h-screen bg-[#0c1222] text-gray-100 flex">
        <!-- Mobile Overlay -->
        <transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden"></div>
        </transition>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="w-72 bg-gradient-to-b from-gray-800 via-gray-800 to-gray-900 shadow-2xl flex flex-col h-screen fixed left-0 top-0 z-50 transition-transform duration-300 ease-in-out lg:translate-x-0 border-r border-gray-700/50">

            <!-- Logo -->
            <div class="p-6 pb-4 border-b border-gray-700/50">
                <Link :href="route('admin.dashboard')" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-cyan to-brand-blue flex items-center justify-center shadow-lg shadow-brand-cyan/20 group-hover:shadow-brand-cyan/40 transition-shadow">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg font-extrabold bg-gradient-to-r from-brand-cyan to-brand-blue text-transparent bg-clip-text leading-tight">TripTrove</h1>
                        <p class="text-[10px] uppercase tracking-widest text-gray-500 font-semibold">Admin Panel</p>
                    </div>
                </Link>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto p-4 space-y-1 custom-scrollbar">

                <p class="text-[10px] uppercase tracking-widest text-gray-500 font-bold px-3 pt-2 pb-1">Menu Utama</p>

                <Link :href="route('admin.dashboard')"
                    class="nav-link"
                    :class="{ 'nav-active': $page.component === 'Admin/Dashboard' }">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard</span>
                </Link>

                <Link :href="route('admin.messages.index')"
                    class="nav-link"
                    :class="{ 'nav-active': $page.component.startsWith('Admin/Messages') }">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    <span>Messages</span>
                </Link>

                <p class="text-[10px] uppercase tracking-widest text-gray-500 font-bold px-3 pt-5 pb-1">Operasional</p>

                <Link :href="route('admin.packages.index')"
                    class="nav-link"
                    :class="{ 'nav-active': $page.component.startsWith('Admin/Packages') }">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                    <span>Manage Packages</span>
                </Link>

                <Link :href="route('admin.bookings.index')"
                    class="nav-link"
                    :class="{ 'nav-active': $page.component.startsWith('Admin/Bookings') }">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <span>Manage Bookings</span>
                </Link>

                <Link :href="route('admin.dream-tour-requests.index')"
                    class="nav-link"
                    :class="{ 'nav-active': $page.component.startsWith('Admin/DreamTourRequests') }">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                    <span>Dream Tour Requests</span>
                </Link>

                <Link :href="route('admin.reviews.index')"
                    class="nav-link"
                    :class="{ 'nav-active': $page.component.startsWith('Admin/Reviews') }">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                    </svg>
                    <span>Manage Reviews</span>
                </Link>

                <Link :href="route('admin.announcements.index')"
                    class="nav-link"
                    :class="{ 'nav-active': $page.component.startsWith('Admin/Announcements') }">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535M10.34 6.66a23.847 23.847 0 008.835-2.535m0 0A23.74 23.74 0 0018.795 3m.38 1.125a23.91 23.91 0 011.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 001.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 010 3.46" />
                    </svg>
                    <span>Announcements</span>
                </Link>

                <p class="text-[10px] uppercase tracking-widest text-gray-500 font-bold px-3 pt-5 pb-1">Inventaris</p>

                <Link :href="route('admin.vehicle-types.index')"
                    class="nav-link"
                    :class="{ 'nav-active': $page.component.startsWith('Admin/VehicleTypes') }">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                    </svg>
                    <span>Vehicle Types</span>
                </Link>

                <Link :href="route('admin.vehicles.index')"
                    class="nav-link"
                    :class="{ 'nav-active': $page.component.startsWith('Admin/Vehicles') }">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                    </svg>
                    <span>Vehicles</span>
                </Link>

                <p class="text-[10px] uppercase tracking-widest text-gray-500 font-bold px-3 pt-5 pb-1">Manajemen</p>

                <Link :href="route('admin.users.index')"
                    class="nav-link"
                    :class="{ 'nav-active': $page.component.startsWith('Admin/Users') }">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    <span>Manage Users</span>
                </Link>
            </nav>

            <!-- User Info & Logout -->
            <div class="p-4 border-t border-gray-700/50 bg-gray-900/50">
                <div class="flex items-center gap-3 mb-3 px-2">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-cyan to-brand-blue flex items-center justify-center text-white font-bold text-sm shadow-md flex-shrink-0">
                        {{ userName.charAt(0).toUpperCase() }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ userName }}</p>
                        <p class="text-[10px] text-gray-500 uppercase tracking-wider">Administrator</p>
                    </div>
                </div>
                <Link :href="route('logout')" method="post" as="button"
                      class="w-full flex items-center gap-3 px-3 py-2.5 text-sm text-gray-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                    </svg>
                    <span>Log Out</span>
                </Link>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 lg:ml-72 min-h-screen flex flex-col">
            <!-- Mobile Top Bar -->
            <header class="lg:hidden sticky top-0 z-30 bg-gray-800/95 backdrop-blur-md border-b border-gray-700/50 px-4 py-3 flex items-center justify-between shadow-lg">
                <button @click="sidebarOpen = true" class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
                <h1 class="text-lg font-bold bg-gradient-to-r from-brand-cyan to-brand-blue text-transparent bg-clip-text">TripTrove Admin</h1>
                <div class="w-10"></div>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <header v-if="$slots.header" class="shadow rounded-lg mb-6 p-4">
                    <slot name="header" />
                </header>
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
/* Nav Link Base */
.nav-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.625rem 0.75rem;
    font-size: 0.875rem;
    color: #9ca3af;
    border-radius: 0.5rem;
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
}

.nav-link:hover {
    color: #fff;
    background-color: rgba(55, 65, 81, 0.4);
}

/* Nav Active State */
.nav-active {
    color: var(--brand-cyan, #00d4ff) !important;
    font-weight: 600;
    background-color: rgba(0, 212, 255, 0.1) !important;
    border-left-color: var(--brand-cyan, #00d4ff) !important;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #374151;
    border-radius: 2px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #4b5563;
}
</style>