@if ($crud->hasAccess('update', $entry))
	@if (!$crud->model->translationEnabled())

	{{-- Single add balance button --}}
	<a href="javascript:void(0)"
    onclick="addBalanceEntry({{ $entry->id_ }})"
    data-bs-toggle="modal"
    data-bs-target="#modalEdit"
    bp-button="add_balance" class="btn btn-sm btn-success">
		<i class="la la-money-bill"></i>
	</a>

	@endif
@endif

@push('after_scripts') @if (request()->ajax()) @endpush @endif
<script>
	if (typeof addBalanceEntry != 'function') {
        function addBalanceEntry(id){
            var url = "{{ url($crud->route) }}"+"/"+id+"/edit?type=add_balance";
            $('#modalEdit .modal-body').html('loading...');
            $.ajax({
                url: url,
                type: 'GET',
                typeData: 'json',
                success: function (data) {
                    $('#modalEdit .modal-body').html(data.html);
                    // Add a hidden input to the form to ensure type=add_balance is sent on submit
                    $('#modalEdit form').append('<input type="hidden" name="type" value="add_balance">');
                },
                error: function (xhr, status, error) {
                    console.error(xhr);
                    alert('An error occurred while loading the add balance form.');
                }
            });
        }
    }
</script>
@if (!request()->ajax()) @endpush @endif
