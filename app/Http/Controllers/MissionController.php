<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\Mission\StoreMissionRequest;
use App\Services\CreateMissionService;
use App\Http\Resources\MissionResource;


class MissionController extends Controller
{
    public function index()
    {
        //
    }

    public function store(
        StoreMissionRequest $request,
        CreateMissionService $service)
    {
        $mission = $service->execute(
            $request->user(),
            $request->validated()
        );

        return (new MissionResource(
            $mission->load(['quest', 'heroes'])
        ))
            ->response()
            ->setStatusCode(201);
        
    }

    public function show(Request $request, string $id)
    {
        $mission = $request->user()
            ->missions()
            ->with(['quest', 'heroes'])
            ->findOrFail($id);

        return (new MissionResource($mission))
            ->additional([
            'message' => 'Mission retrieved successfully.',
            ]);
    }

    public function update(Request $request, Mission $mission)
    {
        //
    }

    public function destroy(Mission $mission)
    {
        //
    }
}
