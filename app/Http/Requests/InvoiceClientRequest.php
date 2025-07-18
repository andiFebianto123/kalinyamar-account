<?php

namespace App\Http\Requests;

use App\Models\ClientPo;
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

        $client_po = request()->client_po_id;

        $rule = [
            // 'name' => 'required|min:5|max:255'
            'invoice_number' => 'required|min:3|max:50|unique:invoice_clients,invoice_number,'.$id,
            'invoice_date' => 'required',
            'client_po_id' => 'required|exists:client_po,id',
            'status' => 'required|in:Paid,Unpaid',
        ];

        if($id){
            $items = json_decode(request()->invoice_client_details_edit, true);
            $this->merge([
                'invoice_client_details_edit' => $items,
            ]);
            $rule['invoice_client_details_edit'] = [
                'required',
                'array',
                'min:1',
                function ($attribute, $value, $fail) use($client_po, $items){
                    $client = ClientPo::find($client_po);
                    $price_total = $client->job_value;
                    $items_total_price = collect($items)->sum('price');
                    if($price_total != $items_total_price){
                        $fail(trans('backpack::crud.invoice_client.field.item.errors.total_price'));
                    }
                }
            ];
            $rule['invoice_client_details_edit.*.name'] = 'required|max:120';
            $rule['invoice_client_details_edit.*.price'] = 'required|numeric|min:1000';
        }else{
            $items = json_decode(request()->invoice_client_details, true);
            $this->merge([
                'invoice_client_details' => $items,
            ]);
            $rule['invoice_client_details'] = [
                'required',
                'array',
                'min:1',
                function ($attribute, $value, $fail) use($client_po, $items){
                    $client = ClientPo::find($client_po);
                    $price_total = $client->job_value;
                    $items_total_price = collect($items)->sum('price');
                    if($price_total != $items_total_price){
                        $fail(trans('backpack::crud.invoice_client.field.item.errors.total_price'));
                    }
                }
            ];'required|array|min:1';
            $rule['invoice_client_details.*.name'] = 'required|max:120';
            $rule['invoice_client_details.*.price'] = 'required|numeric|min:1000';
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
