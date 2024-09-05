import { defineConfig } from 'vite'
import laravel, { refreshPaths } from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: [
                ...refreshPaths,
                'app/Filament/**',
                'app/Forms/Components/**',
                'app/Livewire/**',
                'app/Infolists/Components/**',
                'app/Providers/Filament/**',
                'app/Tables/Columns/**',
            ],
        }),
        // vue(),
        vue({
            template: {
                compilerOptions: {
                    // treat all tags with a dash as custom elements
                    // isCustomElement: (tag) => tag.includes('-')
                    isCustomElement: tagName => {
                        return tagName === 'vue-advanced-chat' || tagName === 'emoji-picker'
                    }
                }
            }
        })
    ],
})
