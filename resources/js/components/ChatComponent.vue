    <!--<template>-->
    <!--    <div>-->
    <!--        <h2 style="color: red">chat</h2>-->
    <!--        &lt;!&ndash; Display messages &ndash;&gt;-->
    <!--        <div v-for="message in initialMessages" :key="message.id">-->
    <!--            {{ message.content }}-->
    <!--        </div>-->
    <!--    </div>-->
    <!--</template>-->

    <!--<script setup>-->
    <!--import { defineProps } from 'vue';-->

    <!--const props = defineProps({-->
    <!--    initialMessages: Array-->
    <!--});-->
    <!--</script>-->
    <template>

        <vue-advanced-chat
            :current-user-id="currentUserId"
            :rooms="JSON.stringify(rooms)"
            :messages="JSON.stringify(messages)"
            :room-actions="JSON.stringify(roomActions)"
        />
    </template>

    <script>
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
