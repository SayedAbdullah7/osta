import { createApp } from 'vue';
import ChatComponent from './components/ChatComponent.vue';

// Import your Vue component
import App from './components/App.vue';

const app = createApp(App);
app.component('chat-component', ChatComponent);
// Mount the Vue app to a DOM element with ID 'app'
app.mount('#app');
