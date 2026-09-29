<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse {

        return response()->json([
            'data' => [
                'user' => new UserResource($request->user()),
            ],
        ], 200);
    }

    public function update(UpdateProfileRequest $request): JsonResponse {

        $user = $request->user();

        $user->update($request->validated());

        return response()->json([
            'data' => [
                'user' => new UserResource($user->fresh())
            ],
            'message' => 'Profil berhasil diperbarui'
        ]);
    }

    public function changePassword (ChangePasswordRequest $request) {

        $user = $request->user();

        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        $user->tokens()->where('id','!=', $request->user()->currentAccessToken()->id)->delete();

        return response()->json([
            'data' => new UserResource($user->fresh()),
            'message' => 'Password berhasil diubah'
        ]);
    }
}
