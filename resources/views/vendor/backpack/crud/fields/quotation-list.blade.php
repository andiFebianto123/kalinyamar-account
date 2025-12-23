{{-- text input --}}

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')

    @if(isset($field['prefix']) || isset($field['suffix'])) <div class="input-group"> @endif
        @if(isset($field['prefix'])) <span class="input-group-text">{!! $field['prefix'] !!}</span> @endif
        <?php
            // dd($field['value']);
        ?>
        <div class="table-responsive">
            <table class="table table-borderless" style="width: 1400px;">
                <thead>
                    <tr>
                        <th>Checklist</th>
                        <th>{{trans('backpack::crud.quotation.column.no_rfq.label')}}</th>
                        <th>{{trans('backpack::crud.quotation.column.name_project.label')}}</th>
                        <th>{{trans('backpack::crud.quotation.column.rab.label')}}</th>
                        <th>{{trans('backpack::crud.quotation.column.rap.label')}}</th>
                        <th>{{trans('backpack::crud.quotation.column.client_id.label')}}</th>
                        <th>{{trans('backpack::crud.quotation.column.pic.label')}}</th>
                        <th>{{trans('backpack::crud.quotation.column.user.label')}}</th>
                        <th>{{trans('backpack::crud.quotation.column.closing_date.label')}}</th>
                        <th>{{trans('backpack::crud.quotation.column.status.label')}}</th>
                        <th>{{trans('backpack::crud.quotation.column.information.label')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($field['value'] as $quotation)
                        <?php
                            $id = str_replace("\n", '', $quotation[10]);
                        ?>
                        <tr>
                            <td><input type="checkbox" name="quotation[]" class="form-check-input" value="{!! $id !!}"></td>
                            <td>{!! $quotation[0] !!}</td>
                            <td>{!! $quotation[1] !!}</td>
                            <td>{!! $quotation[2] !!}</td>
                            <td>{!! $quotation[3] !!}</td>
                            <td>{!! $quotation[4] !!}</td>
                            <td>{!! $quotation[5] !!}</td>
                            <td>{!! $quotation[6] !!}</td>
                            <td>{!! $quotation[7] !!}</td>
                            <td>{!! $quotation[8] !!}</td>
                            <td>{!! $quotation[9] !!}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if(isset($field['suffix'])) <span class="input-group-text">{!! $field['suffix'] !!}</span> @endif
    @if(isset($field['prefix']) || isset($field['suffix'])) </div> @endif

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')
