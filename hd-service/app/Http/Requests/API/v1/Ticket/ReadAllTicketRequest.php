<?php

namespace App\Http\Requests\API\v1\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class ReadAllTicketRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->anyTag) {
            $this->merge([
                'anyTag' => explode(",", $this->anyTag),
            ]);
        }

        if ($this->allTags) {
            $this->merge([
                'allTags' => explode(",", $this->allTags),
            ]);
        }

    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'owner' => 'sometimes|integer',
            'agent' => 'sometimes|integer',
            'status' => 'sometimes|string',
            'priority' => 'sometimes|integer',
            'anyTag' => 'sometimes|array',
            'anyTag.*' => 'integer',
            'allTags' => 'sometimes|array',
            'allTags.*' => 'integer',
        ];
    }
}
