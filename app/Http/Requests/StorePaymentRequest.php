<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePaymentRequest extends FormRequest
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
        return [
            'order_id' => ['required','integer','exists:orders,id']
        ];
    }

    public function withValidator(Validator $validator): void 
    {
        $validator->after(function($validator) {
            $order = \App\Models\Order::find($this->order_id);

            if (! $order) {
                return;
            }

            if ($order->user_id !== $this->user()->id) {

                $validator->errors()->add('order_id','Order tidak ditemukan');
                return;
            }
            
            if ($order->status !== 'pending') {
                
                $validator->errors()->add('order_id','Order ini sudah tidak bisa dibayar');
                return;
            }

            if ($order->payment) {

                $validator->errors()->add('order_id','Order ini sudah dibayar');
            }
        });
    }
}
