<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserPost extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'birthday' => 'required',
            'sex' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '姓名不能为空',
            'birthday.required' => '出生年月不能为空',
            'sex.required' => '性别不能为空'
        ];
    }
}
