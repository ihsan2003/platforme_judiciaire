import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',               
                'resources/css/dashboard.css', 
                'resources/css/notification-bell.css', 
                'resources/css/partie-create.css', 
                'resources/css/audience.css', 
                'resources/css/login.css', 



                'resources/js/app.js',
                'resources/js/dashboard.js',    
                'resources/js/notification-bell.js',    
                'resources/js/avocat-partie-select.js',    
                'resources/js/partie-avocat-select.js',    
                'resources/js/tribunaux-edit.js',    
                'resources/js/audience-create.js',    
                'resources/js/audience-edit.js',    
        


            ],
            refresh: true,
        }),
    ],
});
