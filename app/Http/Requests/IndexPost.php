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
            'captcha'  => 'required',
        ];
    }

    public function messages()
    {
        return [
            'account.required' => '账号不能为空',
            'password.required'  => '密码不能为空',
            'captcha.required'  => '验证码不正确',
        ];
    }
}
