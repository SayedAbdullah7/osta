<x-filament-panels::page>
    @push('scripts')
        @vite(['resources/js/vue.js'])
    @endpush
    <div id="app">
        <chat-component></chat-component>
    </div>
</x-filament-panels::page>
