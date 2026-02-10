@if($crud->hasAccess('list'))
    <a href="javascript:void(0)" 
       onclick="viewLedger({{ $entry->id_ }}, '{{ $entry->code_ }} - {{ $entry->name_ }}')" 
       class="btn btn-sm btn-dark" 
       title="Lihat Buku Besar">
        <i class="la la-eye"></i>
    </a>
@endif
