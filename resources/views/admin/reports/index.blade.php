<x-layouts.admin title="Kumachi | Reports">
    @php
        $titleCase = fn ($value) => \Illuminate\Support\Str::of((string) $value)->lower()->headline()->toString();
    @endphp

    <section class="reports-page">
        <header class="reports-head">
            <div>
                <h1 class="dashboard-title">Reports</h1>
                <p class="reports-subtitle">
                    {{ $from->toDateString() }} to {{ $to->toDateString() }} • {{ $allOrdersInRange }} total orders tracked
                </p>
            </div>
            <a
                class="reports-export"
                href="{{ route('admin.reports.export', ['from' => $from->toDateString(), 'to' => $to->toDateString()]) }}"
            >
                Export CSV (Excel)
            </a>
        </header>

        <form method="GET" class="reports-filter">
            <div class="reports-field">
                <label for="from">From</label>
                <input id="from" type="date" name="from" value="{{ $from->toDateString() }}">
            </div>
            <div class="reports-field">
                <label for="to">To</label>
                <input id="to" type="date" name="to" value="{{ $to->toDateString() }}">
            </div>
            <button type="submit" class="reports-apply">Apply Range</button>
        </form>

        <section class="metric-grid reports-metric-grid" aria-label="Report metrics">
            <article class="metric-card">
                <strong>₱{{ number_format($totalSales, 2) }}</strong>
                <p>Total Sales</p>
            </article>
            <article class="metric-card">
                <strong>{{ $orderCount }}</strong>
                <p>Orders (Excluding Cancelled)</p>
            </article>
            <article class="metric-card">
                <strong>₱{{ number_format($avgOrderValue, 2) }}</strong>
                <p>Average Order Value</p>
            </article>
            <article class="metric-card">
                <strong>{{ (int) $completedOrders }}</strong>
                <p>Completed Orders</p>
            </article>
            <article class="metric-card">
                <strong>{{ (int) $cancelledOrders }}</strong>
                <p>Cancelled Orders</p>
            </article>
            <article class="metric-card">
                <strong>
                    {{ $bestSalesDay ? $bestSalesDay->day : '-' }}
                </strong>
                <p>Best Sales Day</p>
            </article>
        </section>

        <details class="reports-advanced">
            <summary>Show more insights</summary>
            <section class="metric-grid reports-advanced-grid" aria-label="Advanced report metrics">
                <article class="metric-card">
                    <strong>{{ number_format((float) $completionRate, 1) }}%</strong>
                    <p>Completion Rate</p>
                </article>
                <article class="metric-card">
                    <strong>{{ number_format((float) $cancellationRate, 1) }}%</strong>
                    <p>Cancelled Share</p>
                </article>
                <article class="metric-card">
                    <strong>{{ $allOrdersInRange }}</strong>
                    <p>Total Orders (Including Cancelled)</p>
                </article>
            </section>
            <p class="reports-note">
                Completion rate = completed ÷ non-cancelled orders. Cancelled share = cancelled ÷ all orders in range.
            </p>
        </details>

        <section class="analytics-grid">
            <article class="panel panel-products">
                <h2>Orders by Status</h2>
                <ul>
                    @forelse ($ordersByStatus as $status => $total)
                        <li>
                            <span>{{ $titleCase($status) }}</span>
                            <strong>{{ (int) $total }}</strong>
                        </li>
                    @empty
                        <li><span>No data in selected range.</span><strong>0</strong></li>
                    @endforelse
                </ul>
            </article>

            <article class="panel panel-products">
                <h2>Top Products by Revenue</h2>
                <ul>
                    @forelse ($topProducts as $product)
                        <li>
                            <span>{{ $product->name }}</span>
                            <small>{{ (int) $product->qty }} sold</small>
                            <strong>₱{{ number_format((float) $product->revenue, 2) }}</strong>
                        </li>
                    @empty
                        <li><span>No sales yet</span><strong>₱0.00</strong></li>
                    @endforelse
                </ul>
            </article>
        </section>

        <section class="analytics-grid">
            <article class="panel panel-products">
                <h2>Sales by Order Source</h2>
                <ul>
                    @forelse ($salesBySource as $source)
                        <li>
                            <span>{{ $titleCase($source->order_source) }}</span>
                            <small>{{ (int) $source->orders }} orders</small>
                            <strong>₱{{ number_format((float) $source->sales, 2) }}</strong>
                        </li>
                    @empty
                        <li><span>No source data</span><strong>₱0.00</strong></li>
                    @endforelse
                </ul>
            </article>

            <article class="panel panel-products">
                <h2>Payment Method Mix</h2>
                <ul>
                    @forelse ($paymentBreakdown as $payment)
                        <li>
                            <span>{{ $titleCase($payment->payment_method) }}</span>
                            <small>{{ (int) $payment->tx_count }} transactions</small>
                            <strong>₱{{ number_format((float) $payment->total_amount, 2) }}</strong>
                        </li>
                    @empty
                        <li><span>No payment data</span><strong>₱0.00</strong></li>
                    @endforelse
                </ul>
            </article>
        </section>

        <section class="panel panel-table" aria-label="Daily sales report">
            <h2>Daily Trend</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Orders</th>
                            <th>Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dailySales as $row)
                            <tr>
                                <td>{{ $row->day }}</td>
                                <td>{{ (int) $row->orders }}</td>
                                <td>₱{{ number_format((float) $row->sales, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3">No records for this range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="analytics-grid">
            <article class="panel panel-table">
                <h2>Top Customers</h2>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Orders</th>
                                <th>Total Spent</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topCustomers as $customer)
                                <tr>
                                    <td>{{ $customer->customer_name }}</td>
                                    <td>{{ (int) $customer->orders_count }}</td>
                                    <td>₱{{ number_format((float) $customer->total_spent, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3">No customer spend data yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="panel panel-table">
                <h2>Hourly Demand</h2>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Hour</th>
                                <th>Orders</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($hourlyOrders as $hour)
                                <tr>
                                    <td>{{ str_pad((string) $hour->hour_slot, 2, '0', STR_PAD_LEFT) }}:00</td>
                                    <td>{{ (int) $hour->total }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2">No hourly data yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </section>
    </section>
</x-layouts.admin>
