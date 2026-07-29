<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:100'],
            'isbn' => ['required', 'digits:13', 'unique:books,isbn'],
            'published_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image_url' => ['nullable', 'url'],
            'genres' => ['required', 'array', 'min:1'],
            'genres.*' => ['integer', 'exists:genres,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'ユーザーIDは必須です',
            'user_id.integer' => 'ユーザーIDは整数で指定してください',
            'user_id.exists' => '指定されたユーザーは存在しません',

            'title.required' => 'タイトルを入力してください',
            'title.string' => 'タイトルは文字列で入力してください',
            'title.max' => 'タイトルは255文字以内で入力してください',

            'author.required' => '著者を入力してください',
            'author.string' => '著者は文字列で入力してください',
            'author.max' => '著者は100文字以内で入力してください',

            'isbn.required' => 'ISBNを入力してください',
            'isbn.digits' => 'ISBNは13桁で入力してください',
            'isbn.unique' => 'このISBNは既に登録されています',

            'published_date.required' => '出版日を入力してください',
            'published_date.date' => '出版日は日付形式で入力してください',

            'description.string' => '説明は文字列で入力してください',
            'description.max' => '説明は1000文字以内で入力してください',

            'image_url.url' => '画像URLはURL形式で入力してください',

            'genres.required' => 'ジャンルを1つ以上選択してください',
            'genres.array' => 'ジャンルは配列で指定してください',
            'genres.min' => 'ジャンルを1つ以上選択してください',
            'genres.*.integer' => 'ジャンルIDは整数で指定してください',
            'genres.*.exists' => '指定されたジャンルは存在しません',
        ];
    }
}
