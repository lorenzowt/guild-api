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
use App\Services\Mission\CancelMissionService;
use Illuminate\Support\Facades\Gate;



class MissionController extends Controller
{
    public function index(IndexResourceRequest $request)
    {
        $perPage = $request->validated()['per_page'] ?? 10;

    $missions = Mission::query()
        ->visibleTo($request->user())
        ->with(['quest', 'heroes'])
        ->latest()
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

    public function show(Mission $mission)
    {
        Gate::authorize('view', $mission);

        $mission->load(['quest', 'heroes']);

        return (new MissionResource($mission))
            ->additional([
                'message' => 'Mission retrieved successfully.',
            ]);
    }

    public function complete(
        Mission $mission,
        CompleteMissionService $completeMissionService
    ) {
        Gate::authorize('complete', $mission);

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

    public function cancel(
        Mission $mission,
        CancelMissionService $cancelMissionService
    ) {
        Gate::authorize('cancel', $mission);

        $mission = $cancelMissionService->cancel($mission);

        if ($mission === null) {
            return response()->json([
                'message' => 'Only assigned missions can be canceled.',
            ], 409);
        }

        return (new MissionResource($mission))
            ->additional([
                'message' => 'Mission canceled successfully.',
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
