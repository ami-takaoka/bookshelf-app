<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookRequest extends FormRequest
{
    /**
     * リクエストの実行を許可する。
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 書籍登録・更新時のバリデーションルールを定義する。
     *
     * 更新時は、対象書籍自身を除外してISBNの一意性を検証する。
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $book = $this->route('book');

        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:100'],
            'isbn' => [
                'nullable',
                'digits:13',
                Rule::unique('books', 'isbn')->ignore($book),
            ],
            'published_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image_url' => ['nullable', 'url'],
            'genres' => ['required', 'array', 'min:1'],
            'genres.*' => ['exists:genres,id'],
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
        ];
    }
}
