<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'phone' => $this->filled('phone') ? trim((string) $this->input('phone')) : null,
            'motorcycle_type' => trim((string) $this->input('motorcycle_type')),
            'license_plate' => strtoupper(trim((string) $this->input('license_plate'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'motorcycle_type' => ['required', 'string', 'max:100'],
            'license_plate' => ['required', 'string', 'max:20', 'regex:/^[A-Z0-9][A-Z0-9 .-]*$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'license_plate.regex' => 'Plat nomor hanya boleh berisi huruf, angka, spasi, titik, atau tanda hubung.',
        ];
    }
}
