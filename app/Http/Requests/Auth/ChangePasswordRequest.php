<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;

class ChangePasswordRequest extends FormRequest
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
            'current_password' => ['required','string'],
            'password' => ['required','string','min:8','confirmed'],
        ];
    }

    public function withValidator(Validator $validator):void {

        $validator->after(function($validator) {
            if (! Hash::check($this->current_password, $this->user()->password)) {
                $validator->errors()->add('current_password','Password tidak sesuai');
            };
        });
    }
    
}
