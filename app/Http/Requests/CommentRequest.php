<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'username' => 'required|alpha_num',
            'email' => 'required|email',
            'comment' => 'required',
            'homepage' => 'nullable|string',
            'file.*' => 'max:100'
        ];
    }
}
