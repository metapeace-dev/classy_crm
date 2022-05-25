<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">@lang('modules.note.note')</h4>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <div class="table-responsive" style="max-height: 400px; overflow: scroll">
            <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>@lang('modules.note.note')</th>
                    <th>User</th>
                    <th>@lang('app.createdOn')</th>
                </tr>
                </thead>
                <tbody>
                @forelse($notes as $key => $note)
                    <tr id="note-{{ $note->id }}">
                        <td>{{ $key+1 }}</td>
                        <td>{{ ucwords($note->note) }}</td>
                        <td>{{ ucwords($note->user->name) }}</td>
                        <td>{{ $note->created_at }}</td>
{{--                        <td>--}}
{{--                            <a href="javascript:;" data-note-id="{{ $note->id }}" class="btn btn-sm btn-info btn-outline btn-rounded edit-note">@lang("app.edit")</a>--}}
{{--                            <a href="javascript:;" data-note-id="{{ $note->id }}" class="btn btn-sm btn-danger btn-outline btn-rounded delete-note">@lang("app.remove")</a>--}}
{{--                        </td>--}}
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">@lang('messages.noNote')</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <hr>
        {!! Form::open(['id'=>'createNote','class'=>'ajax-form','method'=>'POST']) !!}
        <div class="form-body">
            <div class="row">
                <div class="col-xs-12 ">
                    <div class="form-group">
                        <label>@lang('modules.note.note')</label>
                        <input type="text" name="note" id="note" class="form-control">
                        <input type="hidden" name="client_id" id="client_id" class="form-control">
                        <input type="hidden" name="lead_id" id="lead_id" class="form-control">
                    </div>
                </div>
            </div>
        </div>
        <div class="form-actions">
            <button type="button" id="save-note" class="btn btn-success"> <i class="fa fa-check"></i> @lang('app.save')</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>

<script>

    {{--$('.delete-note').click(function () {--}}
    {{--    var id = $(this).data('note-id');--}}
    {{--    var url = "{{ route('admin.note.destroy',':id') }}";--}}
    {{--    url = url.replace(':id', id);--}}

    {{--    var token = "{{ csrf_token() }}";--}}

    {{--    $.easyAjax({--}}
    {{--        type: 'POST',--}}
    {{--        url: url,--}}
    {{--        data: {'_token': token, '_method': 'DELETE'},--}}
    {{--        success: function (response) {--}}
    {{--            if (response.status == "success") {--}}
    {{--                $.unblockUI();--}}
    {{--                $('#note-'+id).fadeOut();--}}
    {{--            }--}}
    {{--        }--}}
    {{--    });--}}
    {{--});--}}

    {{--$('.edit-note').click(function () {--}}
    {{--    var id = $(this).data('note-id');--}}
    {{--    var url = "{{ route('admin.note.edit-note',':id') }}";--}}
    {{--    url = url.replace(':id', id);--}}
    {{--    $('#noteModal .modal-title').html("@lang('app.update') @lang('modules.lead.leadSource')");--}}
    {{--    $.ajaxModal('#noteModal', url);--}}
    {{--});--}}



    $('#save-note').click(function () {
        $('#client_id').val($('#note_client_id').val());
        $('#lead_id').val($('#note_lead_id').val());
        $.easyAjax({
            url: '{{route('admin.note.store-note')}}',
            container: '#createNote',
            type: "POST",
            data: $('#createNote').serialize(),
            success: function (response) {
                if(response.status == 'success'){
                    $('#noteModal').modal('hide');
                }
            }
        })
    });
</script>