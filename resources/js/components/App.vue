<template>
    <vue-advanced-chat
        :current-user-id="currentUserId"
        :rooms="JSON.stringify(rooms)"
        :messages="JSON.stringify(messages)"
        :room-actions="JSON.stringify(roomActions)"
    />
</template>

<script>
import { ref } from 'vue';

// export default {
//     name: 'App',
//     setup() {
//         const message = ref('Hello from Vue 3 with Composition API!');
//
//         function updateMessage() {
//             message.value = 'Message updated!';
//         }
//
//         return {
//             message,
//             updateMessage
//         };
//     }
// }
import { register } from 'vue-advanced-chat'
register()

// Or if you used CDN import
// window['vue-advanced-chat'].register()

export default {
    data() {
        return {
            currentUserId: '1234',
            rooms: [],
            messages: [],
            roomActions: [
                { name: 'inviteUser', title: 'Invite User' },
                { name: 'removeUser', title: 'Remove User' },
                { name: 'deleteRoom', title: 'Delete Room' }
            ]
        }
    }
}
</script>

<style scoped>
h1 {
    color: #42b983;
}
</style>
