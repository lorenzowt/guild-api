<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\Mission\StoreMissionRequest;
use App\Services\Mission\CreateMissionService;
use App\Http\Resources\MissionResource;
use App\Http\Requests\IndexResourceRequest;
use App\Services\Mission\CompleteMissionService;


class MissionController extends Controller
{
    public function index(IndexResourceRequest $request)
    {
        $perPage = $request->validated()['per_page'] ?? 10;

        $missions = $request->user()
            ->missions()
            ->with(['quest', 'heroes'])
            ->paginate($perPage);

        return MissionResource::collection($missions)
            ->additional([
                'message' => 'Missions retrieved successfully.',
            ]);
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

    public function complete(
        Request $request,
        string $id,
        CompleteMissionService $completeMissionService
    ) {
        $mission = $request->user()
            ->missions()
            ->findOrFail($id);

        $mission = $completeMissionService->complete($mission);

        if ($mission === null) {
            return response()->json([
                'message' => 'Only assigned missions can be completed.',
            ], 409);
        }

        return (new MissionResource($mission))
            ->additional([
                'message' => 'Mission completed successfully.',
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
