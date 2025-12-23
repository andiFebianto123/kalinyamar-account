<div>
    @if (isset($message))
        <center><h3>{{ $message }}</h3></center>
    @endif
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
@endpush --}}

@push('after_scripts')
    <script>
        eventEmitter.on("cast_account_store_success", function(){
            window.location.href = location.href;
        });
    </script>
@endpush
