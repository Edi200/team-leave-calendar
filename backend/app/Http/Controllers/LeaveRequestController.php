<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeaveRequestRequest;
use App\Http\Requests\UpdateLeaveRequestRequest;
use App\Http\Resources\LeaveRequestResource;
use App\Models\LeaveRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class LeaveRequestController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = LeaveRequest::query()
            ->with('teamMember')
            ->orderBy('start_date');

        if ($request->filled('team_member_id')) {
            $query->where('team_member_id', $request->integer('team_member_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return LeaveRequestResource::collection($query->get());
    }

    public function store(StoreLeaveRequestRequest $request): JsonResponse
    {
        $leaveRequest = LeaveRequest::query()->create($request->validated());
        $leaveRequest->refresh()->load('teamMember');

        return (new LeaveRequestResource($leaveRequest))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateLeaveRequestRequest $request, LeaveRequest $leaveRequest): LeaveRequestResource
    {
        $leaveRequest->update($request->validated());
        $leaveRequest->load('teamMember');

        return new LeaveRequestResource($leaveRequest);
    }

    public function destroy(LeaveRequest $leaveRequest): Response
    {
        $leaveRequest->delete();

        return response()->noContent();
    }
}
