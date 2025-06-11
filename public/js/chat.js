let ticketId = '';
let userName = '';
let conversationId = '';
const chatWindow = $('#chatWindow');
const messageInput = $('#messageInput');
const conversationName = $('#conversationName');

// Function to generate an "out" message HTML
function generateOutMessage({time, avatarSrc, userName, messageText}) {
    return `
            <div class="d-flex justify-content-end mb-10">
                <div class="d-flex flex-column align-items-end">
                    <div class="d-flex align-items-center mb-2">
                        <div class="me-3">
                            <span class="text-muted fs-7 mb-1">${time}</span>
                            <a href="#" class="fs-5 fw-bold text-gray-900 text-hover-primary ms-1">${userName}</a>
                        </div>
                        <div class="symbol symbol-35px symbol-circle">
                            <img alt="Pic" src="${avatarSrc}" />
                        </div>
                    </div>
                    <div class="p-5 rounded bg-light-primary text-gray-900 fw-semibold mw-lg-400px text-end">${messageText}</div>
                </div>
            </div>
        `;
}

// Function to generate an "in" message HTML
function generateInMessage({time, avatarSrc, userName, messageText, mediaItems}) {
    let messageHtml = `
                <div class="d-flex justify-content-start mb-10">
                    <div class="d-flex flex-column align-items-start">
                        <div class="d-flex align-items-center mb-2">
                            <div class="symbol symbol-35px symbol-circle">
                            <img alt="Pic" src="${avatarSrc}" />
                                </div>
                            <div class="ms-3">
                                <a href="#" class="fs-5 fw-bold text-gray-900 text-hover-primary me-1">${userName}</a>
                                <span class="text-muted fs-7 mb-1">${time}</span>
                            </div>
                        </div>
                        <div class="p-5 rounded bg-light-info text-gray-900 fw-semibold mw-lg-400px text-start">${messageText}</div>
            `;
    // If there are media items, add them to the message
    if (mediaItems && mediaItems.length > 0) {
        // messageHtml += `
        //     <div class="media-gallery">
        //     <div class="row">
        //     `;
        mediaItems.forEach(media => {
            messageHtml += `
                            <div class="col-6">
                                <!-- begin::Overlay -->
                                <a class="d-block overlay" data-fslightbox="lightbox-basic" href="${media.url}">
                                    <!-- begin::Image -->
                                    <div class="overlay-wrapper bgi-no-repeat bgi-position-center bgi-size-cover card-rounded min-h-175px"
                                        style="background-image:url('${media.thumb}')">
                                    </div>
                                    <!-- end::Image -->

                                    <!-- begin::Action -->
                                    <div class="overlay-layer card-rounded bg-dark bg-opacity-25 shadow">
                                        <i class="bi bi-eye-fill text-white fs-3x"></i>
                                    </div>
                                    <!-- end::Action -->
                                </a>
                                <!-- end::Overlay -->
                            </div>
                        `;
        });
        // messageHtml += `</div></div>`; // Close the row and media-gallery div
    }

    // messageHtml += `</div></div>`; // Close the main message divs

    return messageHtml;
    //     return `
    //     <div class="d-flex justify-content-start mb-10">
    //         <div class="d-flex flex-column align-items-start">
    //             <div class="d-flex align-items-center mb-2">
    //                 <div class="symbol symbol-35px symbol-circle">
    //                     <img alt="Pic" src="${avatarSrc}" />
    //                 </div>
    //                 <div class="ms-3">
    //                     <a href="#" class="fs-5 fw-bold text-gray-900 text-hover-primary me-1">${userName}</a>
    //                     <span class="text-muted fs-7 mb-1">${time}</span>
    //                 </div>
    //             </div>
    //             <div class="p-5 rounded bg-light-info text-gray-900 fw-semibold mw-lg-400px text-start">${messageText}</div>
    //         </div>
    //     </div>
    // `;
}

