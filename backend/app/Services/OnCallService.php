<?php

namespace App\Services;

use App\Enums\LeaveStatus;
use App\Models\LeaveRequest;
use App\Models\TeamMember;
use Carbon\Carbon;

class OnCallService
{
    public function getOnCallMember(Carbon $weekStart): TeamMember
    {
        $weekStart = $weekStart->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();

        $rotationStart = Carbon::parse(config('oncall.start_date'))
            ->startOfWeek(Carbon::MONDAY)
            ->startOfDay();

        $weekIndex = (int) floor($rotationStart->diffInWeeks($weekStart, false));

        $members = TeamMember::query()->orderBy('id')->get();
        $count = $members->count();
        $memberIndex = ($weekIndex % $count + $count) % $count;

        return $members[$memberIndex];
    }

    public function hasConflict(TeamMember $member, Carbon $weekStart, Carbon $weekEnd): bool
    {
        return LeaveRequest::query()
            ->where('team_member_id', $member->id)
            ->where('status', LeaveStatus::Approved)
            ->overlapping($weekStart, $weekEnd)
            ->exists();
    }
}
