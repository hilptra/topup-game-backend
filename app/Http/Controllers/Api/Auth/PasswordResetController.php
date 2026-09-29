<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    public function sendResetLink(ForgotPasswordRequest $request): JsonResponse {

        $status = Password::sendResetLink($request->only('email'));

        return response()->json([
            'message' => 'Jika email terdaftar, link reset password telah dikirim'
        ]);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse {

        $status = Password::reset(
            $request->only('email','password','password_confirmation','token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password'=> Hash::make($request->password),
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password berhasil di reset, Silahkan login dengan password baru'
            ],200);
        }

        return response()->json([
            'message' => 'Token reset tidak valid atau sudah kadaluarsa.'
        ],422);
    }
}
