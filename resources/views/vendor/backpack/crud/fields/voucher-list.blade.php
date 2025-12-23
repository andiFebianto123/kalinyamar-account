{{-- text input --}}
@php
    $field_language = isset($field['date_picker_options']['language']) ? $field['date_picker_options']['language'] : \App::getLocale();
@endphp
@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')

    @if(isset($field['prefix']) || isset($field['suffix'])) <div class="input-group"> @endif
        @if(isset($field['prefix'])) <span class="input-group-text">{!! $field['prefix'] !!}</span> @endif
        <?php
            // dd($field['value']);
        ?>
        <div class="table-responsive">
            <table class="table table-borderless" style="width: 1300px;">
                <thead>
                    <tr>
                        <th>Checklist</th>
                        <th>{{trans('backpack::crud.voucher.column.voucher.no_voucher.label')}}</th>
                        <th>{{trans('backpack::crud.voucher.column.voucher.date_voucher.label')}}</th>
                        <th>{{trans('backpack::crud.voucher.column.voucher.bussines_entity_name.label')}}</th>
                        <th>{{trans('backpack::crud.voucher.column.voucher.bill_date.label')}}</th>
                        <th>{{trans('backpack::crud.voucher.column.voucher.no_po_spk.label')}}</th>
                        <th>{{trans('backpack::crud.voucher.column.voucher.payment_transfer.label_2')}}</th>
                        <th>{{trans('backpack::crud.voucher.column.voucher.due_date.label_2')}}</th>
                        <th>{{trans('backpack::crud.voucher.column.voucher.factur_status.label')}}</th>
                        <th>{{trans('backpack::crud.voucher.column.voucher.payment_type.label')}}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($field['value'] as $voucher)
                        <tr>
                            <td><input type="checkbox" name="voucher[]" class="form-check-input" value="{{$voucher->id}}"></td>
                            <td>{{$voucher->no_voucher}}</td>
                            <td>{{$voucher->date_voucher_str}}</td>
                            <td>{{$voucher?->subkon?->name}}</td>
                            <td>{{$voucher->bill_date_str}}</td>
                            <td>{{ ($voucher?->reference_type == 'App\Models\Spk') ? $voucher?->reference?->no_spk : $voucher?->reference?->po_number}}</td>
                            <td>{{$voucher->payment_transfer_str}}</td>
                            <td>{{$voucher->due_date_str}}</td>
                            <td>{{$voucher->factur_status}}</td>
                            <td>{{$voucher->payment_type}}</td>
                            <td>
                                @if ($field['name'] == 'voucher_payment')
                                    <a href="javascript:void(0)" onclick="goPayment(this)" bp-button="update" class="btn btn-sm btn-primary">
                                        <i class="la la-send"></i>
                                    </a>
                                @endif
                            </td>
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

    <div class="modal fade" id="modal2" style="margin-top: 20px;" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal2Label">{{trans('backpack::crud.title_modal_voucher_payment')}}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group col-md-12" element="div" bp-field-wrapper="true" bp-field-name="no_payment" bp-field-type="text" bp-section="crud-field">
                            <label>{{trans('backpack::crud.voucher.field.no_voucher.label')}}</label>
                            <div id="no_voucher_payment"></div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group col-md-12" element="div" bp-field-wrapper="true" bp-field-name="no_payment" bp-field-type="text" bp-section="crud-field">
                            <label>{{trans('backpack::crud.voucher.field.payment_transfer.label')}}</label>
                            <div id="price_payment"></div>
                            <input type="hidden" name="id_voucher" id="id_voucher_payment">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group col-md-12 required" element="div" bp-field-wrapper="true" bp-field-name="no_payment" bp-field-type="text" bp-section="crud-field">
                            <input type="hidden" class="form-control" name="date_payment">
                            <label>{{trans('backpack::crud.voucher_payment.field.payment_date.label')}}</label>
                            <div class="input-group date">
                                <input 
                                    type="text" 
                                    id="date_payment" 
                                    class="form-control">
                                <span class="input-group-text" id="basic-addon1"><span class="la la-calendar"></span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="submit_manual_voucher_payment">Kirim Pembayaran</button>
            </div>
            </div>
        </div>
    </div>
@include('crud::fields.inc.wrapper_end')

