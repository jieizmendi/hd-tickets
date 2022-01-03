<?php

namespace App\Http\Requests\API\v1\Tag;

use Illuminate\Foundation\Http\FormRequest;

class StoreTagRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'label' => 'required|string|min:3|max:50|unique:tags,label,NULL,id,deleted_at,NULL',
        ];
    }
}
