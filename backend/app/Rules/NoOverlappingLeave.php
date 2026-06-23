<?php

namespace App\Rules;

use App\Models\LeaveRequest;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoOverlappingLeave implements ValidationRule
{
    public function __construct(
        private int $teamMemberId,
        private string $startDate,
        private string $endDate,
        private ?int $ignoreId = null,
    ) {}

    /**
     * @param  Closure(string): void  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = LeaveRequest::query()
            ->where('team_member_id', $this->teamMemberId)
            ->blocking()
            ->overlapping($this->startDate, $this->endDate);

        if ($this->ignoreId !== null) {
            $query->where('id', '!=', $this->ignoreId);
        }

        if ($query->exists()) {
            $fail('This team member already has a leave request that overlaps these dates.');
        }
    }
}
