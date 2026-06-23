<?php

namespace App\Http\Requests;

use App\Rules\NoOverlappingLeave;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'team_member_id' => ['required', 'integer', 'exists:team_members,id'],
            'start_date' => [
                'required',
                'date',
                new NoOverlappingLeave(
                    (int) $this->input('team_member_id'),
                    (string) $this->input('start_date'),
                    (string) $this->input('end_date'),
                ),
            ],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:500'],
        ];
    }
}
