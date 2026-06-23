<?php

namespace App\Http\Requests;

use App\Enums\LeaveStatus;
use App\Models\LeaveRequest;
use App\Rules\NoOverlappingLeave;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeaveRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Merge missing date/member fields from the existing record when any
     * overlap-relevant field is being updated, so partial PATCHes validate correctly.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->hasAny(['team_member_id', 'start_date', 'end_date'])) {
            return;
        }

        /** @var LeaveRequest $leaveRequest */
        $leaveRequest = $this->route('leaveRequest');

        $this->merge([
            'team_member_id' => $this->input('team_member_id', $leaveRequest->team_member_id),
            'start_date' => $this->input('start_date', $leaveRequest->start_date->format('Y-m-d')),
            'end_date' => $this->input('end_date', $leaveRequest->end_date->format('Y-m-d')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'team_member_id' => ['sometimes', 'integer', 'exists:team_members,id'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
            'reason' => ['sometimes', 'string', 'max:500'],
            'status' => ['sometimes', 'string', Rule::in(array_column(LeaveStatus::cases(), 'value'))],
        ];

        if ($this->hasAny(['team_member_id', 'start_date', 'end_date'])) {
            /** @var LeaveRequest $leaveRequest */
            $leaveRequest = $this->route('leaveRequest');

            $rules['start_date'][] = new NoOverlappingLeave(
                (int) $this->input('team_member_id'),
                (string) $this->input('start_date'),
                (string) $this->input('end_date'),
                $leaveRequest->id,
            );
        }

        return $rules;
    }
}
