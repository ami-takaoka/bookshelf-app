<?php

namespace App\Http\Requests;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ReadingPlanRequest extends FormRequest
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
        if ($this->isMethod('post')) {
            return [
                'book_id' => ['required', 'exists:books,id'],
                'target_date' => ['required', 'date', 'after_or_equal:today'],
            ];
        }

        return [
            'target_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required' => '書籍を選択してください',
            'book_id.exists' => '選択した書籍が存在しません',

            'target_date.required' => '期日を入力してください',
            'target_date.date' => '期日は日付形式で入力してください',
            'target_date.after_or_equal' => '期日は本日以降の日付を入力してください',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        if (! $this->isMethod('post')) {
            return;
        }

        $validator->after(function (Validator $validator) {

            $exists = ReadingPlan::where('user_id', auth()->id())
                ->where('book_id', $this->book_id)
                ->whereIn('status', [
                    ReadingPlanStatus::Pending,
                    ReadingPlanStatus::Expired,
                ])
                ->exists();

            if ($exists) {
                $validator->errors()->add(
                    'book_id',
                    'この書籍は未完了の読書計画として既に登録されています'
                );
            }
        });
    }
}
