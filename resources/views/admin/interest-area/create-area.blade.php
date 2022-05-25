<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">@lang('modules.interest_area.area_of_interest')</h4>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <div class="table-responsive" style="max-height: 400px; overflow: scroll">
            <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>@lang('modules.interest_area.type')</th>
                    <th>@lang('app.action')</th>
                </tr>
                </thead>
                <tbody>
                @forelse($areas as $key => $area)
                    <tr id="area-{{ $area->id }}">
                        <td>{{ $key+1 }}</td>
                        <td>{{ ucwords($area->type) }}</td>
                        <td>
                            <a href="javascript:;" data-area-id="{{ $area->id }}" class="btn btn-sm btn-info btn-outline btn-rounded edit-area">@lang("app.edit")</a>
                            <a href="javascript:;" data-area-id="{{ $area->id }}" class="btn btn-sm btn-danger btn-outline btn-rounded delete-area">@lang("app.remove")</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">@lang('messages.noInterestArea')</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <hr>
        {!! Form::open(['id'=>'createInterestAreaForm','class'=>'ajax-form','method'=>'POST']) !!}
        <div class="form-body">
            <div class="row">
                <div class="col-xs-12 ">
                    <div class="form-group">
                        <label>@lang('modules.interest_area.type')</label>
                        <input type="text" name="type" id="type" class="form-control">
                    </div>
                </div>
            </div>
        </div>
        <div class="form-actions">
            <button type="button" id="save-InterestArea" class="btn btn-success"> <i class="fa fa-check"></i> @lang('app.save')</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>

<script>

    $('.delete-area').click(function () {
        var id = $(this).data('area-id');
        var url = "{{ route('admin.interest-area.destroy',':id') }}";
        url = url.replace(':id', id);

        var token = "{{ csrf_token() }}";

        $.easyAjax({
            type: 'POST',
            url: url,
            data: {'_token': token, '_method': 'DELETE'},
            success: function (response) {
                if (response.status == "success") {
                    $.unblockUI();
                    $('#area-'+id).fadeOut();
                    $(".select2-multiple option[value='"+id+"']").remove();
                }
            }
        });
    });

    $('.edit-area').click(function () {
        var id = $(this).data('area-id');
        var url = "{{ route('admin.interest-area.edit-area',':id') }}";
        url = url.replace(':id', id);
        $('#interestAreaModal .modal-title').html("@lang('app.update') @lang('modules.interest_area.area_of_interest')");
        $.ajaxModal('#interestAreaModal', url);
    });



    $('#save-InterestArea').click(function () {
        $.easyAjax({
            url: '{{route('admin.interest-area.store-area')}}',
            container: '#createInterestAreaForm',
            type: "POST",
            data: $('#createInterestAreaForm').serialize(),
            success: function (response) {
                if(response.status == 'success'){
                    var options = ['<option value=""></option>'];
                    var rData = [];
                    rData = response.data;
                    $.each(rData, function( index, value ) {
                        var selectData = '';
                        selectData = '<option value="'+value.id+'">'+value.type+'</option>';
                        options.push(selectData);
                    });
                    $('.select2-multiple').empty();
                    $('.select2-multiple').html(options);
                    $(".select2-multiple").select2({
                        formatNoMatches: function () {
                            return "{{ __('messages.noRecordFound') }}";
                        }
                    });
                    $('#interestAreaModal').modal('hide');
                }
            }
        })
    });
</script>