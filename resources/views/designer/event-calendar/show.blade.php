<div id="event-detail">

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title"><i class="ti-eye"></i> @lang('app.menu.appointments') @lang('app.details')</h4>
    </div>
    <div class="modal-body">
        {!! Form::open(['id'=>'updateEvent','class'=>'ajax-form','method'=>'GET']) !!}
        <div class="form-body">
            <div class="row">
                <div class="col-md-12 ">
                    <div class="form-group">
                        <label>@lang('modules.appointments.appointmentName')</label>
                        <p>
                            {{ ucfirst($event->event_name) }}
                        </p>
                    </div>
                </div>

            </div>

            <div class="row">
                <div class="col-xs-12 ">
                    <div class="form-group">
                        <label>@lang('app.description')</label>
                        <p>{{ ucfirst($event->description) }}</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-6 col-md-3 ">
                    <div class="form-group">
                        <label>@lang('modules.events.startOn')</label>
                        <p>{{ $event->start_date_time->format($global->date_format. ' - '.$global->time_format) }}</p>
                    </div>
                </div>
                <div class="col-xs-6 col-md-3">
                    <div class="form-group">
                        <label>@lang('modules.events.endOn')</label>
                        <p>{{ $event->end_date_time->format($global->date_format. ' - '.$global->time_format) }}</p>
                    </div>
                </div>

            </div>
        </div>
        {!! Form::close() !!}

    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-white waves-effect" data-dismiss="modal">Close</button>
        @if($event->event_type === 3 || $event->event_type === 4)
            <button type="button" class="btn btn-danger btn-outline delete-event waves-effect waves-light"><i class="fa fa-times"></i> @lang('app.delete')</button>
            <button type="button" class="btn btn-info save-event waves-effect waves-light"><i class="fa fa-edit"></i> @lang('app.edit')
            </button>
        @endif
    </div>

</div>

<script>

    $('.save-event').click(function () {
        $.easyAjax({
            url: '{{route('designer.events.edit', $event->id)}}',
            container: '#updateEvent',
            type: "GET",
            data: $('#updateEvent').serialize(),
            success: function (response) {
                if(response.status == 'success'){
                    $('#event-detail').html(response.view);
                }
            }
        })
    })

    $('.delete-event').click(function(){
        swal({
            title: "Are you sure?",
            text: "You will not be able to recover the deleted event!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, cancel please!",
            closeOnConfirm: true,
            closeOnCancel: true
        }, function(isConfirm){
            if (isConfirm) {

                var url = "{{ route('designer.events.destroy', $event->id) }}";

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {'_token': token, '_method': 'DELETE'},
                    success: function (response) {
                        if (response.status == "success") {
                            $('#eventDetailModal').modal('hide');
                            let event = $.CalendarApp.$calendarObj.getEventById('{{$event->id}}');
                            event.remove();
                        }
                    }
                });
            }
        });
    });


</script>