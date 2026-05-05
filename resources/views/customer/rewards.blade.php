<x-layouts.customer title="Kumachi | Rewards">
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/customer/rewards.css') }}">
    @endpush

    <section class="rewards-page">
        <div class="rewards-shell">
            <h1 class="rewards-title">Loyalty Rewards</h1>

            <article class="points-card">
                <div class="points-head">
                    <div>
                        <p class="points-label">Your Points</p>
                        <p class="points-value">{{ number_format($pointsBalance) }}</p>
                    </div>
                    <span class="points-gift" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M20 12v8a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-8"></path>
                            <path d="M2 7h20v5H2z"></path>
                            <path d="M12 21V7"></path>
                            <path d="M12 7h4a2 2 0 1 0-2-2"></path>
                            <path d="M12 7H8a2 2 0 1 1 2-2"></path>
                        </svg>
                    </span>
                </div>

                @php
                    $progressPoints = $nextRewardThreshold > 0 ? $pointsBalance % $nextRewardThreshold : 0;
                    $progressPercent = $nextRewardThreshold > 0 ? min(100, (int) round(($progressPoints / $nextRewardThreshold) * 100)) : 0;
                @endphp

                <div class="progress-row">
                    <span>Progress to next reward</span>
                    <span>{{ $progressPoints }}/{{ $nextRewardThreshold }} points</span>
                </div>
                <div class="progress-track" aria-hidden="true">
                    <div class="progress-fill" data-progress="{{ $progressPercent }}"></div>
                </div>
            </article>

            <div class="rewards-grid">
                <section>
                    <h2 class="panel-title">Active Promotions</h2>
                    <div class="reward-list">
                        @forelse ($activePromotions as $promotion)
                            <article class="reward-item">
                                <div class="reward-left">
                                    <span class="reward-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">
                                            <path d="m12 3 2.6 5.3 5.9.9-4.2 4.2 1 5.9L12 16.6 6.7 19.3l1-5.9L3.5 9.2l5.9-.9Z"></path>
                                        </svg>
                                    </span>
                                    <div>
                                        <p class="reward-name">{{ $promotion->code }}</p>
                                        <span class="reward-points">₱{{ number_format((float) $promotion->discount_value, 2) }} off</span>
                                    </div>
                                </div>
                                <a class="reward-btn" href="{{ route('checkout.index', ['promo_code' => $promotion->code]) }}">Use at Checkout</a>
                            </article>
                        @empty
                            <article class="reward-item is-locked">
                                <div class="reward-left">
                                    <div>
                                        <p class="reward-name">No active promotions</p>
                                        <span class="reward-points">Check back later for new deals.</span>
                                    </div>
                                </div>
                                <button class="reward-btn is-disabled" type="button" disabled>Unavailable</button>
                            </article>
                        @endforelse
                    </div>

                    <article class="how-card">
                        <h3>How It Works</h3>
                        <div class="how-list">
                            <p class="how-step"><span>1</span>Earn 1 point for every ₱20 spent on completed orders.</p>
                            <p class="how-step"><span>2</span>Use promo codes during checkout for instant discounts.</p>
                            <p class="how-step"><span>3</span>Redeem points in checkout (100 points = ₱100 discount).</p>
                        </div>
                    </article>
                </section>

                <section>
                    <h2 class="panel-title">Recent Activity</h2>
                    <div class="activity-list">
                        @forelse ($activities as $activity)
                            <article class="activity-item">
                                <p class="activity-points {{ $activity->type === 'earned' ? 'is-positive' : 'is-negative' }}">
                                    {{ $activity->type === 'earned' ? '+' : '-' }}{{ (int) $activity->points }} pts
                                </p>
                                <p class="activity-desc">
                                    {{ $activity->type === 'earned' ? 'Points earned' : 'Points redeemed' }}
                                    @if ($activity->order)
                                        • Order #KM{{ str_pad((string) $activity->order->id, 6, '0', STR_PAD_LEFT) }}
                                    @endif
                                </p>
                                <p class="activity-date">{{ $activity->created_at?->format('Y-m-d') }}</p>
                            </article>
                        @empty
                            <article class="activity-item">
                                <p class="activity-points">No activity yet</p>
                                <p class="activity-desc">Place your first order to start earning points.</p>
                                <p class="activity-date">-</p>
                            </article>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </section>

    <script>
        (() => {
            const progressFill = document.querySelector('.progress-fill[data-progress]');

            if (!progressFill) {
                return;
            }

            const progress = Number(progressFill.dataset.progress || 0);
            progressFill.style.width = `${Math.min(100, Math.max(0, progress))}%`;
        })();
    </script>
</x-layouts.customer>
