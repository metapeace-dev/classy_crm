<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">@lang('app.update') @lang('modules.lead.leadSource')</h4>
</div>
<div class="modal-body">
    <div class="portlet-body">

        {!! Form::open(['id'=>'editLeadSource','class'=>'ajax-form','method'=>'POST']) !!}
        <div class="form-body">
            <div class="row">
                <div class="col-xs-12 ">
                    <div class="form-group">
                        <label>@lang('modules.lead.leadSource')</label>
                        <input type="text" name="name" id="name" value="{{ $source->name }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>@lang('modules.offlinePayment.description')</label>
                        <input type="text" name="description" id="description" value="{{ $source->description }}" class="form-control">
                    </div>
                </div>
            </div>
        </div>
        <div class="form-actions">
            <button type="button" id="save-group" class="btn btn-success"> <i class="fa fa-check"></i> @lang('app.save')</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>

<script>

    $('#editLeadSource').on('submit', function(e) {
        return false;
    })

    $('#save-group').click(function () {
        $.easyAjax({
            url: '{{route('admin.leadSource.update-src', $source->id)}}',
            container: '#editLeadSource',
            type: "POST",
            data: $('#editLeadSource').serialize(),
            success: function (response) {
                if(response.status == 'success'){
                    $("#source option[value='{{$source->id}}']").text($('#editLeadSource #name').val());
                    $('#source').selectpicker('refresh');
                    $('#leadSourceModal').modal('hide');
                    $('.modal-backdrop').remove();
                }
            }
        })
    });
</script>