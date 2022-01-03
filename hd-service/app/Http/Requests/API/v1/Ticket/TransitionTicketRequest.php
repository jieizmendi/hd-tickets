<?php

namespace App\Http\Requests\API\v1\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class TransitionTicketRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => 'required|in:' . implode(',', config('hd.statuses', [])),
            'content' => 'string|max:1000',
        ];
    }
}
