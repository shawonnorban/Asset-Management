import '../css/shadcn.css';

import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';

const appName = 'Asset Management System';
const pages = import.meta.glob('./pages/**/*.tsx');

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),

    resolve: async (name) => {
        const page = pages[`./pages/${name}.tsx`];

        if (!page) {
            throw new Error(`Inertia page not found: ${name}`);
        }

        return (await page()) as never;
    },

    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },

    progress: {
        color: '#0f172a',
    },
});
