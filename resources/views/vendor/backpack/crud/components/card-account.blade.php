<div>
    <div class="table-responsive">
        <table id="table-account-{{$name}}" class="info-cast-account table">
            <thead class="text-left">
                <tr>
                <th style="width: 15%;">{{trans('backpack::crud.expense_account.column.code')}}</th>
                <th style="width: 45%;">{{trans('backpack::crud.expense_account.column.name')}}</th>
                <th style="width: ">{{trans('backpack::crud.expense_account.column.balance')}}</th>
                <th>{{trans('backpack::crud.expense_account.column.action')}}</th>
                </tr>
            </thead>
            <tbody class="text-left">
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
        if(SIAOPS.getAttribute('accounts') == null){
            SIAOPS.setAttribute('accounts', function(){
                return {
                    name: 'accounts',
                    accounts_compact:[],
                    eventLoader: async function(){
                        eventEmitter.on("account_create_success", async function(data){
                            if(data.component_name != undefined){
                                await SIAOPS.getAttribute(data.component_name).load();
                            }
                        });
                    },
                    addAccount: function(instanceAccount){
                        var instance = this;
                        instance.accounts_compact.push(instanceAccount);
                    },
                    load:async function(){
                        this.eventLoader();
                        for (const callAccount of this.accounts_compact) {
                            await callAccount.load();
                        }
                    }
                }
            });
        }

        SIAOPS.setAttribute("{{$name}}", function(){
            return {
                name: "{{$name}}",
                url: "{{$route}}",
                table: "table-account-{{$name}}",
                load: async function(){
                    var instance = this;

                    return new Promise((resolve, reject) => {
                        $.ajax({
                            url: instance.url,
                            type: 'POST',
                            typeData: 'json',
                            success: function (result) {
                                $('#'+instance.table+ ' tbody').empty();
                                forEachFlexible(result.data, function(index, value){
                                    $('#'+instance.table+' tbody').append(`
                                        <tr>
                                            <td>${value[0]}</td>
                                            <td>${value[1]}</td>
                                            <td>${value[2]}</td>
                                            <td>${value[3]}</td>
                                        </tr>
                                    `);
                                });
                                resolve(result);
                            },
                            error: function (xhr, status, error) {
                                console.error(xhr);
                                reject(xhr);
                                alert('An error occurred while loading the create form.');
                            }
                        });
                    });
                }
            }
        });

        SIAOPS.getAttribute('accounts').addAccount(
            SIAOPS.getAttribute("{{$name}}"));

        eventEmitter.on("{{$name}}_update_success", async function(data){
            await SIAOPS.getAttribute("{{$name}}").load();
        });
    </script>
@endpush

@push('after_scripts')
    @once
        <script>
            $(function(){
                SIAOPS.getAttribute('accounts').load();
            });
        </script>
    @endonce
@endpush
