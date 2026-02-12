@php
    $is_approver = $crud->get('is_approver') ?? false;
    $has_bulk_delete = $crud->get('has_bulk_delete') ?? false;
@endphp

<div class="d-inline-flex align-items-center gap-2 me-1" id="bulk-actions-wrapper" style="vertical-align: middle;">
    <div class="d-inline-flex align-items-center">
        <input type="checkbox" id="bulk-select-all" class="bulk-checkbox-header" title="{{ trans('backpack::crud.select_all') }}" style="width:16px;height:16px;cursor:pointer;accent-color:#4361ee;" />
        <label for="bulk-select-all" style="font-size:13px; cursor:pointer; margin-left:4px; margin-bottom:0; white-space:nowrap;">{{ trans('backpack::crud.select_all') }}</label>
    </div>
    <span id="bulk-selected-count" class="badge bg-secondary d-none" style="font-size:12px;">0 {{ trans('backpack::crud.select_entries') }}</span>
    @if($is_approver)
    <button type="button" id="btn-bulk-approve" class="btn btn-primary d-none">
        <i class="la la-check-double"></i> Approve
    </button>
    @endif
    @if($has_bulk_delete)
    <button type="button" id="btn-bulk-delete" class="btn btn-danger d-none">
        <i class="la la-trash"></i> {{ trans("backpack::crud.delete") }}
    </button>
    @endif
</div>
