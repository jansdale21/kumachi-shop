<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInventoryRequest extends FormRequest
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
        $allowedUnits = ['ml', 'L', 'g', 'kg', 'pcs', 'unit'];

        return [
            'item_name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', Rule::in($allowedUnits)],
            'base_unit' => ['required', 'string', Rule::in($allowedUnits)],
            'units_per_stock_unit' => ['required', 'numeric', 'min:0.0001'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'reorder_level' => ['required', 'numeric', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('unit')) {
            $this->merge([
                'unit' => 'unit',
            ]);
        }

        if (! $this->filled('base_unit')) {
            $this->merge([
                'base_unit' => (string) $this->input('unit', 'unit'),
            ]);
        }

        if (! $this->filled('units_per_stock_unit')) {
            $this->merge([
                'units_per_stock_unit' => 1,
            ]);
        }
    }
}
