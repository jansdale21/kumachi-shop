<x-layouts.admin title="Kumachi Admin Dashboard">
    <section class="admin-dashboard">
        <h1 class="dashboard-title">Dashboard Overview</h1>

        <section class="metric-grid" aria-label="Summary metrics">
            <article class="metric-card">
                <div class="metric-card-head">
                    <span class="metric-icon">₱</span>
                </div>
                <strong>₱{{ number_format((float) ($todaySales ?? 0), 2) }}</strong>
                <p>Today's Sales</p>
            </article>

            <article class="metric-card">
                <div class="metric-card-head">
                    <span class="metric-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="5" y="4" width="14" height="16" rx="2"></rect>
                            <path d="M9 8h6"></path>
                        </svg>
                    </span>
                </div>
                <strong>{{ (int) ($todayOrders ?? 0) }}</strong>
                <p>Orders Today</p>
            </article>

            <article class="metric-card">
                <div class="metric-card-head">
                    <span class="metric-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="9" cy="8" r="2.5"></circle>
                            <circle cx="16" cy="9" r="2"></circle>
                            <path d="M5 18a4 4 0 0 1 8 0"></path>
                            <path d="M13 18a3 3 0 0 1 6 0"></path>
                        </svg>
                    </span>
                </div>
                <strong>{{ (int) ($customersCount ?? 0) }}</strong>
                <p>Customers</p>
            </article>

            <article class="metric-card">
                <div class="metric-card-head">
                    <span class="metric-icon alert">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="12" r="8"></circle>
                            <path d="M12 8v4"></path>
                            <circle cx="12" cy="15.6" r="0.6"></circle>
                        </svg>
                    </span>
                </div>
                <strong>{{ (int) ($lowStockItemsCount ?? 0) }}</strong>
                <p>Low Stock Items</p>
            </article>
        </section>

        <section class="analytics-grid">
            <article class="panel panel-chart">
                <h2>Weekly Sales</h2>
                <div class="bar-chart" aria-label="Weekly sales chart">
                    @foreach (($weekly ?? collect()) as $day)
                        @php
                            $total = (float) ($day['total'] ?? 0);
                            $max = (float) ($maxWeeklyTotal ?? 1);
                            $height = $max > 0 ? ($total / $max) * 100 : 0;
                        @endphp
                        <div class="bar-wrap" style="--h: {{ max(2, (int) round($height)) }}%" title="₱{{ number_format($total, 2) }}">
                            <span>{{ (string) ($day['label'] ?? '') }}</span>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="panel panel-products">
                <h2>Top Products</h2>
                <ul>
                    @forelse (($topProducts ?? collect()) as $product)
                        <li>
                            <span>{{ (string) $product['name'] }}</span>
                            <small>{{ (int) $product['sold'] }} sold</small>
                            <strong>₱{{ number_format((float) $product['revenue'], 2) }}</strong>
                        </li>
                    @empty
                        <li><span>No sales data yet</span><small>0 sold</small><strong>₱0.00</strong></li>
                    @endforelse
                </ul>
            </article>
        </section>

        <section class="panel panel-table" aria-label="Recent orders">
            <h2>Recent Orders</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Source</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse (($recentOrders ?? collect()) as $order)
                            @php
                                $statusLabel = strtolower((string) $order->status) === 'ready'
                                    ? (strtolower((string) $order->order_type) === 'delivery' ? 'Ready for Delivery' : 'Ready for Pickup')
                                    : ucfirst((string) $order->status);
                            @endphp
                            <tr>
                                <td>KM{{ str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $order->user?->name ?? 'Guest' }}</td>
                                <td>{{ (int) ($order->items_count ?? 0) }}</td>
                                <td>₱{{ number_format((float) $order->total_amount, 2) }}</td>
                                <td><span class="status-tag is-ordered">{{ strtoupper((string) $order->order_source) }}</span></td>
                                <td><span class="status-tag is-{{ strtolower((string) $order->status) }}">{{ $statusLabel }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center; padding: 1rem; color: var(--muted);">No orders yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </section>
</x-layouts.admin>
