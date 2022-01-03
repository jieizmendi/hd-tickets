<?php

namespace App\Http\Requests\API\v1\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'subject' => 'required|string|between:3,50',
            'content' => 'required|string|between:3,1000',
        ];
    }
}
