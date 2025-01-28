<!--begin::Sidebar-->
<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar"
     data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px"
     data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <!--begin::Logo-->
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <!--begin::Logo image-->
        <a href="../../demo1/dist/index.html">
            <img alt="Logo" src="assets/media/logos/default-dark.svg" class="h-25px app-sidebar-logo-default"/>
            <img alt="Logo" src="assets/media/logos/default-small.svg" class="h-20px app-sidebar-logo-minimize"/>
        </a>
        <!--end::Logo image-->
        <!--begin::Sidebar toggle-->
        <!--begin::Minimized sidebar setup:
if (isset($_COOKIE["sidebar_minimize_state"]) && $_COOKIE["sidebar_minimize_state"] === "on") {
1. "src/js/layout/sidebar.js" adds "sidebar_minimize_state" cookie value to save the sidebar minimize state.
2. Set data-kt-app-sidebar-minimize="on" attribute for body tag.
3. Set data-kt-toggle-state="active" attribute to the toggle element with "kt_app_sidebar_toggle" id.
4. Add "active" class to to sidebar toggle element with "kt_app_sidebar_toggle" id.
}
-->
        <div id="kt_app_sidebar_toggle"
             class="app-sidebar-toggle btn btn-icon btn-shadow btn-sm btn-color-muted btn-active-color-primary h-30px w-30px position-absolute top-50 start-100 translate-middle rotate"
             data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body"
             data-kt-toggle-name="app-sidebar-minimize">
            <i class="ki-duotone ki-black-left-line fs-3 rotate-180">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </div>
        <!--end::Sidebar toggle-->
    </div>
    <!--end::Logo-->
    <!--begin::sidebar menu-->
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <!--begin::Menu wrapper-->
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
            <!--begin::Scroll wrapper-->
            <div id="kt_app_sidebar_menu_scroll" class="scroll-y my-5 mx-3" data-kt-scroll="true"
                 data-kt-scroll-activate="true" data-kt-scroll-height="auto"
                 data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
                 data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px"
                 data-kt-scroll-save-state="true">
                <!--begin::Menu-->
                <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="#kt_app_sidebar_menu"
                     data-kt-menu="true" data-kt-menu-expand="false">
                    <!--begin:Menu item-->
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                        <!--begin:Menu link-->
                        <span class="menu-link">
												<span class="menu-icon">
													<i class="ki-duotone ki-element-11 fs-2">
														<span class="path1"></span>
														<span class="path2"></span>
														<span class="path3"></span>
														<span class="path4"></span>
													</i>
												</span>
												<span class="menu-title">Dashboards</span>
												<span class="menu-arrow"></span>
											</span>
                        <!--end:Menu link-->
                        <!--begin:Menu sub-->
                        <div class="menu-sub menu-sub-accordion">
                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <!--begin:Menu link-->
                                <a class="menu-link" href="../../demo1/dist/index.html">
														<span class="menu-bullet">
															<span class="bullet bullet-dot"></span>
														</span>
                                    <span class="menu-title">Default</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->
                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <!--begin:Menu link-->
                                <a class="menu-link" href="../../demo1/dist/dashboards/ecommerce.html">
														<span class="menu-bullet">
															<span class="bullet bullet-dot"></span>
														</span>
                                    <span class="menu-title">eCommerce</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->
                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <!--begin:Menu link-->
                                <a class="menu-link" href="../../demo1/dist/dashboards/projects.html">
														<span class="menu-bullet">
															<span class="bullet bullet-dot"></span>
														</span>
                                    <span class="menu-title">Projects</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->
                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <!--begin:Menu link-->
                                <a class="menu-link" href="../../demo1/dist/dashboards/online-courses.html">
														<span class="menu-bullet">
															<span class="bullet bullet-dot"></span>
														</span>
                                    <span class="menu-title">Online Courses</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->
                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <!--begin:Menu link-->
                                <a class="menu-link" href="../../demo1/dist/dashboards/marketing.html">
														<span class="menu-bullet">
															<span class="bullet bullet-dot"></span>
														</span>
                                    <span class="menu-title">Marketing</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->
                            <div class="menu-inner flex-column collapse" id="kt_app_sidebar_menu_dashboards_collapse">
                                <!--begin:Menu item-->
                                <div class="menu-item">
                                    <!--begin:Menu link-->
                                    <a class="menu-link" href="../../demo1/dist/dashboards/bidding.html">
															<span class="menu-bullet">
																<span class="bullet bullet-dot"></span>
															</span>
                                        <span class="menu-title">Bidding</span>
                                    </a>
                                    <!--end:Menu link-->
                                </div>
                                <!--end:Menu item-->
                                <!--begin:Menu item-->
                                <div class="menu-item">
                                    <!--begin:Menu link-->
                                    <a class="menu-link" href="../../demo1/dist/dashboards/pos.html">
															<span class="menu-bullet">
																<span class="bullet bullet-dot"></span>
															</span>
                                        <span class="menu-title">POS System</span>
                                    </a>
                                    <!--end:Menu link-->
                                </div>
                                <!--end:Menu item-->
                                <!--begin:Menu item-->
                                <div class="menu-item">
                                    <!--begin:Menu link-->
                                    <a class="menu-link" href="../../demo1/dist/dashboards/call-center.html">
															<span class="menu-bullet">
																<span class="bullet bullet-dot"></span>
															</span>
                                        <span class="menu-title">Call Center</span>
                                    </a>
                                    <!--end:Menu link-->
                                </div>
                                <!--end:Menu item-->
                                <!--begin:Menu item-->
                                <div class="menu-item">
                                    <!--begin:Menu link-->
                                    <a class="menu-link" href="../../demo1/dist/dashboards/logistics.html">
															<span class="menu-bullet">
																<span class="bullet bullet-dot"></span>
															</span>
                                        <span class="menu-title">Logistics</span>
                                    </a>
                                    <!--end:Menu link-->
                                </div>
                                <!--end:Menu item-->
                                <!--begin:Menu item-->
                                <div class="menu-item">
                                    <!--begin:Menu link-->
                                    <a class="menu-link" href="../../demo1/dist/dashboards/website-analytics.html">
															<span class="menu-bullet">
																<span class="bullet bullet-dot"></span>
															</span>
                                        <span class="menu-title">Website Analytics</span>
                                    </a>
                                    <!--end:Menu link-->
                                </div>
                                <!--end:Menu item-->
                                <!--begin:Menu item-->
                                <div class="menu-item">
                                    <!--begin:Menu link-->
                                    <a class="menu-link" href="../../demo1/dist/dashboards/finance-performance.html">
															<span class="menu-bullet">
																<span class="bullet bullet-dot"></span>
															</span>
                                        <span class="menu-title">Finance Performance</span>
                                    </a>
                                    <!--end:Menu link-->
                                </div>
                                <!--end:Menu item-->
                                <!--begin:Menu item-->
                                <div class="menu-item">
                                    <!--begin:Menu link-->
                                    <a class="menu-link" href="../../demo1/dist/dashboards/store-analytics.html">
															<span class="menu-bullet">
																<span class="bullet bullet-dot"></span>
															</span>
                                        <span class="menu-title">Store Analytics</span>
                                    </a>
                                    <!--end:Menu link-->
                                </div>
                                <!--end:Menu item-->
                                <!--begin:Menu item-->
                                <div class="menu-item">
                                    <!--begin:Menu link-->
                                    <a class="menu-link" href="../../demo1/dist/dashboards/social.html">
															<span class="menu-bullet">
																<span class="bullet bullet-dot"></span>
															</span>
                                        <span class="menu-title">Social</span>
                                    </a>
                                    <!--end:Menu link-->
                                </div>
                                <!--end:Menu item-->
                                <!--begin:Menu item-->
                                <div class="menu-item">
                                    <!--begin:Menu link-->
                                    <a class="menu-link" href="../../demo1/dist/dashboards/delivery.html">
															<span class="menu-bullet">
																<span class="bullet bullet-dot"></span>
															</span>
                                        <span class="menu-title">Delivery</span>
                                    </a>
                                    <!--end:Menu link-->
                                </div>
                                <!--end:Menu item-->
                                <!--begin:Menu item-->
                                <div class="menu-item">
                                    <!--begin:Menu link-->
                                    <a class="menu-link" href="../../demo1/dist/dashboards/crypto.html">
															<span class="menu-bullet">
																<span class="bullet bullet-dot"></span>
															</span>
                                        <span class="menu-title">Crypto</span>
                                    </a>
                                    <!--end:Menu link-->
                                </div>
                                <!--end:Menu item-->
                                <!--begin:Menu item-->
                                <div class="menu-item">
                                    <!--begin:Menu link-->
                                    <a class="menu-link" href="../../demo1/dist/dashboards/school.html">
															<span class="menu-bullet">
																<span class="bullet bullet-dot"></span>
															</span>
                                        <span class="menu-title">School</span>
                                    </a>
                                    <!--end:Menu link-->
                                </div>
                                <!--end:Menu item-->
                                <!--begin:Menu item-->
                                <div class="menu-item">
                                    <!--begin:Menu link-->
                                    <a class="menu-link" href="../../demo1/dist/dashboards/podcast.html">
															<span class="menu-bullet">
																<span class="bullet bullet-dot"></span>
															</span>
                                        <span class="menu-title">Podcast</span>
                                    </a>
                                    <!--end:Menu link-->
                                </div>
                                <!--end:Menu item-->
                                <!--begin:Menu item-->
                                <div class="menu-item">
                                    <!--begin:Menu link-->
                                    <a class="menu-link" href="../../demo1/dist/landing.html">
															<span class="menu-bullet">
																<span class="bullet bullet-dot"></span>
															</span>
                                        <span class="menu-title">Landing</span>
                                    </a>
                                    <!--end:Menu link-->
                                </div>
                                <!--end:Menu item-->
                            </div>
                            <div class="menu-item">
                                <div class="menu-content">
                                    <a class="btn btn-flex btn-color-primary d-flex flex-stack fs-base p-0 ms-2 mb-2 toggle collapsible collapsed"
                                       data-bs-toggle="collapse" href="#kt_app_sidebar_menu_dashboards_collapse"
                                       data-kt-toggle-text="Show Less">
                                        <span data-kt-toggle-text-target="true">Show 12 More</span>
                                        <i class="ki-duotone ki-minus-square toggle-on fs-2 me-0">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <i class="ki-duotone ki-plus-square toggle-off fs-2 me-0">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!--end:Menu sub-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
                    <div class="menu-item pt-5">
                        <!--begin:Menu content-->
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">Pages</span>
                        </div>
                        <!--end:Menu content-->
                    </div>
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
{{--                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">--}}
{{--                        <!--begin:Menu link-->--}}
{{--                        <span class="menu-link">--}}
{{--												<span class="menu-icon">--}}
{{--													<i class="ki-duotone ki-address-book fs-2">--}}
{{--														<span class="path1"></span>--}}
{{--														<span class="path2"></span>--}}
{{--														<span class="path3"></span>--}}
{{--													</i>--}}
{{--												</span>--}}
{{--												<span class="menu-title">User Profile</span>--}}
{{--												<span class="menu-arrow"></span>--}}
{{--											</span>--}}
{{--                        <!--end:Menu link-->--}}
{{--                        <!--begin:Menu sub-->--}}
{{--                        <div class="menu-sub menu-sub-accordion">--}}
{{--                            <!--begin:Menu item-->--}}
{{--                            <div class="menu-item">--}}
{{--                                <!--begin:Menu link-->--}}
{{--                                <a class="menu-link" href="../../demo1/dist/pages/user-profile/overview.html">--}}
{{--														<span class="menu-bullet">--}}
{{--															<span class="bullet bullet-dot"></span>--}}
{{--														</span>--}}
{{--                                    <span class="menu-title">Overview</span>--}}
{{--                                </a>--}}
{{--                                <!--end:Menu link-->--}}
{{--                            </div>--}}
{{--                            <!--end:Menu item-->--}}
{{--                            <!--begin:Menu item-->--}}
{{--                            <div class="menu-item">--}}
{{--                                <!--begin:Menu link-->--}}
{{--                                <a class="menu-link" href="../../demo1/dist/pages/user-profile/projects.html">--}}
{{--														<span class="menu-bullet">--}}
{{--															<span class="bullet bullet-dot"></span>--}}
{{--														</span>--}}
{{--                                    <span class="menu-title">Projects</span>--}}
{{--                                </a>--}}
{{--                                <!--end:Menu link-->--}}
{{--                            </div>--}}
{{--                            <!--end:Menu item-->--}}
{{--                            <!--begin:Menu item-->--}}
{{--                            <div class="menu-item">--}}
{{--                                <!--begin:Menu link-->--}}
{{--                                <a class="menu-link" href="../../demo1/dist/pages/user-profile/campaigns.html">--}}
{{--														<span class="menu-bullet">--}}
{{--															<span class="bullet bullet-dot"></span>--}}
{{--														</span>--}}
{{--                                    <span class="menu-title">Campaigns</span>--}}
{{--                                </a>--}}
{{--                                <!--end:Menu link-->--}}
{{--                            </div>--}}
{{--                            <!--end:Menu item-->--}}
{{--                            <!--begin:Menu item-->--}}
{{--                            <div class="menu-item">--}}
{{--                                <!--begin:Menu link-->--}}
{{--                                <a class="menu-link" href="../../demo1/dist/pages/user-profile/documents.html">--}}
{{--														<span class="menu-bullet">--}}
{{--															<span class="bullet bullet-dot"></span>--}}
{{--														</span>--}}
{{--                                    <span class="menu-title">Documents</span>--}}
{{--                                </a>--}}
{{--                                <!--end:Menu link-->--}}
{{--                            </div>--}}
{{--                            <!--end:Menu item-->--}}
{{--                            <!--begin:Menu item-->--}}
{{--                            <div class="menu-item">--}}
{{--                                <!--begin:Menu link-->--}}
{{--                                <a class="menu-link" href="../../demo1/dist/pages/user-profile/followers.html">--}}
{{--														<span class="menu-bullet">--}}
{{--															<span class="bullet bullet-dot"></span>--}}
{{--														</span>--}}
{{--                                    <span class="menu-title">Followers</span>--}}
{{--                                </a>--}}
{{--                                <!--end:Menu link-->--}}
{{--                            </div>--}}
{{--                            <!--end:Menu item-->--}}
{{--                            <!--begin:Menu item-->--}}
{{--                            <div class="menu-item">--}}
{{--                                <!--begin:Menu link-->--}}
{{--                                <a class="menu-link" href="../../demo1/dist/pages/user-profile/activity.html">--}}
{{--														<span class="menu-bullet">--}}
{{--															<span class="bullet bullet-dot"></span>--}}
{{--														</span>--}}
{{--                                    <span class="menu-title">Activity</span>--}}
{{--                                </a>--}}
{{--                                <!--end:Menu link-->--}}
{{--                            </div>--}}
{{--                            <!--end:Menu item-->--}}
{{--                        </div>--}}
{{--                        <!--end:Menu sub-->--}}
{{--                    </div>--}}
                    <!--end:Menu item-->
                    @php
                        $menuItems = [
//                            [
//                                'route' => 'area.index',
//                                'title' => 'Areas',
//                                'icon' => 'ki-calendar-8'
//                            ],
                            [
                                'route' => 'service.index',
                                'title' => 'services',
                                'icon' => 'ki-wrench'
                            ],
                             [
                                'route' => 'sub-service.index',
                                'title' => 'sub services',
                                'icon' => 'ki-wrench'
                            ],
                             [
                                'route' => 'space_sub_service.index',
                                'title' => 'spaces',
                                'icon' => 'ki-design-frame'
                            ],
                            [
                                'route' => 'discount-code.index',
                                'title' => 'discount codes',
                                'icon' => 'ki-discount'
                            ],
                            [
                                'route' => 'user.index',
                                'title' => 'users',
                                'icon' => 'ki-user'
                            ],
                            [
                                'route' => 'review.index',
                                'title' => 'rates',
                                'icon' => 'ki-star'
                            ],
                            [
                                'route' => 'provider.index',
                                'title' => 'providers',
                                'icon' => 'ki-user'
                            ],
                            [
                                'route' => 'order.index',
                                'title' => 'orders',
                                'icon' => 'ki-parcel-tracking '
                            ],
                            [
                                'route' => 'invoice.index',
                                'title' => 'invoices',
                                'icon' => 'ki-parcel-tracking '
                            ],
                            [
                                'route' => 'payment.index',
                                'title' => 'payments',
                                'icon' => 'ki-parcel-tracking '
                            ],
                            [
                                'route' => 'ticket.index',
                                'title' => 'tickets',
                                'icon' => 'ki-messages'
                            ],
                            [
                                'route' => 'warranty.index',
                                'title' => 'warranties',
                                'icon' => 'ki-medal-star'
                            ],
                            [
                                'route' => 'level.index',
                                'title' => 'levels',
                                'icon' => 'ki-double-up'
                            ],
                            [
                                'route' => 'wallet.index',
                                'title' => 'wallets',
                                'icon' => 'ki-save-deposit'
                            ],
                             [
                                'route' => 'transaction.index',
                                'title' => 'transaction',
                                'icon' => 'ki-bill'
                            ],
                            [
                                'route' => 'setting.index',
                                'title' => 'setting',
                                'icon' => 'ki-setting-3'
                            ],
                            // Add more items as needed
                        ];
                    @endphp

                    @foreach ($menuItems as $item)
                        <!--begin:Menu item-->
                        <div class="menu-item">
                            <a class="menu-link {{ Route::is($item['route']) ? 'active' : '' }}"
                               href="{{ route($item['route']) }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone {{ $item['icon'] }} fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                        <span class="path5"></span>
                                        <span class="path6"></span>
                                    </i>
                                </span>
                                <span class="menu-title text-capitalize">{{ $item['title'] }}</span>
                            </a>
                        </div>
                        <!--end:Menu item-->
                    @endforeach

                    <!--begin:Menu item-->
                    {{--                    <div class="menu-item">--}}
                    {{--                        <!--begin:Menu link-->--}}
                    {{--                        <a class="menu-link {{ Route::is('area.index') ? 'active' : '' }}"--}}
                    {{--                           href="{{route('area.index')}}">--}}
                    {{--                            <span class="menu-icon">--}}
                    {{--                                <i class="ki-duotone ki-calendar-8 fs-2">--}}
                    {{--                                    <span class="path1"></span>--}}
                    {{--                                    <span class="path2"></span>--}}
                    {{--                                    <span class="path3"></span>--}}
                    {{--                                    <span class="path4"></span>--}}
                    {{--                                    <span class="path5"></span>--}}
                    {{--                                    <span class="path6"></span>--}}
                    {{--                                </i>--}}
                    {{--                            </span>--}}
                    {{--                            <span class="menu-title">Ares</span>--}}
                    {{--                        </a>--}}
                    {{--                        <!--end:Menu link-->--}}
                    {{--                    </div>--}}
                    <!--end:Menu item-->
                    <!--begin:Menu item-->
{{--                    <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="right-start"--}}
{{--                         class="menu-item menu-lg-down-accordion menu-sub-lg-down-indention">--}}
{{--                        <!--begin:Menu link-->--}}
{{--                        <span class="menu-link">--}}
{{--												<span class="menu-icon">--}}
{{--													<i class="ki-duotone ki-file fs-2">--}}
{{--														<span class="path1"></span>--}}
{{--														<span class="path2"></span>--}}
{{--													</i>--}}
{{--												</span>--}}
{{--												<span class="menu-title">Corporate</span>--}}
{{--												<span class="menu-arrow"></span>--}}
{{--											</span>--}}
{{--                        <!--end:Menu link-->--}}
{{--                        <!--begin:Menu sub-->--}}
{{--                        <div--}}
{{--                            class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown px-2 py-4 w-200px mh-75 overflow-auto">--}}
{{--                            <!--begin:Menu item-->--}}
{{--                            <div class="menu-item">--}}
{{--                                <!--begin:Menu link-->--}}
{{--                                <a class="menu-link" href="../../demo1/dist/pages/about.html">--}}
{{--														<span class="menu-bullet">--}}
{{--															<span class="bullet bullet-dot"></span>--}}
{{--														</span>--}}
{{--                                    <span class="menu-title">About</span>--}}
{{--                                </a>--}}
{{--                                <!--end:Menu link-->--}}
{{--                            </div>--}}
{{--                            <!--end:Menu item-->--}}
{{--                            <!--begin:Menu item-->--}}
{{--                            <div class="menu-item">--}}
{{--                                <!--begin:Menu link-->--}}
{{--                                <a class="menu-link" href="../../demo1/dist/pages/team.html">--}}
{{--														<span class="menu-bullet">--}}
{{--															<span class="bullet bullet-dot"></span>--}}
{{--														</span>--}}
{{--                                    <span class="menu-title">Our Team</span>--}}
{{--                                </a>--}}
{{--                                <!--end:Menu link-->--}}
{{--                            </div>--}}
{{--                            <!--end:Menu item-->--}}
{{--                            <!--begin:Menu item-->--}}
{{--                            <div class="menu-item">--}}
{{--                                <!--begin:Menu link-->--}}
{{--                                <a class="menu-link" href="../../demo1/dist/pages/contact.html">--}}
{{--														<span class="menu-bullet">--}}
{{--															<span class="bullet bullet-dot"></span>--}}
{{--														</span>--}}
{{--                                    <span class="menu-title">Contact Us</span>--}}
{{--                                </a>--}}
{{--                                <!--end:Menu link-->--}}
{{--                            </div>--}}
{{--                            <!--end:Menu item-->--}}
{{--                            <!--begin:Menu item-->--}}
{{--                            <div class="menu-item">--}}
{{--                                <!--begin:Menu link-->--}}
{{--                                <a class="menu-link" href="../../demo1/dist/pages/licenses.html">--}}
{{--														<span class="menu-bullet">--}}
{{--															<span class="bullet bullet-dot"></span>--}}
{{--														</span>--}}
{{--                                    <span class="menu-title">Licenses</span>--}}
{{--                                </a>--}}
{{--                                <!--end:Menu link-->--}}
{{--                            </div>--}}
{{--                            <!--end:Menu item-->--}}
{{--                            <!--begin:Menu item-->--}}
{{--                            <div class="menu-item">--}}
{{--                                <!--begin:Menu link-->--}}
{{--                                <a class="menu-link" href="../../demo1/dist/pages/sitemap.html">--}}
{{--														<span class="menu-bullet">--}}
{{--															<span class="bullet bullet-dot"></span>--}}
{{--														</span>--}}
{{--                                    <span class="menu-title">Sitemap</span>--}}
{{--                                </a>--}}
{{--                                <!--end:Menu link-->--}}
{{--                            </div>--}}
{{--                            <!--end:Menu item-->--}}
{{--                        </div>--}}
{{--                        <!--end:Menu sub-->--}}
{{--                    </div>--}}
{{--                    <!--end:Menu item-->--}}
{{--                    <!--begin:Menu item-->--}}
{{--                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">--}}
{{--                        <!--begin:Menu link-->--}}
{{--                        <span class="menu-link">--}}
{{--                            <span class="menu-icon">--}}
{{--                                <i class="ki-duotone ki-abstract-39 fs-2">--}}
{{--                                --}}{{--                                    <span class="path1"></span>--}}
{{--                                --}}{{--                                    <span class="path2"></span>--}}
{{--                                --}}{{--                                </i>--}}
{{--                                <i class="fa-solid fa-boxes-stacked fs-2"></i>--}}
{{--                            </span>--}}
{{--                            <span class="menu-title">@lang('words.stock')</span>--}}
{{--                            <span class="menu-arrow"></span>--}}
{{--                        </span>--}}
{{--                        <!--end:Menu link-->--}}
{{--                        <!--begin:Menu sub-->--}}
{{--                        <div class="menu-sub menu-sub-accordion">--}}
{{--                            <!--begin:Menu item-->--}}
{{--                            <div class="menu-item">--}}
{{--                                <!--begin:Menu link-->--}}
{{--                                <a class="menu-link" href="{{route('category.index')}}">--}}
{{--                                    <span class="menu-bullet">--}}
{{--                                        <span class="bullet bullet-dot"></span>--}}
{{--                                    </span>--}}
{{--                                    <span class="menu-title text-capitalize">@lang('words.categories')</span>--}}
{{--                                </a>--}}
{{--                                <!--end:Menu link-->--}}
{{--                            </div>--}}
{{--                            <!--end:Menu item-->--}}
{{--                            <!--begin:Menu item-->--}}
{{--                            <div class="menu-item">--}}
{{--                                <!--begin:Menu link-->--}}
{{--                                <a class="menu-link" href="{{route('section.index')}}">--}}
{{--                                    <span class="menu-bullet">--}}
{{--                                        <span class="bullet bullet-dot"></span>--}}
{{--                                    </span>--}}
{{--                                    <span class="menu-title text-capitalize">@lang('words.sections')</span>--}}
{{--                                </a>--}}
{{--                                <!--end:Menu link-->--}}
{{--                            </div>--}}
{{--                            <!--end:Menu item-->--}}
{{--                            <!--begin:Menu item-->--}}
{{--                            <div class="menu-item">--}}
{{--                                <!--begin:Menu link-->--}}
{{--                                <a class="menu-link" href="{{route('company.index')}}">--}}
{{--                                    <span class="menu-bullet">--}}
{{--                                        <span class="bullet bullet-dot"></span>--}}
{{--                                    </span>--}}
{{--                                    <span class="menu-title text-capitalize">@lang('words.companies')</span>--}}
{{--                                </a>--}}
{{--                                <!--end:Menu link-->--}}
{{--                            </div>--}}
{{--                            <!--end:Menu item-->--}}
{{--                            <!--begin:Menu item-->--}}
{{--                            <div class="menu-item">--}}
{{--                                <!--begin:Menu link-->--}}
{{--                                <a class="menu-link" href="{{route('product.index')}}">--}}
{{--                                    <span class="menu-bullet">--}}
{{--                                        <span class="bullet bullet-dot"></span>--}}
{{--                                    </span>--}}
{{--                                    <span class="menu-title text-capitalize">@lang('words.products')</span>--}}
{{--                                </a>--}}
{{--                                <!--end:Menu link-->--}}
{{--                            </div>--}}
{{--                            <!--end:Menu item-->--}}
{{--                        </div>--}}
{{--                        <!--end:Menu sub-->--}}
{{--                    </div>--}}
{{--                    <!--end:Menu item-->--}}
{{--                    <!--begin:Menu item-->--}}
{{--                    <div class="menu-item pt-5">--}}
{{--                        <!--begin:Menu content-->--}}
{{--                        <div class="menu-content">--}}
{{--                            <span class="menu-heading fw-bold text-uppercase fs-7">Apps</span>--}}
{{--                        </div>--}}
{{--                        <!--end:Menu content-->--}}
{{--                    </div>--}}
{{--                    <!--end:Menu item-->--}}
{{--                    <!--begin:Menu item-->--}}
{{--                    <div data-kt-menu-trigger="click" class="menu-item here show menu-accordion">--}}
{{--                        <!--begin:Menu link-->--}}
{{--                        <span class="menu-link">--}}
{{--												<span class="menu-icon">--}}
{{--													<i class="ki-duotone ki-abstract-28 fs-2">--}}
{{--														<span class="path1"></span>--}}
{{--														<span class="path2"></span>--}}
{{--													</i>--}}
{{--												</span>--}}
{{--												<span class="menu-title">User Management</span>--}}
{{--												<span class="menu-arrow"></span>--}}
{{--											</span>--}}
{{--                        <!--end:Menu link-->--}}
{{--                        <!--begin:Menu sub-->--}}
{{--                        <div class="menu-sub menu-sub-accordion">--}}
{{--                            <!--begin:Menu item-->--}}
{{--                            <div data-kt-menu-trigger="click" class="menu-item here show menu-accordion mb-1">--}}
{{--                                <!--begin:Menu link-->--}}
{{--                                <span class="menu-link">--}}
{{--														<span class="menu-bullet">--}}
{{--															<span class="bullet bullet-dot"></span>--}}
{{--														</span>--}}
{{--														<span class="menu-title">Users</span>--}}
{{--														<span class="menu-arrow"></span>--}}
{{--													</span>--}}
{{--                                <!--end:Menu link-->--}}
{{--                                <!--begin:Menu sub-->--}}
{{--                                <div class="menu-sub menu-sub-accordion">--}}
{{--                                    <!--begin:Menu item-->--}}
{{--                                    <div class="menu-item">--}}
{{--                                        <!--begin:Menu link-->--}}
{{--                                        <a class="menu-link"--}}
{{--                                           href="../../demo1/dist/apps/user-management/users/list.html">--}}
{{--																<span class="menu-bullet">--}}
{{--																	<span class="bullet bullet-dot"></span>--}}
{{--																</span>--}}
{{--                                            <span class="menu-title">Users List</span>--}}
{{--                                        </a>--}}
{{--                                        <!--end:Menu link-->--}}
{{--                                    </div>--}}
{{--                                    <!--end:Menu item-->--}}
{{--                                    <!--begin:Menu item-->--}}
{{--                                    <div class="menu-item">--}}
{{--                                        <!--begin:Menu link-->--}}
{{--                                        <a class="menu-link"--}}
{{--                                           href="../../demo1/dist/apps/user-management/users/view.html">--}}
{{--																<span class="menu-bullet">--}}
{{--																	<span class="bullet bullet-dot"></span>--}}
{{--																</span>--}}
{{--                                            <span class="menu-title">View User</span>--}}
{{--                                        </a>--}}
{{--                                        <!--end:Menu link-->--}}
{{--                                    </div>--}}
{{--                                    <!--end:Menu item-->--}}
{{--                                </div>--}}
{{--                                <!--end:Menu sub-->--}}
{{--                            </div>--}}
{{--                            <!--end:Menu item-->--}}
{{--                            <!--begin:Menu item-->--}}
{{--                            <div data-kt-menu-trigger="click" class="menu-item menu-accordion">--}}
{{--                                <!--begin:Menu link-->--}}
{{--                                <span class="menu-link">--}}
{{--														<span class="menu-bullet">--}}
{{--															<span class="bullet bullet-dot"></span>--}}
{{--														</span>--}}
{{--														<span class="menu-title">Roles</span>--}}
{{--														<span class="menu-arrow"></span>--}}
{{--													</span>--}}
{{--                                <!--end:Menu link-->--}}
{{--                                <!--begin:Menu sub-->--}}
{{--                                <div class="menu-sub menu-sub-accordion">--}}
{{--                                    <!--begin:Menu item-->--}}
{{--                                    <div class="menu-item">--}}
{{--                                        <!--begin:Menu link-->--}}
{{--                                        <a class="menu-link"--}}
{{--                                           href="../../demo1/dist/apps/user-management/roles/list.html">--}}
{{--																<span class="menu-bullet">--}}
{{--																	<span class="bullet bullet-dot"></span>--}}
{{--																</span>--}}
{{--                                            <span class="menu-title">Roles List</span>--}}
{{--                                        </a>--}}
{{--                                        <!--end:Menu link-->--}}
{{--                                    </div>--}}
{{--                                    <!--end:Menu item-->--}}
{{--                                    <!--begin:Menu item-->--}}
{{--                                    <div class="menu-item">--}}
{{--                                        <!--begin:Menu link-->--}}
{{--                                        <a class="menu-link"--}}
{{--                                           href="../../demo1/dist/apps/user-management/roles/view.html">--}}
{{--																<span class="menu-bullet">--}}
{{--																	<span class="bullet bullet-dot"></span>--}}
{{--																</span>--}}
{{--                                            <span class="menu-title">View Role</span>--}}
{{--                                        </a>--}}
{{--                                        <!--end:Menu link-->--}}
{{--                                    </div>--}}
{{--                                    <!--end:Menu item-->--}}
{{--                                </div>--}}
{{--                                <!--end:Menu sub-->--}}
{{--                            </div>--}}
{{--                            <!--end:Menu item-->--}}
{{--                            <!--begin:Menu item-->--}}
{{--                            <div class="menu-item">--}}
{{--                                <!--begin:Menu link-->--}}
{{--                                <a class="menu-link" href="../../demo1/dist/apps/user-management/permissions.html">--}}
{{--														<span class="menu-bullet">--}}
{{--															<span class="bullet bullet-dot"></span>--}}
{{--														</span>--}}
{{--                                    <span class="menu-title">Permissions</span>--}}
{{--                                </a>--}}
{{--                                <!--end:Menu link-->--}}
{{--                            </div>--}}
{{--                            <!--end:Menu item-->--}}
{{--                        </div>--}}
{{--                        <!--end:Menu sub-->--}}
{{--                    </div>--}}
{{--                    <!--end:Menu item-->--}}
                </div>
                <!--end::Menu-->
            </div>
            <!--end::Scroll wrapper-->
        </div>
        <!--end::Menu wrapper-->
    </div>
    <!--end::sidebar menu-->
    <!--begin::Footer-->
    <div class="app-sidebar-footer flex-column-auto pt-2 pb-6 px-6" id="kt_app_sidebar_footer">
        <a href="#"
           class="btn btn-flex flex-center btn-custom btn-primary overflow-hidden text-nowrap px-0 h-40px w-100"
           data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-dismiss-="click"
           title="200+ in-house components and 3rd-party plugins">
{{--            <span class="btn-label">Docs & Components</span>--}}
            <i class="ki-duotone ki-document btn-icon fs-2 m-0">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </a>
    </div>
    <!--end::Footer-->
</div>
<!--end::Sidebar-->
