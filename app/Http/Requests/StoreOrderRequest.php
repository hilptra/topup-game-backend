<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
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
            'product_id' => ['required','integer','exists:products,id'],
            'game_account_id' => ['required','string'],
            'game_server_id' => ['nullable','string'],
        ];
    }

    public function withValidator(Validator $validator) {

        $validator->after(function ($validator) {

            $product = \App\Models\Product::find($this->product_id);

            if ($product && $product->game->requires_server_id && ! $this->game_server_id) {
                $validator->errors()->add('game_server_id','Server ID wajib diisi untuk game ini!');
            }
        });
    }
}
