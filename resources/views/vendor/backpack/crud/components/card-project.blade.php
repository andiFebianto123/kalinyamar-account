<div>

    <div class="d-flex justify-content-between">
        <div>
            <h5>{{$title}}</h5>
        </div>
        <div>
            @if ($crud->hasAccess('create'))
                <a href="javascript:void(0)" id="btn-{{$name}}" data-bs-toggle="modal" data-bs-target="#modalCreate" class="btn btn-primary" bp-button="create" data-style="zoom-in">
                    <i class="la la-plus"></i>
                </a>
            @endif
        </div>
    </div>
    <div class="table-responsive">
        <div id="panel-{{$name}}" class="mt-4 info-cast-account">

        </div>
    </div>

</div>

@push('after_scripts')
    <script>
        $("#btn-{{$name}}").unbind('click').on('click', function (e) {
            e.preventDefault();
            var route = "{{ $route_create }}";
            OpenCreateFormModal({
                route: route,
                modal: {
                    id: '#modalCreate',
                    title: "{{ $title_create }}",
                    action: "{{ $route_store }}",
                }
            });
        });
    </script>
@endpush


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
                            }else{
                                window.location.href = location.href;
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
                                $("#panel-{{$name}}").empty();
                                forEachFlexible(result.data, function(index, value){
                                    $("#panel-{{$name}}").append(`
                                        <div class="d-flex justify-content-between mb-2">
                                            <div>${value[0]}</div>
                                            <div>${value[1]}</div>
                                        </div>
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

        eventEmitter.on("{{$name}}_create_success", async function(data){
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
