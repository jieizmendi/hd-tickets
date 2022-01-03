<?php

namespace App\Http\Requests\API\v1\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'subject' => 'required|string|between:3,50',
            'content' => 'required|string|between:3,1000',
            'files.*.file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'files.*.is_public' => 'sometimes|boolean',
        ];
    }
}
