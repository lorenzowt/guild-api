<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\HeroClass;
use App\Models\Hero;
use App\Http\Requests\StoreHeroRequest;
use App\Http\Resources\HeroResource;
use App\Http\Requests\IndexResourceRequest;
use App\Http\Requests\UpdateHeroRequest;
use App\Enums\HeroStatus;
use Illuminate\Support\Facades\Gate;

use function PHPUnit\Framework\isEmpty;

class HeroController extends Controller
{

    public function index(IndexResourceRequest $request)
    {
        $perPage = $request->validated()['per_page'] ?? 10;

        $heroes = Hero::query()
            ->visibleTo($request->user())
            ->paginate($perPage);

        return HeroResource::collection($heroes)
            ->additional([
                'message' => 'Heroes retrieved successfully.',
            ]);
    }

    public function store(StoreHeroRequest $request)
    {
        $hero = $request->user()
            ->heroes()
            ->create($request->validated());
        
        return (new HeroResource($hero))
            ->additional([
                'message' => 'Hero created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Hero $hero)
    {
        Gate::authorize('view', $hero);

        return (new HeroResource($hero))
            ->additional([
                'message' => 'Hero retrieved successfully.',
            ]);
    }

    public function update(UpdateHeroRequest $request, Hero $hero)
    {
        Gate::authorize('update', $hero);

        $hero->update($request->validated());

        return (new HeroResource($hero))
            ->additional([
                'message' => 'Hero updated successfully.',
            ]);
    }

    public function destroy(Hero $hero)
    {
        Gate::authorize('delete', $hero);

        $hero->deactivate();
        $hero->save();

        return response()->json([
            'message' => 'Hero deleted successfully.',
        ]);
    }

    public function heal(Hero $hero)
    {
        Gate::authorize('heal', $hero);

        if (! $hero->isInjured()) {
            return response()->json([
                'message' => "Hero isn't injured.",
            ], 409);
        }

        $hero->heal();
        $hero->save();

        return response()->json([
            'message' => 'Hero healed successfully.',
        ]);
    }
}
