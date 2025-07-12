<div class="row">
    <div class="form-group col-sm-2 mb-3" element="div" bp-field-wrapper="true" bp-field-name="type" bp-field-type="select_from_array" bp-section="crud-field">
        <h5>{{trans('backpack::crud.balance_sheet.filters.year.label')}}</h5>
        <select name="year" class="form-control form-select">
            <option selected>{{trans('backpack::crud.balance_sheet.filters.year.placeholder')}}</option>
        </select>
    </div>
    <div class="form-group col-sm-2 mb-3" element="div" bp-field-wrapper="true" bp-field-name="type" bp-field-type="select_from_array" bp-section="crud-field">
        <h5>{{trans('backpack::crud.balance_sheet.filters.quarter.label')}}</h5>
        <select name="quarter" class="form-control form-select">
            <option selected>{{trans('backpack::crud.balance_sheet.filters.quarter.placeholder')}}</option>
        </select>
    </div>
</div>
