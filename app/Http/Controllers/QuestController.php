<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\IndexResourceRequest;
use App\Http\Requests\Quest\StoreQuestRequest;
use App\Models\Quest;
use App\Http\Resources\QuestResource;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Quest\UpdateQuestRequest;


class QuestController extends Controller
{
    public function index(IndexResourceRequest $request)
    {
        $perPage = $request->validated()['per_page'] ?? 10;

        $quests = Quest::active()->paginate($perPage);

        return QuestResource::collection($quests)
            ->additional([
                'message' => 'Quests retrieved successfully.'
            ]);

    }

    public function store(StoreQuestRequest $request)
    {
        Gate::authorize('create', Quest::class);

        $quest = $request->user()
            ->createdQuests()
            ->create([
                ...$request->validated(),
                'is_active' => true,
            ]);

        return (new QuestResource($quest))
            ->additional([
                'message' => 'Quest created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, string $id)
    {
        $quest = Quest::active()->findOrFail($id);

        return (new QuestResource($quest))
            ->additional([
                'message' => 'Quest retrieved successfully.'
            ]);
    }

    public function update(UpdateQuestRequest $request, Quest $quest)
    {
        Gate::authorize('update', $quest);

        $quest->update($request->validated());

        return (new QuestResource($quest))
            ->additional([
                'message' => 'Quest updated successfully.',
            ]);
    }

    public function destroy(Quest $quest)
    {
        Gate::authorize('delete', $quest);

        $quest->deactivate();
        $quest->save();

        return response()->json([
            'message' => 'Quest deleted successfully.',
        ]);
    }
}
