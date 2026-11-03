<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\IndexResourceRequest;
use App\Models\Quest;
use App\Http\Resources\QuestResource;

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
}
