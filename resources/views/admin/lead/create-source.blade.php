<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">@lang('modules.lead.leadSource')</h4>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <div class="table-responsive" style="max-height: 400px; overflow: scroll">
            <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>@lang('modules.lead.sourceName')</th>
                    <th>@lang('app.action')</th>
                </tr>
                </thead>
                <tbody>
                @forelse($sources as $key => $source)
                    <tr id="src-{{ $source->id }}">
                        <td>{{ $key+1 }}</td>
                        <td>{{ ucwords($source->name) }}</td>
                        <td>
                            <a href="javascript:;" data-src-id="{{ $source->id }}" class="btn btn-sm btn-info btn-outline btn-rounded edit-category">@lang("app.edit")</a>
                            <a href="javascript:;" data-src-id="{{ $source->id }}" class="btn btn-sm btn-danger btn-outline btn-rounded delete-category">@lang("app.remove")</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">@lang('messages.noLeadSource')</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <hr>
        {!! Form::open(['id'=>'createLeadSourceForm','class'=>'ajax-form','method'=>'POST']) !!}
        <div class="form-body">
            <div class="row">
                <div class="col-xs-12 ">
                    <div class="form-group">
                        <label>@lang('modules.lead.sourceName')</label>
                        <input type="text" name="name" id="name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>@lang('modules.offlinePayment.description')</label>
                        <input type="text" name="description" id="description" class="form-control">
                    </div>
                </div>
            </div>
        </div>
        <div class="form-actions">
            <button type="button" id="save-leadSource" class="btn btn-success"> <i class="fa fa-check"></i> @lang('app.save')</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>

<script>

    $('.delete-category').click(function () {
        var id = $(this).data('src-id');
        var url = "{{ route('admin.leadSource.destroy',':id') }}";
        url = url.replace(':id', id);

        var token = "{{ csrf_token() }}";

        $.easyAjax({
            type: 'POST',
            url: url,
            data: {'_token': token, '_method': 'DELETE'},
            success: function (response) {
                if (response.status == "success") {
                    $.unblockUI();
                    $('#src-'+id).fadeOut();
                    $("#source option[value='"+id+"']").remove();
                }
            }
        });
    });

    $('.edit-category').click(function () {
        var id = $(this).data('src-id');
        var url = "{{ route('admin.leadSource.edit-src',':id') }}";
        url = url.replace(':id', id);
        $('#leadSourceModal .modal-title').html("@lang('app.update') @lang('modules.lead.leadSource')");
        $.ajaxModal('#leadSourceModal', url);
    });



    $('#save-leadSource').click(function () {
        $.easyAjax({
            url: '{{route('admin.leadSource.store-src')}}',
            container: '#createLeadSourceForm',
            type: "POST",
            data: $('#createLeadSourceForm').serialize(),
            success: function (response) {
                if(response.status == 'success'){
                    console.log(response.data);
                    var options = [];
                    var rData = [];
                    rData = response.data;
                    $.each(rData, function( index, value ) {
                        var selectData = '';
                        selectData = '<option value="'+value.id+'">'+value.name+'</option>';
                        options.push(selectData);
                    });

                    $('#source').html(options);
                    $('#source').selectpicker('refresh');
                    $('#leadSourceModal').modal('hide');
                }
            }
        })
    });
</script>