// Function to fetch conversation and messages
const fetchTicketAndConversation = (ticketId) => {
    $.get(`/chat/conversation/${ticketId}`, function (ticket) {
        const conversation = ticket.conversation;
        conversationName.text(ticket.name);
        userName = ticket.short_name;
        conversationId = conversation.id;
        fetchMessages(conversationId);
        setInterval(() => fetchMessages(conversationId, true), 3000);


    }).fail(function () {
        console.error('Error fetching conversation.');
    });
};
const fetchConversation = (orderId) => {
    $.get(`/chat/conversation/${orderId}?type=order`, function (conversation) {
        console.log(conversation);
        userId = conversation.user_id;
        console.log('conversation user id', userId);
        conversationName.text(conversation.name);
        userName = conversation.user_short_name;
        providerName = conversation.provider_short_name;
        conversationId = conversation.id;
        fetchMessages(conversationId, false, userId);
        setInterval(() => fetchMessages(conversationId, true, userId), 3000);
    }).fail(function () {
        console.error('Error fetching conversation.');
    });
};
let lastFetchedMessageId = null;
// Function to fetch messages for the conversation
const fetchMessages = (id, stopScroll = false,  userId = null) => {
    console.log('fetchMessages user id', userId);
    if (!id) id = conversationId;

    if (!$('#kt_drawer_chat').hasClass('drawer-on')) {
        console.log('Chat drawer is closed. Stopping message fetch.');
        return;
    }


    $.get(`/chat/messages/${id}?userId=${userId}`, function (reponse) {
        chatWindow.html('');
        messages = reponse;
        console.log(reponse)
        console.log(messages)
        let newLastMessageId = null;  // To keep track of the last message ID
        // for (const element of array) {
        //     console.log(element); // Access each element directly
        // }
        messages.forEach(msg => {
            const isCurrentUser = msg.is_sender;
            const avatarUrl = isCurrentUser ? 'assets/media/avatars/300-1.jpg' : 'assets/media/avatars/300-25.jpg';
            const messageData = {
                time: moment(msg.created_at).fromNow(),
                avatarSrc: avatarUrl,
                userName: isCurrentUser ? 'You' : userName,
                messageText: msg.content,
                mediaItems: msg.media
            };
            const messageHtml = isCurrentUser ? generateOutMessage(messageData) : generateInMessage(messageData);
            chatWindow.append(messageHtml);
            newLastMessageId = msg.id;
        });
        // // if (!stopScroll) {
        //     chatWindow.scrollTop(chatWindow.prop("scrollHeight"));
        // // }
        console.log('newLastMessageId', newLastMessageId);
        console.log('lastFetchedMessageId', lastFetchedMessageId);
        if (newLastMessageId !== lastFetchedMessageId) {
            lastFetchedMessageId = newLastMessageId;
            // if (!stopScroll) {
            chatWindow.scrollTop(chatWindow.prop("scrollHeight"));
            // }
        }
    }).fail(function () {
        console.error('Error fetching messages.');
    });
};

// Send message function
const sendMessage = () => {
    $('.sending-status').remove();
    const message = messageInput.val().trim();
    if (!message) return;
    // Create a temporary ID for tracking
    const tempId = `temp-${Date.now()}`;

    const messageData = {
        time: moment().fromNow(),
        avatarSrc: 'assets/media/avatars/300-1.jpg',
        userName: 'You',
        messageText: message,
    };
    let messageHtml = generateOutMessage(messageData);
    messageHtml = messageHtml.replace(
        /(<div class="p-5 rounded bg-light-primary text-gray-900 fw-semibold mw-lg-400px text-end">.*<\/div>)/,
        '$1' + '<div class="text-muted fs-7 sending-status">Sending...</div>'
    );
    messageHtml = messageHtml.replace(
        '<div class="d-flex justify-content-end mb-10">',
        '<div id="' + tempId + '" class="temp-message d-flex justify-content-end mb-10">'
    );
    chatWindow.append(messageHtml);
    messageInput.val('');
    chatWindow.scrollTop(chatWindow.prop("scrollHeight"));
    // <div class="text-muted fs-7 sending-status">Sending...</div>

    $.post('/chat/send', {message, conversation_id: conversationId}, function () {
        // On success, remove "Sending..." and finalize the message
        // $(`#${tempId} .sending-status`).remove();
        $(`#${tempId} .sending-status`).text('sen   t');
        // $(`#${tempId} .sending-status`).removeClass('text-muted')
        // $(`#${tempId} .sending-status`).addClass('text-success')
    }).fail(function () {
        console.error('Error sending message.');
        // On failure, update the message with an error status
        $(`#${tempId} .sending-status`).text('Failed to send');
        $(`#${tempId} .sending-status`).removeClass('text-muted')
        $(`#${tempId} .sending-status`).addClass('text-danger')
        $(`#${tempId} .sending-status`).removeClass('sending-status')

    });
};

// Event listeners
$('#sendMessage').on('click', sendMessage);
messageInput.on('keypress', function (e) {
    if (e.which === 13) sendMessage();
});

$(document).on('click', '.openChat', function () {
    var $type = $(this).data('type');
    if ($type && $type === 'order') {
        orderId = $(this).data('order-id');
        console.log('Order ID:', orderId);
        chatWindow.html('');
        fetchConversation(orderId);
        $('#kt_drawer_chat_toggle').click();

    } else {
        ticketId = $(this).data('ticket-id');
        chatWindow.html('');
        fetchTicketAndConversation(ticketId);
        $('#kt_drawer_chat_toggle').click();
    }

});
document.addEventListener("DOMContentLoaded", function () {
    new VenoBox({
        selector: '.my-image-links',
        numeration: true,
        infinigall: true,
        share: true,
        spinner: 'rotating-plane'
    });
});
