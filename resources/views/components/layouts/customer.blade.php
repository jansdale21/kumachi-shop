@props(['title' => config('app.name', 'Kumachi')])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title }}</title>
        <link rel="stylesheet" href="{{ asset('css/layouts/customer.css') }}">
        @stack('styles')
    </head>
    <body>
        <div class="shell" data-mobile-shell>
            <header class="topbar">
                <div class="topbar-inner">
                    <a class="brand" href="{{ route('home') }}">
                        <span class="brand-mark" aria-hidden="true">
                            <img src="{{ asset('media/kumachi-logo.png') }}" alt="{{ config('app.name', 'Kumachi') }} logo">
                        </span>
                        <span>{{ config('app.name', 'Kumachi') }}</span>
                    </a>

                        <button class="mobile-nav-toggle" type="button" data-mobile-nav-toggle aria-label="Toggle menu" aria-expanded="false">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M4 7h16"></path>
                                <path d="M4 12h16"></path>
                                <path d="M4 17h16"></path>
                            </svg>
                        </button>

                        <nav class="nav nav-main" aria-label="Customer navigation" data-mobile-nav-panel>
                            <a class="nav-link nav-control {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                            <a class="nav-link nav-control {{ request()->routeIs('menu') ? 'active' : '' }}" href="{{ route('menu') }}">Menu</a>
                            <a class="nav-link nav-control {{ request()->routeIs('orders') ? 'active' : '' }}" href="{{ route('orders') }}">Orders</a>
                            <a class="nav-link nav-control {{ request()->routeIs('rewards') ? 'active' : '' }}" href="{{ route('rewards') }}">Rewards</a>
                            @auth
                                @if (auth()->user()?->isStaff())
                                    <a class="nav-link nav-control {{ request()->routeIs('kiosk') ? 'active' : '' }}" href="{{ route('kiosk') }}">Kiosk</a>
                                @endif
                            @endauth
                        </nav>

                        <nav class="nav nav-auth" aria-label="Authentication navigation" data-mobile-nav-panel>
                            @auth
                                <div class="notif-dropdown">
                                    <button class="nav-control nav-icon-link notif-trigger" type="button" aria-label="Notifications">
                                        <svg class="icon-svg" viewBox="0 0 24 24" aria-hidden="true">
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
                                <a class="nav-control nav-cart" href="{{ route('cart.index') }}" aria-label="Cart">
                                    <svg class="cart-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle cx="9" cy="20" r="1.5"></circle>
                                        <circle cx="18" cy="20" r="1.5"></circle>
                                        <path d="M3 4h2l2.5 11h10.8l2.2-7.5H7.2"></path>
                                    </svg>
                                </a>
                                <a class="nav-control nav-icon-link" href="{{ route('profile.edit') }}" aria-label="Profile settings">
                                    <svg class="icon-svg" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle cx="12" cy="8" r="3.2"></circle>
                                        <path d="M5 20a7 7 0 0 1 14 0"></path>
                                    </svg>
                                </a>
                            @else
                                <a class="nav-link nav-control" href="{{ route('login') }}">
                                    <svg class="nav-inline-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                        <path d="M10 17l5-5-5-5"></path>
                                        <path d="M15 12H3"></path>
                                    </svg>
                                    Sign In
                                </a>
                                @if (Route::has('register'))
                                    <a class="nav-cta nav-control" href="{{ route('register') }}">Sign Up</a>
                                @endif
                                <a class="nav-control nav-cart" href="{{ route('cart.index') }}" aria-label="Cart">
                                    <svg class="cart-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle cx="9" cy="20" r="1.5"></circle>
                                        <circle cx="18" cy="20" r="1.5"></circle>
                                        <path d="M3 4h2l2.5 11h10.8l2.2-7.5H7.2"></path>
                                    </svg>
                                </a>
                            @endauth
                        </nav>
                </div>
            </header>

            <main class="content">
                {{ $slot }}
            </main>

            <footer class="footer">
                <div class="footer-panel">
                    <div class="footer-inner">
                        <div class="footer-grid">
                            <div class="footer-column">
                                <a class="footer-brand" href="{{ route('home') }}">
                                    <span class="brand-mark" aria-hidden="true">
                                        <img src="{{ asset('media/kumachi-logo.png') }}" alt="{{ config('app.name', 'Kumachi') }} logo">
                                    </span>
                                    <span>{{ config('app.name', 'Kumachi') }}</span>
                                </a>
                                <p>Premium coffee, crafted with care.</p>
                            </div>

                            <div class="footer-column">
                                <h3>Quick Links</h3>
                                <a href="{{ route('menu') }}">Menu</a>
                                <a href="{{ route('cart.index') }}">Cart</a>
                                <a href="{{ route('orders') }}">Orders</a>
                                <a href="{{ route('rewards') }}">Rewards</a>
                            </div>

                            <div class="footer-column">
                                <h3>Hours</h3>
                                <p>Mon-Fri: 7:00 AM - 8:00 PM</p>
                                <p>Sat-Sun: 8:00 AM - 9:00 PM</p>
                            </div>
                        </div>

                        <div class="footer-divider">&copy; {{ date('Y') }} Kumachi. All rights reserved.</div>
                    </div>
                </div>
            </footer>
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
                const shell = document.querySelector('[data-mobile-shell]');
                const toggle = document.querySelector('[data-mobile-nav-toggle]');
                const panels = Array.from(document.querySelectorAll('[data-mobile-nav-panel]'));

                if (!shell || !toggle || panels.length === 0) {
                    return;
                }

                const closeMenu = () => {
                    shell.classList.remove('is-mobile-nav-open');
                    toggle.setAttribute('aria-expanded', 'false');
                };

                const openMenu = () => {
                    shell.classList.add('is-mobile-nav-open');
                    toggle.setAttribute('aria-expanded', 'true');
                };

                toggle.addEventListener('click', () => {
                    if (shell.classList.contains('is-mobile-nav-open')) {
                        closeMenu();
                    } else {
                        openMenu();
                    }
                });

                panels.forEach((panel) => {
                    panel.querySelectorAll('a, button[type="submit"]').forEach((node) => {
                        node.addEventListener('click', () => {
                            if (window.innerWidth <= 640) {
                                closeMenu();
                            }
                        });
                    });
                });

                window.addEventListener('resize', () => {
                    if (window.innerWidth > 640) {
                        closeMenu();
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
