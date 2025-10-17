<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TodoListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "title" => "string|required",
            "assignee" => "string|nullable",
            "due_date" => "date|required",
            "time_tracked" => "numeric",
            "status" => "string|required",
            "priority" => "string|required",
        ];
    }
}
