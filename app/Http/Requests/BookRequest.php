<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $book = $this->route('book');

        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:100'],
            'isbn' => [
                'required',
                'digits:13',
                Rule::unique('books', 'isbn')->ignore($book),
            ],
            'published_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image_url' => ['nullable', 'url'],
            'genres' => ['required', 'array', 'min:1'],
            'genres.*' => ['exists:genres,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'タイトルを入力してください',
            'title.max' => 'タイトルは255文字以内で入力してください',
            'author.required' => '著者を入力してください',
            'author.max' => '著者は100文字以内で入力してください',
            'isbn.required' => 'ISBNを入力してください',
            'isbn.digits' => 'ISBNは13桁で入力してください',
            'isbn.unique' => 'このISBNは既に登録されています',
            'published_date.required' => '出版日を入力してください',
            'published_date.date' => '出版日は日付形式で入力してください',
            'description.max' => '説明は1000文字以内で入力してください',
            'image_url.url' => '画像URLはURL形式で入力してください',
            'genres.required' => 'ジャンルを1つ以上選択してください',
            'genres.array' => 'ジャンルを1つ以上選択してください',
            'genres.min' => 'ジャンルを1つ以上選択してください',
        ];
    }
}
