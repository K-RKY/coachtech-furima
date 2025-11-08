<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
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
            'name' => 'required',
            'description' => 'required|max:255',
            'image' => 'required|image|mimes:jpg,png',
            'categories' => 'required',
            'condition_id' => 'required',
            'price' => 'required|integer|min:0',
            'brand' => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '商品名を入力してください',
            'description.required' => '商品の説明を入力してください',
            'description.max' => '255文字以内で入力してください',
            'image.required' => '商品画像を選択してください',
            'image.image' => 'アップロードされたファイルは画像ではありません。',
            'image.mimes' => '画像ファイルはjpgまたはpng形式にしてください',
            'categories.required' => 'カテゴリーを選択してください',
            'condition_id.required' => '商品の状態を選択してください',
            'price.required' => '販売価格を入力してください',
            'price.integer' => '販売価格は数値を入力してください',
            'price.min' => '販売価格が不正です',
        ];
    }
}
