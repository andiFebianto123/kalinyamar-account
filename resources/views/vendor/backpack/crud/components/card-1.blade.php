<div>
    Hai namaku andi
</div>

{{-- @push('inline_scripts')
    @once
        <script>
            function submitButton(element){
                console.log($(element));
                console.log('ini adalah submit');
            }
        </script>
    @endonce
@endpush

@push('after_scripts')
    @once
    <script>
        function halloAndi(){
            console.log('hallo andi');
        }
        SIAOPS.setAttribute('andi_attribute', function(){
            return {
                name: 'andi',
                read: function(){
                    console.log(this);
                }
            }
        });
    </script>
    @endonce
@endpush --}}
