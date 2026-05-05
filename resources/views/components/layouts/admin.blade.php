@props(['title' => config('app.name', 'Kumachi')])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title }}</title>
        <link rel="stylesheet" href="{{ asset('css/layouts/admin.css') }}">
        @stack('styles')
    </head>
    <body>
        <div class="layout" data-layout-root>
            <aside class="sidebar" id="admin-sidebar">
                <a class="brand" href="{{ route('dashboard') }}">
                    <span class="brand-mark" aria-hidden="true">
                        <img src="{{ asset('media/kumachi-logo.png') }}" alt="{{ config('app.name', 'Kumachi') }} logo">
                    </span>
                    <span class="brand-copy">
                        <strong>{{ config('app.name', 'Kumachi') }}</strong>
                        <small>Admin Panel</small>
                    </span>
                </a>

                <nav class="sidebar-nav" aria-label="Admin navigation">
                    <a class="{{ request()->routeIs('dashboard') || request()->routeIs('admin.dashboard') ? 'current' : '' }}" href="{{ route('dashboard') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="4" y="4" width="7" height="7"></rect>
                            <rect x="13" y="4" width="7" height="4"></rect>
                            <rect x="13" y="10" width="7" height="10"></rect>
                            <rect x="4" y="13" width="7" height="7"></rect>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                    <a class="{{ request()->routeIs('admin.products.*') ? 'current' : '' }}" href="{{ route('admin.products.index') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M3 7h9v11H3z"></path>
                            <path d="M12 9h9v9h-9z"></path>
                            <path d="M7.5 4 16 8"></path>
                        </svg>
                        <span>Products</span>
                    </a>
                    <a class="{{ request()->routeIs('admin.categories.*') ? 'current' : '' }}" href="{{ route('admin.categories.index') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M5 7h14"></path>
                            <path d="M5 12h14"></path>
                            <path d="M5 17h14"></path>
                        </svg>
                        <span>Categories</span>
                    </a>
                    <a class="{{ request()->routeIs('admin.addons.*') ? 'current' : '' }}" href="{{ route('admin.addons.index') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M5 7h14"></path>
                            <path d="M12 5v14"></path>
                            <path d="M7 12h10"></path>
                        </svg>
                        <span>Add-ons</span>
                    </a>
                    <a class="{{ request()->routeIs('admin.orders.*') ? 'current' : '' }}" href="{{ route('admin.orders.index') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="5" y="4" width="14" height="16" rx="2"></rect>
                            <path d="M9 9h6"></path>
                        </svg>
                        <span>Orders</span>
                    </a>
                    <a class="{{ request()->routeIs('admin.users.*') ? 'current' : '' }}" href="{{ route('admin.users.index') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="9" cy="8" r="2.5"></circle>
                            <circle cx="16" cy="9" r="2"></circle>
                            <path d="M5 18a4 4 0 0 1 8 0"></path>
                            <path d="M13 18a3 3 0 0 1 6 0"></path>
                        </svg>
                        <span>Users</span>
                    </a>
                    <a class="{{ request()->routeIs('admin.promotions.*') ? 'current' : '' }}" href="{{ route('admin.promotions.index') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="m5 8 6-4 8 8-6 6-8-8z"></path>
                            <circle cx="8.5" cy="8.5" r="1"></circle>
                        </svg>
                        <span>Promotions</span>
                    </a>
                    <a class="{{ request()->routeIs('admin.reports.*') ? 'current' : '' }}" href="{{ route('admin.reports.index') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M5 19V8"></path>
                            <path d="M10 19V5"></path>
                            <path d="M15 19v-7"></path>
                            <path d="M20 19V9"></path>
                            <path d="M4 19h17"></path>
                        </svg>
                        <span>Reports</span>
                    </a>
                    <a class="{{ request()->routeIs('admin.inventories.*') ? 'current' : '' }}" href="{{ route('admin.inventories.index') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M5 20h14"></path>
                            <path d="M6 20V8l6-4 6 4v12"></path>
                            <path d="M9 12h6"></path>
                        </svg>
                        <span>Inventory</span>
                    </a>
                    <a class="{{ request()->routeIs('admin.suppliers.*') ? 'current' : '' }}" href="{{ route('admin.suppliers.index') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M3 12h6v8H3z"></path>
                            <path d="M9 15h5l3 2.5h4"></path>
                            <circle cx="7" cy="20" r="1"></circle>
                            <circle cx="18" cy="20" r="1"></circle>
                        </svg>
                        <span>Suppliers</span>
                    </a>
                    <a class="{{ request()->routeIs('admin.purchase-orders.*') ? 'current' : '' }}" href="{{ route('admin.purchase-orders.index') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M6 3h9l3 3v15H6z"></path>
                            <path d="M15 3v3h3"></path>
                            <path d="M9 12h6"></path>
                        </svg>
                        <span>Purchase Orders</span>
                    </a>
                    @if (auth()->user()?->isStaff())
                        <a class="{{ request()->routeIs('kiosk') ? 'current' : '' }}" href="{{ route('kiosk') }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <rect x="4" y="5" width="16" height="11" rx="1.5"></rect>
                                <path d="M10 19h4"></path>
                                <path d="M12 16v3"></path>
                            </svg>
                            <span>Kiosk Mode</span>
                        </a>
                    @endif
                </nav>

                <form class="sidebar-logout" method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M10 17l5-5-5-5"></path>
                            <path d="M15 12H8"></path>
                            <path d="M4 4h7v3H7v10h4v3H4z"></path>
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>
            </aside>

            <main class="content">
                <header class="topbar">
                    <div class="topbar-left">
                        <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-label="Toggle navigation" aria-controls="admin-sidebar" aria-expanded="false">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M4 7h16"></path>
                                <path d="M4 12h16"></path>
                                <path d="M4 17h16"></path>
                            </svg>
                        </button>
                        <span class="topbar-title">Admin Menu</span>
                    </div>
                    <nav class="topbar-tools" aria-label="Admin quick actions">
                        <div class="notif-dropdown">
                            <button class="topbar-icon notif-trigger" type="button" aria-label="Notifications">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M15 17h5l-1.4-1.4a2 2 0 0 1-.6-1.4V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5"></path>
                                    <path d="M9.5 17a2.5 2.5 0 0 0 5 0"></path>
                                </svg>
                                @if (($unreadNotificationsCount ?? 0) > 0)
                                    <span class="notif-badge">{{ min(99, (int) $unreadNotificationsCount) }}</span>
                                @endif
                            </button>
                            <div class="notif-menu">
                                <div class="notif-menu-head">
                                    <strong>Notifications</strong>
                                    <form method="POST" action="{{ route('notifications.read-all') }}">
                                        @csrf
                                        <button type="submit">Mark all read</button>
                                    </form>
                                </div>
                                <div class="notif-items">
                                    @forelse (($notifications ?? collect()) as $notification)
                                        <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                            @csrf
                                            <button type="submit" class="notif-item {{ $notification->is_read ? '' : 'is-unread' }}">
                                                <span>{{ $notification->message }}</span>
                                                <small>{{ $notification->created_at?->diffForHumans() }}</small>
                                            </button>
                                        </form>
                                    @empty
                                        <p class="notif-empty">No notifications yet.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        <a class="topbar-icon" href="{{ route('profile.edit') }}" aria-label="Profile">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="12" cy="8" r="3.2"></circle>
                                <path d="M5 20a7 7 0 0 1 14 0"></path>
                            </svg>
                        </a>
                    </nav>
                </header>
                <div class="content-inner">
                    {{ $slot }}
                </div>
            </main>
        </div>
        <div class="confirm-modal" id="confirm-modal" hidden>
            <div class="confirm-modal__backdrop" data-confirm-close></div>
            <div class="confirm-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title">
                <h2 id="confirm-modal-title">Please confirm</h2>
                <p id="confirm-modal-message">Are you sure?</p>
                <div class="confirm-modal__actions">
                    <button type="button" class="confirm-modal__btn is-muted" data-confirm-cancel>Cancel</button>
                    <button type="button" class="confirm-modal__btn is-danger" data-confirm-accept>Continue</button>
                </div>
            </div>
        </div>
        <script>
            (() => {
                const layout = document.querySelector('[data-layout-root]');
                const toggle = document.querySelector('[data-sidebar-toggle]');

                if (!layout || !toggle) {
                    return;
                }

                const closeSidebar = () => {
                    layout.classList.remove('is-nav-open');
                    toggle.setAttribute('aria-expanded', 'false');
                };

                const openSidebar = () => {
                    layout.classList.add('is-nav-open');
                    toggle.setAttribute('aria-expanded', 'true');
                };

                toggle.addEventListener('click', () => {
                    if (layout.classList.contains('is-nav-open')) {
                        closeSidebar();
                    } else {
                        openSidebar();
                    }
                });

                document.addEventListener('click', (event) => {
                    if (window.innerWidth > 900) {
                        return;
                    }

                    const sidebar = document.getElementById('admin-sidebar');
                    if (!sidebar) {
                        return;
                    }

                    if (!layout.contains(event.target) || (!sidebar.contains(event.target) && !toggle.contains(event.target))) {
                        closeSidebar();
                    }
                });

                window.addEventListener('resize', () => {
                    if (window.innerWidth > 900) {
                        closeSidebar();
                    }
                });
            })();

            document.querySelectorAll('.notif-trigger').forEach((trigger) => {
                trigger.addEventListener('click', () => {
                    trigger.closest('.notif-dropdown')?.classList.toggle('is-open');
                    trigger.blur();
                });
            });

            document.addEventListener('click', (event) => {
                document.querySelectorAll('.notif-dropdown.is-open').forEach((node) => {
                    if (!node.contains(event.target)) {
                        node.classList.remove('is-open');
                    }
                });
            });

            (() => {
                const modal = document.getElementById('confirm-modal');
                const messageNode = document.getElementById('confirm-modal-message');
                const acceptBtn = modal?.querySelector('[data-confirm-accept]');
                const cancelBtn = modal?.querySelector('[data-confirm-cancel]');
                const closeBtn = modal?.querySelector('[data-confirm-close]');
                let pendingAction = null;

                if (!modal || !messageNode || !acceptBtn || !cancelBtn || !closeBtn) {
                    return;
                }

                const openModal = (message, action) => {
                    messageNode.textContent = message || 'Are you sure?';
                    pendingAction = action;
                    modal.hidden = false;
                };

                const closeModal = () => {
                    modal.hidden = true;
                    pendingAction = null;
                };

                const extractConfirmMessage = (handlerString) => {
                    const match = handlerString.match(/confirm\((['"])(.*?)\1\)/);
                    return match ? match[2] : 'Are you sure?';
                };

                document.querySelectorAll('form[onsubmit*="confirm("]').forEach((form) => {
                    const inline = form.getAttribute('onsubmit') || '';
                    if (!form.dataset.confirm) {
                        form.dataset.confirm = extractConfirmMessage(inline);
                    }
                    form.removeAttribute('onsubmit');
                });

                document.querySelectorAll('form[data-confirm]').forEach((form) => {
                    form.addEventListener('submit', (event) => {
                        if (form.dataset.confirmPassed === '1') {
                            form.dataset.confirmPassed = '0';
                            return;
                        }

                        event.preventDefault();
                        openModal(form.dataset.confirm || 'Are you sure?', () => {
                            form.dataset.confirmPassed = '1';
                            form.requestSubmit();
                        });
                    });
                });

                document.querySelectorAll('button[data-confirm]').forEach((button) => {
                    button.addEventListener('click', (event) => {
                        event.preventDefault();
                        const form = button.closest('form');
                        if (!form) {
                            return;
                        }

                        openModal(button.dataset.confirm || 'Are you sure?', () => {
                            form.requestSubmit();
                        });
                    });
                });

                acceptBtn.addEventListener('click', () => {
                    const action = pendingAction;
                    closeModal();
                    if (action) {
                        action();
                    }
                });

                [cancelBtn, closeBtn].forEach((node) => node.addEventListener('click', closeModal));

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && !modal.hidden) {
                        closeModal();
                    }
                });
            })();
        </script>
    </body>
</html>

