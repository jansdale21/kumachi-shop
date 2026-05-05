@php
    $isAdmin = strtolower((string) data_get($user, 'role.role_name')) === 'admin';
@endphp

@if ($isAdmin)
    <x-layouts.admin title="Kumachi | Admin Profile">
        @push('styles')
            <link rel="stylesheet" href="{{ asset('css/customer/profile.css') }}">
        @endpush

        <section class="profile-page admin-profile-page">
            <div class="profile-shell admin-profile-shell">
                <h1 class="profile-title">Admin Profile</h1>

                <div class="profile-card profile-main-card">
                    @include('profile.partials.update-profile-information-form')
                </div>

                <div class="profile-card">
                    <div class="profile-card-head">
                        <h2>Security Settings</h2>
                    </div>
                    @include('profile.partials.update-password-form')
                </div>

                <div class="profile-card profile-card-danger">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </section>
    </x-layouts.admin>
@else
    <x-layouts.customer title="Kumachi | Profile">
        @push('styles')
            <link rel="stylesheet" href="{{ asset('css/customer/profile.css') }}">
        @endpush

        <section class="profile-page">
            <div class="profile-shell">
                <h1 class="profile-title">My Profile</h1>

                <div class="profile-card profile-main-card">
                    @include('profile.partials.update-profile-information-form')
                </div>

                <div class="profile-card">
                    <div class="profile-card-head">
                        <h2>Saved Addresses</h2>
                        <a class="profile-button profile-button-small profile-button-link" href="#address-form">+ Add Address</a>
                    </div>

                    <form method="POST" action="{{ route('profile.addresses.store') }}" class="profile-inline-form" id="address-form" @if(!$errors->hasAny(['full_name', 'phone', 'street', 'city'])) style="display: none;" @endif>
                        @csrf
                        <div class="profile-inline-form-grid">
                            <div class="profile-field">
                                <label class="profile-label" for="address_full_name">Full Name</label>
                                <input id="address_full_name" name="full_name" type="text" class="profile-input" value="{{ old('full_name') }}" required>
                                <x-input-error class="profile-error" :messages="$errors->get('full_name')" />
                            </div>
                            <div class="profile-field">
                                <label class="profile-label" for="address_phone">Phone</label>
                                <input id="address_phone" name="phone" type="text" class="profile-input" value="{{ old('phone') }}" required>
                                <x-input-error class="profile-error" :messages="$errors->get('phone')" />
                            </div>
                            <div class="profile-field">
                                <label class="profile-label" for="address_street">Street</label>
                                <input id="address_street" name="street" type="text" class="profile-input" value="{{ old('street') }}" required>
                                <x-input-error class="profile-error" :messages="$errors->get('street')" />
                            </div>
                            <div class="profile-field">
                                <label class="profile-label" for="address_city">City</label>
                                <input id="address_city" name="city" type="text" class="profile-input" value="{{ old('city') }}" required>
                                <x-input-error class="profile-error" :messages="$errors->get('city')" />
                            </div>
                        </div>

                        <div class="profile-actions">
                            <button class="profile-button" type="submit">Save Address</button>
                        </div>
                    </form>

                    @if ($addresses->isEmpty())
                        <p class="profile-empty-note">No saved addresses yet.</p>
                    @else
                        <div class="address-list">
                            @foreach ($addresses as $address)
                                <article class="address-item {{ $address->is_default ? 'is-default' : '' }}">
                                    <div class="address-content">
                                        <div class="address-title-row">
                                            <strong>{{ $address->full_name }}</strong>
                                            @if ($address->is_default)
                                                <span class="address-badge">Default</span>
                                            @endif
                                        </div>
                                        <p>{{ $address->street }}</p>
                                        <p>{{ $address->city }}</p>
                                        <p>{{ $address->phone }}</p>
                                    </div>

                                    <div class="address-actions">
                                        @unless ($address->is_default)
                                            <form method="POST" action="{{ route('profile.addresses.default', $address) }}" style="display: inline;">
                                                @csrf
                                                @method('PATCH')
                                                <button class="address-text-action" type="submit">Set Default</button>
                                            </form>
                                        @endunless
                                        <button class="address-icon-action" type="button" aria-label="Edit address" data-edit-address="{{ $address->id }}" data-address='@json($address)'>
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M4 20h4l10-10-4-4L4 16z"></path>
                                                <path d="m12 6 4 4"></path>
                                            </svg>
                                        </button>
                                        <form method="POST" action="{{ route('profile.addresses.destroy', $address) }}" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="address-icon-action danger" type="submit" aria-label="Delete address" onclick="return confirm('Are you sure you want to delete this address?');">
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M4 7h16"></path>
                                                    <path d="M10 11v6"></path>
                                                    <path d="M14 11v6"></path>
                                                    <path d="M6 7l1 13h10l1-13"></path>
                                                    <path d="M9 7V4h6v3"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="profile-card">
                    <div class="profile-card-head">
                        <h2>Saved Payment Methods</h2>
                        <a class="profile-button profile-button-small profile-button-link" href="#payment-form">+ Add Card</a>
                    </div>

                    <form method="POST" action="{{ route('profile.payment-methods.store') }}" class="profile-inline-form" id="payment-form" @if(!$errors->hasAny(['label', 'cardholder_name', 'card_brand', 'card_number', 'card_cvv', 'exp_month', 'exp_year'])) style="display: none;" @endif>
                        @csrf
                        <div class="profile-inline-form-grid profile-inline-form-grid-three">
                            <div class="profile-field">
                                <label class="profile-label" for="payment_label">Card Label</label>
                                <input id="payment_label" name="label" type="text" class="profile-input" value="{{ old('label') }}" placeholder="My Main Card" required>
                                <x-input-error class="profile-error" :messages="$errors->get('label')" />
                            </div>
                            <div class="profile-field">
                                <label class="profile-label" for="payment_cardholder_name">Cardholder Name</label>
                                <input id="payment_cardholder_name" name="cardholder_name" type="text" class="profile-input" value="{{ old('cardholder_name') }}" required>
                                <x-input-error class="profile-error" :messages="$errors->get('cardholder_name')" />
                            </div>
                            <div class="profile-field">
                                <label class="profile-label" for="payment_card_brand">Card Brand</label>
                                <input id="payment_card_brand" name="card_brand" type="text" class="profile-input" value="{{ old('card_brand') }}" placeholder="Visa, Mastercard">
                                <x-input-error class="profile-error" :messages="$errors->get('card_brand')" />
                            </div>
                            <div class="profile-field profile-inline-form-wide">
                                <label class="profile-label" for="payment_card_number">Card Number</label>
                                <input id="payment_card_number" name="card_number" type="text" class="profile-input" value="{{ old('card_number') }}" placeholder="4111 1111 1111 1111" required>
                                <x-input-error class="profile-error" :messages="$errors->get('card_number')" />
                            </div>
                            <div class="profile-field">
                                <label class="profile-label" for="payment_card_cvv">CVV</label>
                                <input id="payment_card_cvv" name="card_cvv" type="password" class="profile-input" value="" placeholder="123" maxlength="4" required>
                                <x-input-error class="profile-error" :messages="$errors->get('card_cvv')" />
                            </div>
                            <div class="profile-field">
                                <label class="profile-label" for="payment_exp_month">Expiry Month</label>
                                <input id="payment_exp_month" name="exp_month" type="number" class="profile-input" value="{{ old('exp_month') }}" min="1" max="12" required>
                                <x-input-error class="profile-error" :messages="$errors->get('exp_month')" />
                            </div>
                            <div class="profile-field">
                                <label class="profile-label" for="payment_exp_year">Expiry Year</label>
                                <input id="payment_exp_year" name="exp_year" type="number" class="profile-input" value="{{ old('exp_year') }}" min="2024" max="2100" required>
                                <x-input-error class="profile-error" :messages="$errors->get('exp_year')" />
                            </div>
                        </div>

                        <label class="profile-check-row">
                            <input type="checkbox" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}>
                            <span>Set as default card</span>
                        </label>

                        <div class="profile-actions">
                            <button class="profile-button" type="submit">Save Card</button>
                        </div>
                    </form>

                    @if ($paymentMethods->isEmpty())
                        <p class="profile-empty-note">No saved cards yet.</p>
                    @else
                        <div class="payment-method-list">
                            @foreach ($paymentMethods as $paymentMethod)
                                <article class="address-item {{ $paymentMethod->is_default ? 'is-default' : '' }}">
                                    <div class="address-content">
                                        <div class="address-title-row">
                                            <strong>{{ $paymentMethod->label }}</strong>
                                            @if ($paymentMethod->is_default)
                                                <span class="address-badge">Default</span>
                                            @endif
                                        </div>
                                        <p>{{ $paymentMethod->card_brand ?? 'Card' }} ending in {{ $paymentMethod->card_last4 }}</p>
                                        <p>{{ $paymentMethod->cardholder_name }}</p>
                                        <p>{{ str_pad((string) $paymentMethod->exp_month, 2, '0', STR_PAD_LEFT) }}/{{ $paymentMethod->exp_year }}</p>
                                    </div>

                                    <div class="address-actions">
                                        @unless ($paymentMethod->is_default)
                                            <form method="POST" action="{{ route('profile.payment-methods.default', $paymentMethod) }}" style="display: inline;">
                                                @csrf
                                                @method('PATCH')
                                                <button class="address-text-action" type="submit">Set Default</button>
                                            </form>
                                        @endunless
                                        <button class="address-icon-action" type="button" aria-label="Edit card" data-edit-payment="{{ $paymentMethod->id }}" data-payment='@json($paymentMethod)'>
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M4 20h4l10-10-4-4L4 16z"></path>
                                                <path d="m12 6 4 4"></path>
                                            </svg>
                                        </button>
                                        <form method="POST" action="{{ route('profile.payment-methods.destroy', $paymentMethod) }}" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="address-icon-action danger" type="submit" aria-label="Delete card" onclick="return confirm('Are you sure you want to delete this card?');">
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M4 7h16"></path>
                                                    <path d="M10 11v6"></path>
                                                    <path d="M14 11v6"></path>
                                                    <path d="M6 7l1 13h10l1-13"></path>
                                                    <path d="M9 7V4h6v3"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="profile-stat-grid">
                    <article class="profile-stat-card">
                        <strong>{{ number_format($stats['orders_count']) }}</strong>
                        <span>Total Orders</span>
                    </article>
                    <article class="profile-stat-card">
                        <strong>₱{{ number_format($stats['total_spent'], 2) }}</strong>
                        <span>Total Spent</span>
                    </article>
                    <article class="profile-stat-card">
                        <strong>{{ number_format($stats['loyalty_points']) }}</strong>
                        <span>Loyalty Points</span>
                    </article>
                </div>

                <div class="profile-card">
                    <div class="profile-card-head">
                        <h2>Security Settings</h2>
                    </div>
                    @include('profile.partials.update-password-form')
                </div>

                <div class="profile-card profile-card-danger">
                    @include('profile.partials.delete-user-form')
                </div>

                <form method="POST" action="{{ route('logout') }}" class="profile-logout-form">
                    @csrf
                    <button class="profile-button profile-button-danger profile-button-logout" type="submit">Logout</button>
                </form>
            </div>
        </section>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const toggles = document.querySelectorAll('a[href^="#"][class*="profile-button-link"]');
                toggles.forEach(toggle => {
                    toggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        const targetId = this.getAttribute('href');
                        const target = document.querySelector(targetId);
                        if (target) {
                            target.style.display = target.style.display === 'none' ? 'grid' : 'none';
                        }
                    });
                });

                // Address Editing functionality
                const editButtons = document.querySelectorAll('[data-edit-address]');
                editButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const address = JSON.parse(this.dataset.address);
                        const form = document.getElementById('address-form');
                        form.action = `/profile/addresses/${address.id}`;
                        
                        let methodInput = form.querySelector('input[name="_method"]');
                        if (!methodInput) {
                            methodInput = document.createElement('input');
                            methodInput.type = 'hidden';
                            methodInput.name = '_method';
                            form.appendChild(methodInput);
                        }
                        methodInput.value = 'PUT';
                        
                        form.querySelector('#address_full_name').value = address.full_name;
                        form.querySelector('#address_phone').value = address.phone;
                        form.querySelector('#address_street').value = address.street;
                        form.querySelector('#address_city').value = address.city;
                        
                        form.querySelector('button[type="submit"]').textContent = 'Update Address';
                        
                        form.style.display = 'grid';
                        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    });
                });

                // Reset Add Address form when clicking the "+" button
                const addAddressToggle = document.querySelector('a[href="#address-form"]');
                if (addAddressToggle) {
                    addAddressToggle.addEventListener('click', function(e) {
                        const form = document.getElementById('address-form');
                        form.action = "{{ route('profile.addresses.store') }}";
                        const methodInput = form.querySelector('input[name="_method"]');
                        if (methodInput) {
                            methodInput.remove();
                        }
                        form.reset();
                        form.querySelector('button[type="submit"]').textContent = 'Save Address';
                    });
                }

                // Payment Method Editing functionality
                const editPaymentButtons = document.querySelectorAll('[data-edit-payment]');
                editPaymentButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const payment = JSON.parse(this.dataset.payment);
                        const form = document.getElementById('payment-form');
                        form.action = `/profile/payment-methods/${payment.id}`;
                        
                        let methodInput = form.querySelector('input[name="_method"]');
                        if (!methodInput) {
                            methodInput = document.createElement('input');
                            methodInput.type = 'hidden';
                            methodInput.name = '_method';
                            form.appendChild(methodInput);
                        }
                        methodInput.value = 'PUT';
                        
                        form.querySelector('#payment_label').value = payment.label;
                        form.querySelector('#payment_cardholder_name').value = payment.cardholder_name;
                        form.querySelector('#payment_card_brand').value = payment.card_brand;
                        
                        const numInput = form.querySelector('#payment_card_number');
                        numInput.value = '';
                        numInput.placeholder = `**** **** **** ${payment.card_last4}`;
                        numInput.required = false;

                        const cvvInput = form.querySelector('#payment_card_cvv');
                        cvvInput.value = '';
                        cvvInput.placeholder = 'Enter current CVV';
                        cvvInput.required = true;

                        form.querySelector('#payment_exp_month').value = payment.exp_month;
                        form.querySelector('#payment_exp_year').value = payment.exp_year;

                        const isDefaultCheck = form.querySelector('input[name="is_default"]');
                        if (isDefaultCheck) {
                            isDefaultCheck.checked = payment.is_default;
                        }
                        
                        form.querySelector('button[type="submit"]').textContent = 'Update Card';
                        
                        form.style.display = 'grid';
                        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    });
                });

                // Reset Add Card form when clicking the "+" button
                const addPaymentToggle = document.querySelector('a[href="#payment-form"]');
                if (addPaymentToggle) {
                    addPaymentToggle.addEventListener('click', function(e) {
                        const form = document.getElementById('payment-form');
                        form.action = "{{ route('profile.payment-methods.store') }}";
                        const methodInput = form.querySelector('input[name="_method"]');
                        if (methodInput) {
                            methodInput.remove();
                        }
                        form.reset();
                        
                        const numInput = form.querySelector('#payment_card_number');
                        numInput.placeholder = "4111 1111 1111 1111";
                        numInput.required = true;

                        const cvvInput = form.querySelector('#payment_card_cvv');
                        cvvInput.value = '';
                        cvvInput.placeholder = '123';
                        cvvInput.required = true;

                        form.querySelector('button[type="submit"]').textContent = 'Save Card';
                    });
                }
            });
        </script>
    </x-layouts.customer>
@endif
