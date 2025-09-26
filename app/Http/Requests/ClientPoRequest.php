<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Backpack\CRUD\app\Library\Validation\Rules\ValidUpload;

class ClientPoRequest extends FormRequest
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
        $status = request('status');
        $rule_origin = [
            // 'name' => 'required|min:5|max:255'
            'client_id' => 'required|exists:clients,id',
            'work_code' => 'required|max:30|unique:client_po,work_code,'. $id,
            'po_number' => 'required|max:30|unique:client_po,po_number,'. $id,
            'job_name' => 'required|max:255',
            'job_value' => 'required|numeric',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'reimburse_type' => 'required|max:50',
            // 'price_total' => 'required|numeric',
            'document_path' => ValidUpload::field('nullable')->file('mimes:pdf|max:5000'),
            'date_invoice' => 'nullable|date',
            'rap_value' => 'required|numeric',
            // 'price_after_year' => 'required|numeric',
            'load_general_value' => 'nullable|numeric',
            'category' => 'required',
        ];

        $rule_no_po = [
            'work_code' => 'required|max:30|unique:client_po,work_code,'. $id,
            // 'po_number' => 'required|max:30|unique:client_po,po_number,'. $id,
            'client_id' => 'nullable|exists:clients,id',
            'job_name' => 'nullable|max:255',
            'rap_value' => 'nullable|numeric',
            'job_value' => 'nullable|numeric',
            'tax_ppn' => 'nullable|numeric',
            'reimburse_type' => 'nullable|max:50',
            'load_general_value' => 'nullable|numeric',
            'document_path' => ValidUpload::field('nullable')->file('mimes:pdf|max:5000'),
            'category' => 'nullable',
        ];

        // rule defautl
        $rule = $rule_no_po;

        if($status == 'TANPA PO'){
            $rule = $rule_no_po;
            $rule['po_number'] = 'nullable|max:30';
        }else if($status == 'ADA PO'){
            $rule = $rule_origin;
        }

        if(request()->has('work_code')){
            $rule['work_code'] = 'required|max:30|unique:client_po,work_code,'.$id;
        }else{
            $rule['work_code'] = 'nullable|max:30|unique:client_po,work_code,'.$id;
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
