<?php

namespace App\Models;

use App\Enums\LeaveStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    /** @use HasFactory<\Database\Factories\LeaveRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'team_member_id',
        'start_date',
        'end_date',
        'reason',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => LeaveStatus::class,
        ];
    }

    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class);
    }

    /**
     * @param  Builder<LeaveRequest>  $query
     */
    public function scopeBlocking(Builder $query): void
    {
        $query->whereIn('status', [LeaveStatus::Pending, LeaveStatus::Approved]);
    }

    /**
     * @param  Builder<LeaveRequest>  $query
     */
    public function scopeOverlapping(Builder $query, mixed $start, mixed $end): void
    {
        $startDate = Carbon::parse($start)->toDateString();
        $endDate = Carbon::parse($end)->toDateString();

        $query->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate);
    }
}
