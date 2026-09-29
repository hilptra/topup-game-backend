<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
use App\Models\Game;
use Illuminate\Http\JsonResponse;

class GameController extends Controller
{
    public function index(): JsonResponse
    {
        $games = Game::where('is_active',true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => GameResource::collection($games)
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $game = Game::where('slug', $slug)
            ->where('is_active',true)
            ->with(['products' => function ($query) {
                $query->where('is_active',true)->orderBy('sort_order');
            }])->firstOrFail();

        return response()->json([
            'data' => new GameResource($game)
        ]);
    }
}
