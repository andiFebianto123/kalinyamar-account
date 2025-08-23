<div id="detail-project">
    <div class="text-center mb-2">
        <h5>{{trans('backpack::crud.profit_lost.detail.project_profit_and_loss_report')}}</h5>
        <h5>{{$data->clientPo->client->name}}</h5>
    </div>

    <div>
        <p class="section-title">A. {{trans('backpack::crud.profit_lost.detail.contract_revenue')}}</p>
        <p>{{trans('backpack::crud.profit_lost.detail.contract_value')}}: <strong>Rp{{\App\Http\Helpers\CustomHelper::formatRupiah($data->contract_value)}}</strong></p>
    </div>

    <div class="mt-2">
        <p class="section-title">B. {{trans('backpack::crud.profit_lost.detail.project_related_costs')}}</p>

        <div class="table-responsive d-flex justify-content-center">
            <table class="table table-borderless w-auto text-start">
                <thead>
                    <tr>
                    <th scope="col">{{trans('backpack::crud.profit_lost.detail.fee_type')}}</th>
                    <th scope="col">{{trans('backpack::crud.profit_lost.detail.balance')}}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>{{trans('backpack::crud.profit_lost.detail.material')}}</td><td>Rp{{\App\Http\Helpers\CustomHelper::formatRupiah($data->price_material)}}</td></tr>
                    <tr><td>{{trans('backpack::crud.profit_lost.detail.subcon')}}</td><td>Rp{{\App\Http\Helpers\CustomHelper::formatRupiah($data->price_subkon)}}</td></tr>
                    <tr><td>{{trans('backpack::crud.profit_lost.detail.direct_labor_wages')}}</td><td>Rp{{\App\Http\Helpers\CustomHelper::formatRupiah($data->price_btkl)}}</td></tr>
                    <tr><td>{{trans('backpack::crud.profit_lost.detail.project_transportation')}}</td><td>Rp{{\App\Http\Helpers\CustomHelper::formatRupiah($data->price_transport_project)}}</td></tr>
                    <tr><td>{{trans('backpack::crud.profit_lost.detail.worker_consumption')}}</td><td>Rp{{\App\Http\Helpers\CustomHelper::formatRupiah($data->price_worker_consumption)}}</td></tr>
                    <tr><td>{{trans('backpack::crud.profit_lost.detail.project_equipment_rental')}}</td><td>Rp{{\App\Http\Helpers\CustomHelper::formatRupiah($data->price_project_equipment)}}</td></tr>
                    <tr><td>{{trans('backpack::crud.profit_lost.detail.other_costs')}}</td><td>Rp{{\App\Http\Helpers\CustomHelper::formatRupiah($data->price_other)}}</td></tr>
                </tbody>
            </table>
        </div>

        <p class="fw-bold mt-2">{{trans('backpack::crud.profit_lost.detail.total_project_cost')}}: Rp{{\App\Http\Helpers\CustomHelper::formatRupiah($data->total_project)}}</p>
    </div>

    <div class="mt-2">
        <p class="section-title">C. {{trans('backpack::crud.profit_lost.detail.project_profit_loss')}}</p>
        <p>{{trans('backpack::crud.profit_lost.detail.project_profit_loss_value')}}: <strong>Rp{{\App\Http\Helpers\CustomHelper::formatRupiah($data->price_profit_lost_project)}}</strong></p>
    </div>
</div>


@push('inline_scripts')
    <style>
        #detail-project p {
            font-size: 20px;
        }
        .section-title {
            font-weight: bold;
            margin-top: 20px;
        }
        .table th {
            font-size: 20px;
            font-weight: bold;
        }
        .table td {
            font-size: 20px;
        }
    </style>
@endpush
