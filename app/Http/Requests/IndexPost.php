<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexPost extends FormRequest
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
            'account' => 'required',
            'password'  => 'required',
            'code'  => 'required',
            'sid'  => 'required',
        ];
    }

    public function messages()
    {
        return [
            'account.required' => '账号不能为空',
            'password.required'  => '密码不能为空',
            'code.required'  => '验证码不能为空',
            'sid.required'  => '其他错误',
        ];
    }
}
