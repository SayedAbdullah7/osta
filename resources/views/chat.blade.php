{{-- Load VenoBox CSS --}}
<link href="{{ asset('assets/plugins/VenoBox/venobox.min.css') }}" rel="stylesheet"/>

<!--begin::Chat drawer-->
<div id="kt_drawer_chat" class="bg-body" data-kt-drawer="true" data-kt-drawer-name="chat" data-kt-drawer-activate="true"
     data-kt-drawer-overlay="true" data-kt-drawer-width="{default:'300px', 'md': '500px'}"
     data-kt-drawer-direction="end" data-kt-drawer-toggle="#kt_drawer_chat_toggle"
     data-kt-drawer-close="#kt_drawer_chat_close">
    <!--begin::Messenger-->
    <div class="card w-100 border-0 rounded-0" id="kt_drawer_chat_messenger">
        <!--begin::Card header-->
        <div class="card-header pe-5" id="kt_drawer_chat_messenger_header">
            <!--begin::Title-->
            <div class="card-title">
                <!--begin::User-->
                <div class="d-flex justify-content-center flex-column me-3">
                    <a id="conversationName" href="#" class="fs-4 fw-bold text-gray-900 text-hover-primary me-1 mb-2 lh-1">Brian Cox</a>
                    <!--begin::Info-->
                    <div class="mb-0 lh-1">
                        <span class="badge badge-success badge-circle w-10px h-10px me-1"></span>
                        <span class="fs-7 fw-semibold text-muted">Active</span>
                    </div>
                    <!--end::Info-->
                </div>
                <!--end::User-->
            </div>
            <!--end::Title-->
            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                <!--begin::Menu-->
                <div class="me-0">
                    <button class="btn btn-sm btn-icon btn-active-color-primary" data-kt-menu-trigger="click"
                            data-kt-menu-placement="bottom-end">
                        <i class="ki-duotone ki-dots-square fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </i>
                    </button>
                    <!--begin::Menu 3-->
                    <div
                        class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px py-3"
                        data-kt-menu="true">
                        <!--begin::Heading-->
                        <div class="menu-item px-3">
                            <div class="menu-content text-muted pb-2 px-3 fs-7 text-uppercase">Contacts</div>
                        </div>
                        <!--end::Heading-->
                        <!--begin::Menu item-->
                        <div class="menu-item px-3">
                            <a href="#" class="menu-link px-3" data-bs-toggle="modal"
                               data-bs-target="#kt_modal_users_search">Add Contact</a>
                        </div>
                        <!--end::Menu item-->
                        <!--begin::Menu item-->
                        <div class="menu-item px-3">
                            <a href="#" class="menu-link flex-stack px-3" data-bs-toggle="modal"
                               data-bs-target="#kt_modal_invite_friends">Invite Contacts
                                <span class="ms-2" data-bs-toggle="tooltip"
                                      title="Specify a contact email to send an invitation">
										<i class="ki-duotone ki-information fs-7">
											<span class="path1"></span>
											<span class="path2"></span>
											<span class="path3"></span>
										</i>
									</span></a>
                        </div>
                        <!--end::Menu item-->
                        <!--begin::Menu item-->
                        <div class="menu-item px-3" data-kt-menu-trigger="hover" data-kt-menu-placement="right-start">
                            <a href="#" class="menu-link px-3">
                                <span class="menu-title">Groups</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <!--begin::Menu sub-->
                            <div class="menu-sub menu-sub-dropdown w-175px py-4">
                                <!--begin::Menu item-->
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3" data-bs-toggle="tooltip" title="Coming soon">Create
                                        Group</a>
                                </div>
                                <!--end::Menu item-->
                                <!--begin::Menu item-->
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3" data-bs-toggle="tooltip" title="Coming soon">Invite
                                        Members</a>
                                </div>
                                <!--end::Menu item-->
                                <!--begin::Menu item-->
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3" data-bs-toggle="tooltip" title="Coming soon">Settings</a>
                                </div>
                                <!--end::Menu item-->
                            </div>
                            <!--end::Menu sub-->
                        </div>
                        <!--end::Menu item-->
                        <!--begin::Menu item-->
                        <div class="menu-item px-3 my-1">
                            <a href="#" class="menu-link px-3" data-bs-toggle="tooltip" title="Coming soon">Settings</a>
                        </div>
                        <!--end::Menu item-->
                    </div>
                    <!--end::Menu 3-->
                </div>
                <!--end::Menu-->
                <!--begin::Close-->
                <div class="btn btn-sm btn-icon btn-active-color-primary" id="kt_drawer_chat_close">
                    <i class="ki-duotone ki-cross-square fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
                <!--end::Close-->
            </div>
            <!--end::Card toolbar-->
        </div>
        <!--end::Card header-->
        <!--begin::Card body-->
        <div class="card-body" id="kt_drawer_chat_messenger_body">
            <!--begin::Messages-->
            <div id="chatWindow" class="scroll-y me-n5 pe-5" data-kt-element="messages" data-kt-scroll="true"
                 data-kt-scroll-activate="true" data-kt-scroll-height="auto"
                 data-kt-scroll-dependencies="#kt_drawer_chat_messenger_header, #kt_drawer_chat_messenger_footer"
                 data-kt-scroll-wrappers="#kt_drawer_chat_messenger_body" data-kt-scroll-offset="0px">
                <!--begin::Message(template for out)-->
                <div class="d-flex justify-content-end mb-10 d-none" data-kt-element="template-out">
                    <!--begin::Wrapper-->
                    <div class="d-flex flex-column align-items-end">
                        <!--begin::User-->
                        <div class="d-flex align-items-center mb-2">
                            <!--begin::Details-->
                            <div class="me-3">
                                <span class="text-muted fs-7 mb-1">Just now</span>
                                <a href="#" class="fs-5 fw-bold text-gray-900 text-hover-primary ms-1">You</a>
                            </div>
                            <!--end::Details-->
                            <!--begin::Avatar-->
                            <div class="symbol symbol-35px symbol-circle">
                                <img alt="Pic" src="assets/media/avatars/300-1.jpg"/>
                            </div>
                            <!--end::Avatar-->
                        </div>
                        <!--end::User-->
                        <!--begin::Text-->
                        <div class="p-5 rounded bg-light-primary text-gray-900 fw-semibold mw-lg-400px text-end"
                             data-kt-element="message-text"></div>
                        <!--end::Text-->
                    </div>
                    <!--end::Wrapper-->
                </div>
                <!--end::Message(template for out)-->
                <!--begin::Message(template for in)-->
                <div class="d-flex justify-content-start mb-10 d-none" data-kt-element="template-in">
                    <!--begin::Wrapper-->
                    <div class="d-flex flex-column align-items-start">
                        <!--begin::User-->
                        <div class="d-flex align-items-center mb-2">
                            <!--begin::Avatar-->
                            <div class="symbol symbol-35px symbol-circle">
                                <img alt="Pic" src="assets/media/avatars/300-25.jpg"/>
                            </div>
                            <!--end::Avatar-->
                            <!--begin::Details-->
                            <div class="ms-3">
                                <a href="#" class="fs-5 fw-bold text-gray-900 text-hover-primary me-1">Brian Cox</a>
                                <span class="text-muted fs-7 mb-1">Just now</span>
                            </div>
                            <!--end::Details-->
                        </div>
                        <!--end::User-->
                        <!--begin::Text-->
                        <div class="p-5 rounded bg-light-info text-gray-900 fw-semibold mw-lg-400px text-start"
                             data-kt-element="message-text">Right before vacation season we have the next Big Deal for
                            you.
                        </div>
                        <!--end::Text-->
                    </div>
                    <!--end::Wrapper-->
                </div>
                <!--end::Message(template for in)-->
            </div>
            <!--end::Messages-->
        </div>
        <!--end::Card body-->
        <!--begin::Card footer-->
        <div class="card-footer pt-4" id="kt_drawer_chat_messenger_footer">
            <!--begin::Input-->
            <textarea id="messageInput" class="form-control form-control-flush mb-3" rows="1" data-kt-element="input"
                      placeholder="Type a message"></textarea>
            <!--end::Input-->
            <!--begin:Toolbar-->
            <div class="d-flex flex-stack">
                <!--begin::Actions-->
                <div class="d-flex align-items-center me-2">
                    <button class="btn btn-sm btn-icon btn-active-light-primary me-1" type="button"
                            data-bs-toggle="tooltip" title="Coming soon">
                        <i class="ki-duotone ki-paper-clip fs-3"></i>
                    </button>
                    <button class="btn btn-sm btn-icon btn-active-light-primary me-1" type="button"
                            data-bs-toggle="tooltip" title="Coming soon">
                        <i class="ki-duotone ki-cloud-add fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </button>
                </div>
                <!--end::Actions-->
                <!--begin::Send-->
                <button id="sendMessage" class="btn btn-primary" type="button" data-kt-element="send">Send</button>
                <!--end::Send-->
            </div>
            <!--end::Toolbar-->
        </div>
        <!--end::Card footer-->
    </div>
    <!--end::Messenger-->
