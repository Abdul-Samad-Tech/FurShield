<?php

namespace App\Http\Requests;

use App\Enums\FulfillmentMethod;
use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'contact_name' => ['required', 'string', 'max:120'],
            'contact_phone' => ['required', 'string', 'max:30'],
            'contact_email' => ['required', 'email', 'max:255'],
            'fulfillment' => ['required', Rule::enum(FulfillmentMethod::class)],
            'address_line' => ['required_if:fulfillment,delivery', 'nullable', 'string', 'max:255'],
            'city' => ['required_if:fulfillment,delivery', 'nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'cardholder' => ['required_if:payment_method,card', 'nullable', 'string', 'max:120'],
            'card_number' => ['required_if:payment_method,card', 'nullable', 'string', 'max:23'],
            'card_expiry' => ['required_if:payment_method,card', 'nullable', 'string', 'max:5'],
            'card_cvc' => ['required_if:payment_method,card', 'nullable', 'string', 'max:4'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'address_line.required_if' => 'Enter the delivery address.',
            'city.required_if' => 'Enter the delivery city.',
            'cardholder.required_if' => 'Enter the name on the card.',
            'card_number.required_if' => 'Enter the card number.',
            'card_expiry.required_if' => 'Enter the card expiry.',
            'card_cvc.required_if' => 'Enter the card security code.',
        ];
    }
}
