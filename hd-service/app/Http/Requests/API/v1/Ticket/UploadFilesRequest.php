<?php

namespace App\Http\Requests\API\v1\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class UploadFilesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'files.*.file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'files.*.is_public' => 'sometimes|boolean',
        ];
    }
}
