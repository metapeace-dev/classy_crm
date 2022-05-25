<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">@lang('app.update') @lang('modules.interest_area.area_of_interest')</h4>
</div>
<div class="modal-body">
    <div class="portlet-body">

        {!! Form::open(['id'=>'editInterestArea','class'=>'ajax-form','method'=>'POST']) !!}
        <div class="form-body">
            <div class="row">
                <div class="col-xs-12 ">
                    <div class="form-group">
                        <label>@lang('modules.interest_area.type')</label>
                        <input type="text" name="type" id="type" value="{{ $area->type }}" class="form-control">
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

    $('#editInterestArea').on('submit', function(e) {
        return false;
    })

    $('#save-group').click(function () {
        $.easyAjax({
            url: '{{route('admin.interest-area.update-area', $area->id)}}',
            container: '#editInterestArea',
            type: "POST",
            data: $('#editInterestArea').serialize(),
            success: function (response) {
                if(response.status == 'success'){
                    $(".select2-multiple option[value='"+ '{{$area->id}}' +"']").text($('#type').val());
                    $('.select2-multiple').select2().trigger('change');
                    $('#interestAreaModal').modal('hide');
                    $('.modal-backdrop').remove();
                }
            }
        })
    });
</script>