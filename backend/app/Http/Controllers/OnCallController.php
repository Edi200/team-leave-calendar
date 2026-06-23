<?php

namespace App\Http\Controllers;

use App\Services\OnCallService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnCallController extends Controller
{
    public function __construct(private OnCallService $onCallService) {}

    public function index(Request $request): JsonResponse
    {
        $date = $request->filled('week')
            ? Carbon::parse($request->string('week'))->startOfDay()
            : Carbon::now()->startOfDay();

        $weekStart = $date->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $date->copy()->endOfWeek(Carbon::SUNDAY)->startOfDay();

        $onCallMember = $this->onCallService->getOnCallMember($weekStart);

        return response()->json([
            'week_start' => $weekStart->format('Y-m-d'),
            'week_end' => $weekEnd->format('Y-m-d'),
            'on_call_member' => [
                'id' => $onCallMember->id,
                'name' => $onCallMember->name,
            ],
            'conflict' => $this->onCallService->hasConflict($onCallMember, $weekStart, $weekEnd),
        ]);
    }
}
