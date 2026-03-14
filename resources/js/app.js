import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        app.config.globalProperties.__ = (key) => {
            const page = app.config.globalProperties.$page;
            const translations = page.props.translations || {};
            return translations[key] || key;
        };

        app.config.globalProperties.$formatCurrency = (value) => {
            const page = app.config.globalProperties.$page;
            
            const selectedCurrency = page.props.currency || 'IDR';
            
            const rates = page.props.currencyRates || {}; 

            let rate = 1; 
            let fractionDigits = 0;

            if (selectedCurrency !== 'IDR' && rates[selectedCurrency]) {
                rate = rates[selectedCurrency];
                fractionDigits = 2;
            }

            const numericValue = Number(value);
            if (isNaN(numericValue)) {
                return value;
            }

            const convertedValue = numericValue * rate;

            let localeString = 'id-ID';
            if (page.props.locale === 'en') localeString = 'en-US';
            if (page.props.locale === 'ms') localeString = 'ms-MY';

            return new Intl.NumberFormat(localeString, {
                style: 'currency',
                currency: selectedCurrency,
                minimumFractionDigits: fractionDigits,
                maximumFractionDigits: fractionDigits
            }).format(convertedValue);
        };

        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});