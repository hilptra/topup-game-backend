<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse {

        return response()->json([
            'data' => [
                'user' => new UserResource($request->user()),
            ],
        ], 200);
    }
}
