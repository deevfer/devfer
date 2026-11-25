<?php

namespace App\Http\Requests;

use App\Models\Service;
use App\Models\SiteText;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:190'],
            'company' => ['nullable', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40', 'regex:/\A[0-9]{6,40}\z/'],
            'service' => ['required', Rule::in([...Service::query()->visible()->pluck('slug')->all(), 'other'])],
            'message' => ['required', 'string', 'min:20', 'max:5000'],
            'recaptcha_token' => ['nullable', 'string', 'max:4000'],
            'website' => ['nullable', 'string', 'max:200'], // honeypot, must stay empty
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        $t = SiteText::forLocale($this->route('locale'));

        return [
            'name.required' => $t['errors.name'],
            'name.max' => $t['errors.too_long'],
            'email.required' => $t['errors.email'],
            'email.email' => $t['errors.email'],
            'email.max' => $t['errors.too_long'],
            'company.max' => $t['errors.too_long'],
            'phone.required' => $t['errors.phone'],
            'phone.max' => $t['errors.too_long'],
            'phone.regex' => $t['errors.phone'],
            'service.required' => $t['errors.service'],
            'service.in' => $t['errors.service'],
            'message.required' => $t['errors.message'],
            'message.min' => $t['errors.message'],
            'message.max' => $t['errors.too_long'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(collect($this->only(['name', 'email', 'company', 'phone', 'message']))
            ->map(fn ($v) => is_string($v) ? trim($v) : $v)->all());
    }
}
