<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Backpack\CRUD\app\Library\Validation\Rules\ValidUpload;

class SpkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = request('id');

        $rule = [
            'subkon_id' => 'required|exists:subkons,id',
            'no_spk' => 'required|string|max:255',
            'date_spk' => 'required|string',
            'work_code' => 'required|max:30|unique:spk,work_code,'. $id,
            'job_name' => 'required|string|max:255',
            'job_description' => 'required|string',
            'job_value' => 'required|numeric|min:1000',
            'document_path' => ValidUpload::field('required')->file('mimes:pdf|max:5000'),
            'tax_ppn' => 'nullable|numeric|min:0',
            'status' => 'required|in:open,close',
            'total_value_with_tax' => 'nullable|numeric',
        ];

        if(request()->has('work_code')){
            $rule['work_code'] = 'required|max:30|unique:spk,work_code,'.$id;
        }else{
            $rule['work_code'] = 'nullable|max:30|unique:spk,work_code,'.$id;
        }

        return $rule;
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            //
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            //
        ];
    }
}
