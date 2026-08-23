<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IsbnSearchRequest extends FormRequest
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
     * ISBN検索時のバリデーションルールを定義する。
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'isbn' => ['required', 'digits:13'],
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
            'isbn.required' => 'ISBNを入力してください。',
            'isbn.digits' => 'ISBNは13桁で入力してください。',
        ];
    }

    /**
     * バリデーション前にルートパラメータのISBNをリクエストへマージする。
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'isbn' => $this->route('isbn'),
        ]);
    }
}