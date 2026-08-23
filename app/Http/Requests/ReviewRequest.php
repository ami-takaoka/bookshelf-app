<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
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
     * レビュー投稿・更新時のバリデーションルールを定義する。
     *
     * 評価を1から5の整数、レビュー内容を1000文字以内の文字列として検証する。
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'max:1000'],
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
            'rating.required' => '評価を選択してください',
            'rating.integer' => '評価は1から5の間で選択してください',
            'rating.between' => '評価は1から5の間で選択してください',
            'comment.required' => 'レビューを入力してください',
            'comment.max' => 'レビューは1000文字以内で入力してください',
        ];
    }
}