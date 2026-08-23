<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookUpdateRequest extends FormRequest
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
     * 書籍更新時のバリデーションルールを定義する。
     *
     * ISBNは更新対象の書籍自身を除外して一意性を検証する。
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:100'],
            'isbn' => [
                'nullable',
                'digits:13',
                Rule::unique('books', 'isbn')->ignore($this->book),
            ],
            'published_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image_url' => ['nullable', 'url'],
            'genres' => ['required', 'array', 'min:1'],
            'genres.*' => ['integer', 'exists:genres,id'],
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
            'title.required' => 'タイトルを入力してください',
            'title.string' => 'タイトルは文字列で入力してください',
            'title.max' => 'タイトルは255文字以内で入力してください',
            'author.required' => '著者を入力してください',
            'author.string' => '著者は文字列で入力してください',
            'author.max' => '著者は100文字以内で入力してください',
            'isbn.digits' => 'ISBNは13桁で入力してください',
            'isbn.unique' => 'このISBNは既に登録されています',
            'published_date.date' => '出版日は日付形式で入力してください',
            'description.string' => '説明は文字列で入力してください',
            'description.max' => '説明は1000文字以内で入力してください',
            'image_url.url' => '画像URLはURL形式で入力してください',
            'genres.required' => 'ジャンルを1つ以上選択してください',
            'genres.array' => 'ジャンルを1つ以上選択してください',
            'genres.min' => 'ジャンルを1つ以上選択してください',
            'genres.*.integer' => 'ジャンルIDは整数で指定してください',
            'genres.*.exists' => '指定されたジャンルは存在しません',
        ];
    }
}