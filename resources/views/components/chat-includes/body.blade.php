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
