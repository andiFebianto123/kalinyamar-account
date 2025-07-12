<div>
    <h5>{{trans('backpack::crud.profit_lost.consolidation_income_statement')}}</h5>
    <div class="table-responsive">
        <table id="table-account-{{$name}}" class="info-cast-account table">
            <thead class="text-left">
                <tr>
                <th style="width: 15%;">{{trans('backpack::crud.expense_account.column.code')}}</th>
                <th style="width: 45%;">{{trans('backpack::crud.expense_account.column.name')}}</th>
                <th style="width: ">{{trans('backpack::crud.expense_account.column.balance')}}</th>
                </tr>
            </thead>
            <tbody class="text-left">
                <tr>
                    <td colspan="3">
                        {{trans('backpack::crud.profit_lost.empty_account')}}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

@push('inline_scripts')
    @once
        <style>
            .saldo-str {
                font-size: 20px;
                font-weight: 700;
                padding-top: 200px;
            }
        </style>
        <style>
            .info-cast-account tbody,
            .info-cast-account td,
            .info-cast-account tfoot,
            .info-cast-account th,
            .info-cast-account thead,
            .info-cast-account tr {
                border-color: transparent;
            }

            .infor-cast-account {
                width: auto;
                table-layout: auto;
            }
            .info-cast-account th, .info-cast-account td{
                /* text-align: left; */
                white-space: nowrap;
            }
            .info-cast-account th:nth-child(1),
            .info-cast-account td:nth-child(1) { width: 15%; }

            .info-cast-account th:nth-child(2),
            .info-cast-account td:nth-child(2) { width: 45%; }

            .info-cast-account th:nth-child(3),
            .info-cast-account td:nth-child(3) { width: 25%; }

            .info-cast-account th:nth-child(4),
            .info-cast-account td:nth-child(4) { width: 15%; }
        </style>
        <style>
            .btn-danger {
                background-color: #e55353 !important;
            }
        </style>
    @endonce
@endpush

@push('after_scripts')
    <script>
       eventEmitter.on("account_create_success", async function(data){
            if(data.component_name != undefined){
                await SIAOPS.getAttribute(data.component_name).load();
            }else{
                window.location.href = location.href;
            }
        });
    </script>
@endpush
