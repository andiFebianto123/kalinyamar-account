<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceClientRequest extends FormRequest
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
        $id = $this->get('id') ?? $this->route('id');

        return [
            // 'name' => 'required|min:5|max:255'
            'invoice_number' => 'required|min:3|max:50|unique:invoice_clients,invoice_number,'.$id,
            'name' => 'required|min:5|max:100',
            'invoice_date' => 'required',
            'client_po_id' => 'required|exists:client_po,id',
            'status' => 'required|in:Paid,Unpaid',
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
