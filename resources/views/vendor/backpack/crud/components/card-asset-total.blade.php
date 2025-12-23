<div>
    <div class="row mb-3">
        <div class="col-6 label">Total Aset</div>
        <div class="col-6 value total_asset">Rp0</div>
    </div>
    <div class="row mb-3">
        <div class="col-6 label">Total Kewajiban</div>
        <div class="col-6 value total_liabilities">Rp0</div>
    </div>
    <div class="row">
        <div class="col-6 label">Total Ekuitas</div>
        <div class="col-6 value total_equity">Rp0</div>
    </div>
</div>

@push('inline_scripts')
    <style>
        .label {
            font-weight: 600;
            color: #0d1b2a;
        }
        .value {
            font-weight: 600;
            color: #0d1b2a;
            text-align: right;
        }
    </style>
@endpush

@push('after_scripts')
    <script>
        SIAOPS.setAttribute('asset-total', function(){
            return {
                name: 'asset-total',
                url: "{{ $route }}",
                eventLoader: function(){
                    eventEmitter.on('asset-total-create', function(data){

                    })
                },
                load: function(){
                    var instance = this;
                    $.ajax({
                        url: instance.url,
                        type: 'GET',
                        typeData: 'json',
                        success: function (result) {
                            // $('#'+instance.table+' tbody').empty();
                            // forEachFlexible(result.data, function(index, value){

                            // });
                            $('.total_asset').html(result.total_asset);
                            $('.total_liabilities').html(result.total_liabilities);
                            $('.total_equity').html(result.total_equity);

                        },
                        error: function (xhr, status, error) {
                            console.error(xhr);
                            alert('An error occurred while loading the create form.');
                        }
                    });
                }
            }
        });
    </script>
    <script>
        SIAOPS.getAttribute('asset-total').load();
    </script>
@endpush
