<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAddonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $addon = $this->route('addon');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('addons', 'name')->ignore($addon)],
            'price' => ['required', 'numeric', 'min:0'],
            'inventory_id' => ['required', 'integer', Rule::exists('inventories', 'id')],
            'inventory_usage_qty' => ['required', 'numeric', 'min:0.01'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', Rule::exists('products', 'id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please provide an add-on name.',
            'name.unique' => 'This add-on name already exists.',
            'price.required' => 'Please provide the add-on price.',
            'price.numeric' => 'Add-on price must be a valid amount.',
            'inventory_id.required' => 'Please select the linked inventory item for this add-on.',
            'inventory_usage_qty.required' => 'Please provide inventory usage quantity per add-on use.',
            'inventory_usage_qty.numeric' => 'Inventory usage quantity must be a valid number.',
            'product_ids.array' => 'Selected products are invalid.',
        ];
    }
}