@push('crud_fields_scripts')
    <script>
        SIAOPS.loadCSS("{{ asset('packages/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css') }}");
    </script>
    <script>
        SIAOPS.loadScript([
                "{{ asset('packages/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"
        ], {
            async: false, // Load secara synchronous
            defer: true,
        });
    </script>
    @if ($field_language !== 'en')
        {{-- <script charset="UTF-8" src="{{ asset('packages/bootstrap-datepicker/dist/locales/bootstrap-datepicker.'.$field_language.'.min.js') }}"></script> --}}
        <script>
            SIAOPS.loadScript([
                "{{ asset('packages/bootstrap-datepicker/dist/locales/bootstrap-datepicker.'.$field_language.'.min.js') }}"
            ], {
                async: false, // Load secara synchronous
                defer: true,
            });
        </script>
    @endif
    <script>
        var dateFormat=function(){var a=/d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,b=/\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,c=/[^-+\dA-Z]/g,d=function(a,b){for(a=String(a),b=b||2;a.length<b;)a="0"+a;return a};return function(e,f,g){var h=dateFormat;if(1!=arguments.length||"[object String]"!=Object.prototype.toString.call(e)||/\d/.test(e)||(f=e,e=void 0),e=e?new Date(e):new Date,isNaN(e))throw SyntaxError("invalid date");f=String(h.masks[f]||f||h.masks.default),"UTC:"==f.slice(0,4)&&(f=f.slice(4),g=!0);var i=g?"getUTC":"get",j=e[i+"Date"](),k=e[i+"Day"](),l=e[i+"Month"](),m=e[i+"FullYear"](),n=e[i+"Hours"](),o=e[i+"Minutes"](),p=e[i+"Seconds"](),q=e[i+"Milliseconds"](),r=g?0:e.getTimezoneOffset(),s={d:j,dd:d(j),ddd:h.i18n.dayNames[k],dddd:h.i18n.dayNames[k+7],m:l+1,mm:d(l+1),mmm:h.i18n.monthNames[l],mmmm:h.i18n.monthNames[l+12],yy:String(m).slice(2),yyyy:m,h:n%12||12,hh:d(n%12||12),H:n,HH:d(n),M:o,MM:d(o),s:p,ss:d(p),l:d(q,3),L:d(q>99?Math.round(q/10):q),t:n<12?"a":"p",tt:n<12?"am":"pm",T:n<12?"A":"P",TT:n<12?"AM":"PM",Z:g?"UTC":(String(e).match(b)||[""]).pop().replace(c,""),o:(r>0?"-":"+")+d(100*Math.floor(Math.abs(r)/60)+Math.abs(r)%60,4),S:["th","st","nd","rd"][j%10>3?0:(j%100-j%10!=10)*j%10]};return f.replace(a,function(a){return a in s?s[a]:a.slice(1,a.length-1)})}}();dateFormat.masks={default:"ddd mmm dd yyyy HH:MM:ss",shortDate:"m/d/yy",mediumDate:"mmm d, yyyy",longDate:"mmmm d, yyyy",fullDate:"dddd, mmmm d, yyyy",shortTime:"h:MM TT",mediumTime:"h:MM:ss TT",longTime:"h:MM:ss TT Z",isoDate:"yyyy-mm-dd",isoTime:"HH:MM:ss",isoDateTime:"yyyy-mm-dd'T'HH:MM:ss",isoUtcDateTime:"UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"},dateFormat.i18n={dayNames:["Sun","Mon","Tue","Wed","Thu","Fri","Sat","Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],monthNames:["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec","January","February","March","April","May","June","July","August","September","October","November","December"]},Date.prototype.format=function(a,b){return dateFormat(this,a,b)};
        async function bpFieldInitDatePickerElement(element) {
            await new Promise((resolve) => {
                setTimeout(() => {
                    resolve(1);
                }, 100);
            });

            var $fake = element;

            if (jQuery.ui) {
                var datepicker = $.fn.datepicker.noConflict();
                $.fn.bootstrapDP = datepicker;
            } else {
                $.fn.bootstrapDP = $.fn.datepicker;
            }

            $field = $fake.closest('.input-group').parent().children('input[type="hidden"]');

            $customConfig = $.extend({
                format: 'dd/mm/yyyy',
                autoclose: true,
            }, $fake.data('bs-datepicker'));
            $picker = $fake.bootstrapDP($customConfig);

            var $existingVal = $field.val();

            if( $existingVal && $existingVal.length ){
                var parts = $existingVal.split('-');
                var year = parts[0];
                var month = parts[1] - 1; // Date constructor expects a zero-indexed month
                var day = parts[2];
                preparedDate = new Date(year, month, day).format($customConfig.format);
                $fake.val(preparedDate);
                $picker.bootstrapDP('update', preparedDate);
            }

            $picker.on('show hide change', function(e){
                if( e.date ){
                    var sqlDate = e.format('yyyy-mm-dd');
                } else {
                    try {
                        var sqlDate = $fake.val();

                        if( $customConfig.format === 'dd/mm/yyyy' ){
                            sqlDate = new Date(sqlDate.split('/')[2], sqlDate.split('/')[1] - 1, sqlDate.split('/')[0]).format('yyyy-mm-dd');
                        }
                    } catch(e){
                        if( $fake.val() ){
                                new Noty({
                                    type: "error",
                                    text: "<strong>Whoops!</strong><br>Sorry we did not recognise that date format, please make sure it uses a yyyy mm dd combination"
                                }).show();
                            }
                        }
                }
                $field = $fake.closest('.input-group').parent().children('input[type="hidden"]');
                $field.val(sqlDate);
            });
        }
        var field_date = $('#date_payment');
        bpFieldInitDatePickerElement(field_date);
    </script>
    <script>
        function goPayment(e){
            // console.log($(e).parent().parent())
            var voucher = $(e).parent().parent().children().eq(1).text();
            var price = $(e).parent().parent().children().eq(6).text();
            var voucher_id = $(e).parent().parent().children().eq(0).children(":first");
            let modal2 = new bootstrap.Modal(document.getElementById('modal2'), {
                backdrop: false
            });
            modal2.show();

            $('#no_voucher_payment').html(voucher);
            $('#price_payment').html(price);
            $('#id_voucher_payment').val(voucher_id.val());
            $('#date_payment').val("");
            $('input[name="date_payment"]').val("");

            let backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show second-backdrop';
            document.body.appendChild(backdrop);
            document.getElementById('modal2').addEventListener('hidden.bs.modal', function () {
                backdrop.remove();
            }, { once: true });
        }

        $('#submit_manual_voucher_payment').on('click', function(e){
            setLoadingButton("#submit_manual_voucher_payment", true);

            $.ajax({
                url: "{{ url($crud->route.'/single-store') }}",
                type: 'POST',
                data: {
                    id: $('#id_voucher_payment').val(),
                    date: $('input[name="date_payment"]').val(),
                },
                // typeData: 'json',
                success: function (data) {
                    setLoadingButton("#submit_manual_voucher_payment", false);
                    if(data.success){
                        swal({
                            title: "Success",
                            text: "{!! trans('backpack::crud.insert_success') !!}",
                            icon: "success",
                            timer: 4000,
                            buttons: false,
                        });
                        $('#modal2').modal('hide');
                        hideModal('modalCreate');
                        hideModal('modal2');
                        if(window.crud.table){
                            window.crud.table.ajax.reload();
                        }
                        if(data.events){
                            forEachFlexible(data.events, function(eventname, data){
                                eventEmitter.emit(eventname, data);
                            });
                        }
                    }else{
                        swal({
                            title: "{!! trans('backpack::crud.delete_confirmation_not_title') !!}",
                            text: data.error,
                            icon: "error",
                            timer: 4000,
                            buttons: false,
                        });
                    }
                },
                error: function (xhr, status, error) {
                    // console.error(xhr);
                    setLoadingButton("#submit_manual_voucher_payment", false);
                    // errorShowMessage('form-create', xhr.responseJSON.errors);
                    console.log(xhr.responseJSON.errors);
                    swal({
                        title: "{!! trans('backpack::crud.ajax_error_title') !!}",
                        text: "{{ trans('backpack::crud.insert_error') }}",
                        icon: "error",
                        timer: 4000,
                        buttons: false,
                    });
                }
            });
        });
    </script>
@endpush
