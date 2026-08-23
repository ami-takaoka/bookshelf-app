<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenreRequest extends FormRequest
{
    /**
     * リクエストの実行を許可する。
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * ジャンル登録・更新時のバリデーションルールを定義する。
     *
     * 更新時は、対象ジャンル自身を除外してジャンル名の一意性を検証する。
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $genre = $this->route('genre');

        return [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('genres', 'name')->ignore($genre),
            ],
        ];
    }

    /**
     * バリデーションエラーメッセージを定義する。
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'ジャンル名を入力してください',
            'name.unique' => 'このジャンル名は既に登録されています',
            'name.max' => 'ジャンル名は50文字以内で入力してください',
        ];
    }
}