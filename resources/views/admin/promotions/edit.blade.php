<x-layouts.admin title="Kumachi Admin | Edit Promotion">
    <section class="admin-products-page">
        <header class="products-head">
            <h1>Edit Promotion</h1>
            <a class="products-add-button" href="{{ route('admin.promotions.index') }}">Back to Promotions</a>
        </header>

        <section class="products-form-panel">
            <form class="products-form" method="POST" action="{{ route('admin.promotions.update', $promotion) }}">
                @csrf
                @method('PUT')

                <div class="products-field">
                    <label for="code">Promo Code</label>
                    <input id="code" name="code" type="text" value="{{ old('code', $promotion->code) }}" required>
                    <x-input-error :messages="$errors->get('code')" class="products-form-error" />
                </div>

                <div class="products-field">
                    <label for="discount_value">Discount Value (₱)</label>
                    <input id="discount_value" name="discount_value" type="number" step="0.01" min="0.01" value="{{ old('discount_value', $promotion->discount_value) }}" required>
                    <x-input-error :messages="$errors->get('discount_value')" class="products-form-error" />
                </div>

                <div class="products-field">
                    <label for="expires_at">Expiration Date (optional)</label>
                    <input id="expires_at" name="expires_at" type="date" value="{{ old('expires_at', $promotion->expires_at?->toDateString()) }}">
                    <x-input-error :messages="$errors->get('expires_at')" class="products-form-error" />
                </div>

                <div class="products-form-actions">
                    <button class="products-add-button" type="submit">Update Promotion</button>
                </div>
            </form>
        </section>
    </section>
</x-layouts.admin>
