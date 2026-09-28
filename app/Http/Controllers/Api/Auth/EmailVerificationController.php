<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function verify(string $id, string $hash): JsonResponse {

        $user = User::findOrFail($id);

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return response()->json([
                'message' => 'Link verifikasi tidak valid.'
            ], 403);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return response()->json([
            'message' => 'Verifikasi Email Berhasil.'
        ], 200);
    }

    public function resend(Request $request): JsonResponse {

        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email sudah terverifikasi.',
            ]);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Link verifikasi berhasil dikirim ulang.',
        ], 200);
    }

    
}
