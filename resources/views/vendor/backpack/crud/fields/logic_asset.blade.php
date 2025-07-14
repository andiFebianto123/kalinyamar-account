@php
	// if not otherwise specified, the hidden input should take up no space in the form
  $field['wrapper'] = $field['wrapper'] ?? $field['wrapperAttributes'] ?? [];
  $field['wrapper']['class'] = $field['wrapper']['class'] ?? "hidden";
@endphp

{{-- hidden input --}}
@include('crud::fields.inc.wrapper_start')
  <input
  	type="hidden"
    name="{{ $field['name'] }}"
    value="{{ old_empty_or_null($field['name'], '') ??  $field['value'] ?? $field['default'] ?? '' }}"
    @include('crud::fields.inc.attributes')
  	>
@include('crud::fields.inc.wrapper_end')

@push('crud_fields_scripts')
    <script>
        function getInclusiveMonthDiff(fromDateStr) {
            const fromDate = new Date(fromDateStr);
            const targetYear = new Date().getFullYear(); // Tahun sekarang
            const toDate = new Date(targetYear, 11, 1);   // Desember (11 = Desember)

            const months =
                (toDate.getFullYear() - fromDate.getFullYear()) * 12 +
                (toDate.getMonth() - fromDate.getMonth()) + 1; // +1 untuk inklusif

            return months;
        }
        SIAOPS.setAttribute('logic_asset', function(){
            return {
                form_type : "{{ $crud->getActionMethod() }}",
                logicFormula: function(){
                    var form = (this.form_type == 'create') ? '#form-create' : '#form-edit';
                    var price_acquisition = getInputNumber(form+' #price_acquisition');
                    var ue = getInputNumber(form+' input[name="economic_age"]');
                    var period = getInclusiveMonthDiff($(form+ ' input[name="year_acquisition"]').val());

                    console.log(period);

                    var penyusutan_per_tahun = (ue == 0) ? 0 : (price_acquisition - 0) / ue;
                    setInputNumber(form+ ' #price_rate_per_year_masked', penyusutan_per_tahun);

                    var tarif = (penyusutan_per_tahun / price_acquisition) * 100;
                    $(form+ ' input[name="tarif"]').val(tarif);

                    var tarif_penyusutan_tahun_ini = price_acquisition - penyusutan_per_tahun;

                    setInputNumber(form + ' #this_year_depreciation_rate_masked', tarif_penyusutan_tahun_ini);
                    var akumulasi_penyusutan_desember_tahun_ini = Math.round((penyusutan_per_tahun / 12 * period) / 10) * 10;

                    setInputNumber(form+ ' #accumulated_until_december_this_year_masked', akumulasi_penyusutan_desember_tahun_ini);

                    var nilai_buku_des_tahun_ini = price_acquisition - akumulasi_penyusutan_desember_tahun_ini;
                    setInputNumber(form+ ' #book_value_this_december_masked', nilai_buku_des_tahun_ini);
                    // console.log(penyusutan_per_tahun, period);

                },
                load: function(){
                    var instance = this;
                    var form = (this.form_type == 'create') ? '#form-create' : '#form-edit';
                    $(form+' #price_acquisition_masked').off('keyup').on('keyup', function(){
                        instance.logicFormula();
                    });
                    $(form+' input[name="economic_age"]').off('keyup').on('keyup', function(){
                        instance.logicFormula();
                    });
                    // $(form+ ' #year_acquisition').on('changeDate', function(){
                    //     instance.logicFormula();
                    // });
                }
            }
        });
        SIAOPS.getAttribute('logic_asset').load();
    </script>
@endpush
