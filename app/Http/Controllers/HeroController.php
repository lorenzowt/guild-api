<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\HeroClass;
use App\Models\Hero;
use App\Http\Requests\StoreHeroRequest;
use App\Http\Resources\HeroResource;
use App\Http\Requests\IndexHeroRequest;
use App\Http\Requests\UpdateHeroRequest;

use function PHPUnit\Framework\isEmpty;

class HeroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexHeroRequest $request)
    {
        $perPage = $request->validated()['per_page'] ?? 10;

        $heroes = $request->user()
            ->heroes()
            ->paginate($perPage);

        return HeroResource::collection($heroes)
            ->additional([
                'message' => 'Heroes retrieved successfully.'
            ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
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

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $hero = $request->user()->heroes()->findOrFail($id);

        return (new HeroResource($hero))
            ->additional([
                'message' => 'Hero retrieved successfully.'
            ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHeroRequest $request, string $id)
    {
        $hero = $request->user()->heroes()->findOrFail($id);

        $data = $request->validated();

        $hero->update($data);
        
        return (new HeroResource($hero))
            ->additional([
                'message' => 'Hero updated successfully.'
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
