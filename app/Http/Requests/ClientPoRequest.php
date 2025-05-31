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
        return [
            // 'name' => 'required|min:5|max:255'
            'client_id' => 'required|exists:clients,id',
            'work_code' => 'required|max:30',
            'po_number' => 'required|max:30',
            'job_name' => 'required|max:255',
            'job_value' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'reimburse_type' => 'required|max:50',
            'price_total' => 'required|numeric',
            'document_path' => ValidUpload::field('required')->file('mimes:pdf|max:5000'),
            'date_invoice' => 'required|date',
        ];
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