</div>
<!--end::Chat drawer-->

@push('scripts')
    {{-- Load VenoBox JS and initialize --}}
    <script src="{{ asset('assets/plugins/VenoBox/venobox.min.js') }}"></script>
    <script>
        let ticketId = '';
        let userName = '';
        let conversationId = '';
        const chatWindow = $('#chatWindow');
        const messageInput = $('#messageInput');
        const conversationName = $('#conversationName');

        // Function to generate an "out" message HTML
        function generateOutMessage({ time, avatarSrc, userName, messageText }) {
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
        function generateInMessage({ time, avatarSrc, userName, messageText, mediaItems }) {
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
        let lastFetchedMessageId = null;
        // Function to fetch messages for the conversation
        const fetchMessages = (id, stopScroll = false) => {
            if (!id) id = conversationId;

            if (!$('#kt_drawer_chat').hasClass('drawer-on')) {
                console.log('Chat drawer is closed. Stopping message fetch.');
                return;
            }


            $.get(`/chat/messages/${id}`, function (reponse) {
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

            $.post('/chat/send', { message, conversation_id: conversationId }, function () {
                // On success, remove "Sending..." and finalize the message
                // $(`#${tempId} .sending-status`).remove();
                $(`#${tempId} .sending-status`).text('sent');
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
            ticketId = $(this).data('ticket-id');
            chatWindow.html('');
            fetchTicketAndConversation(ticketId);
            $('#kt_drawer_chat_toggle').click();
        });
        document.addEventListener("DOMContentLoaded", function() {
            new VenoBox({
                selector: '.my-image-links',
                numeration: true,
                infinigall: true,
                share: true,
                spinner: 'rotating-plane'
            });
        });
    </script>
@endpush
