<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle list-user">
        <thead class="table-light">
            <tr>
                <th scope="col" style="width: 60px;">#</th>
                <th scope="col">Nama</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Andi Febian</td>
            </tr>
            <tr>
                <td>2</td>
                <td>Budi Santoso</td>
            </tr>
            <tr>
                <td>3</td>
                <td>Citra Lestari</td>
            </tr>
        </tbody>
    </table>
</div>

@push('after_scripts')
    <script>
        $(function(){
            $(document).on('click', '.user_count', function() {
                let roleId = $(this).data('id-role');
                var modal_id = "#modal_list_user";
                $('#modal_list_user .list-user tbody').html('');
                $('#modal_list_user').modal('show');
                $.ajax({
                    url: '{{ url($crud->route) }}/get-user-role',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        role_id: roleId
                    },
                    success: function(response) {
                        var result = response;
                        var i = 1;
                        $.each(result, function(index, value) {
                            $('#modal_list_user .list-user tbody').append(
                                '<tr>' +
                                    '<td>' + i + '</td>' +
                                    '<td>' + value.name + '</td>' +
                                '</tr>'
                            );
                            i++;
                        });
                    }
                }).fail(function() {
                    console.log('error load user');
                })
            });
        })
    </script>
@endpush
