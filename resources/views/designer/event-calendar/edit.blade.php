

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title"><i class="icon-pencil"></i> @lang('app.edit') @lang('app.menu.appointments')</h4>
</div>
<div class="modal-body">
    {!! Form::open(['id'=>'updateEvent','class'=>'ajax-form','method'=>'PUT']) !!}
    <div class="form-body">
        <div class="row">
            @if($event->event_type == 1 || $event->event_type == 2)
                <div class="col-md-6 ">
                    <div class="form-group">
                        <label>@lang('modules.appointments.appointmentName')</label>
                        <input type="text" name="event_name" id="event_name" value="{{ $event->event_name }}" class="form-control">
                    </div>
                </div>
            @endif
            <div class="col-md-2 ">
                <div class="form-group">
                    <label>@lang('modules.sticky.colors')</label>
                    <select id="edit-colorselector" name="label_color">
                        <option value="bg-info" data-color="#5475ed" @if($event->label_color == 'bg-info') selected @endif>Blue</option>
                        <option value="bg-purple" data-color="#ab8ce4" @if($event->label_color == 'bg-purple') selected @endif>Purple</option>
                        <option value="bg-inverse" data-color="#4c5667" @if($event->label_color == 'bg-inverse') selected @endif>Grey</option>
                        <option value="bg-warning" data-color="#f1c411" @if($event->label_color == 'bg-warning') selected @endif>Yellow</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 ">
                <div class="form-group">
                    <label>@lang('app.description')</label>
                    <textarea name="description" id="description" class="form-control">{{ $event->description }}</textarea>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-6 col-md-3 ">
                <div class="form-group">
                    <label>@lang('modules.events.startOn')</label>
                    <input type="text" name="start_date" id="start_date" value="{{ $event->start_date_time->format('m/d/Y') }}" class="form-control">
                </div>
            </div>
            <div class="col-xs-5 col-md-3">
                <div class="input-group bootstrap-timepicker timepicker">
                    <label>&nbsp;</label>
                    <input type="text" name="start_time" id="start_time" value="{{ $event->start_date_time->format('h:i A') }}"
                           class="form-control">
                </div>
            </div>

            <div class="col-xs-6 col-md-3">
                <div class="form-group">
                    <label>@lang('modules.events.endOn')</label>
                    <input type="text" name="end_date" id="end_date" value="{{ $event->end_date_time->format('m/d/Y') }}" class="form-control">
                </div>
            </div>
            <div class="col-xs-5 col-md-3">
                <div class="input-group bootstrap-timepicker timepicker">
                    <label>&nbsp;</label>
                    <input type="text" name="end_time" id="end_time" value="{{ $event->end_date_time->format('h:i A') }}"
                           class="form-control">
                </div>
            </div>
        </div>
        @if($event->event_type == 1 || $event->event_type == 2)
            <div class="row">
                <div class="col-xs-12">
                    <div class="form-group">
                        <label class="control-label">@lang('modules.module.projects')</label>
                        <select class="select2 form-control" data-placeholder="@lang('modules.timeLogs.selectProject')" name="project_id" id="project_id" >
                            <option value=""></option>
                            @foreach($projects as $project)
                                <option @if($event->project_id == $project->id) selected @endif value="{{ $project->id }}">{{ ucwords($project->project_name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="form-group">
                        <label class="control-label">@lang('modules.tasks.assignTo')</label>
                        <select class="select2 form-control"
                                data-placeholder="@lang('modules.tasks.chooseAssignee')" name="user_id" id="user_id">
                            @foreach($designers as $designer)
                                <option @if ($event->attendee->user_id == $designer->id) selected @endif value="{{ $designer->id }}">{{ ucwords($designer->name) }} @if($designer->id == $user->id)
                                        (YOU) @endif</option>
                            @endforeach
                        </select>

                    </div>
                </div>
            </div>

            <div class="row">
                <div class="form-group">
                    <div class="col-xs-6">
                        <div class="checkbox checkbox-info">
                            <input id="edit-send-reminder" name="send_reminder" value="yes" @if($event->send_reminder == 'yes') checked @endif
                            type="checkbox">
                            <label for="edit-send-reminder">@lang('modules.tasks.reminder')</label>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <input type="hidden" name="user_id" id="user_id" value="{{ $event->attendee->user_id }}">
        @endif
        <div class="row" id="edit-reminder-fields" @if($event->send_reminder == 'no') style="display: none;" @endif>
            <div class="col-xs-6 col-md-3">
                <div class="form-group">
                    <label>@lang('modules.events.remindBefore')</label>
                    <input type="number" min="1" value="{{ $event->remind_time }}" name="remind_time" class="form-control">
                </div>
            </div>
            <div class="col-xs-6 col-md-3">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <select name="remind_type" id="" class="form-control">
                        <option @if ($event->remind_type == 'day')
                                selected
                                @endif value="day">@lang('app.day')</option>
                        <option @if ($event->remind_type == 'hour')
                                selected
                                @endif value="hour">@lang('app.hour')</option>
                        <option @if ($event->remind_type == 'minute')
                                selected
                                @endif value="minute">@lang('app.minute')</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" name="event_type" id="event_type" value="{{ $event->event_type }}">
    {!! Form::close() !!}

</div>
<div class="modal-footer">
    <button type="button" class="btn btn-white waves-effect" data-dismiss="modal">Close</button>
    <button type="button" class="btn btn-success save-event waves-effect waves-light">@lang('app.update')</button>
</div>



<script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/timepicker/bootstrap-timepicker.min.js') }}"></script>

<script src="{{ asset('js/cbpFWTabs.js') }}"></script>
<script src="{{ asset('plugins/bower_components/custom-select/custom-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/multiselect/js/jquery.multi-select.js') }}"></script>
<script src="{{ asset('plugins/bootstrap-colorselector/bootstrap-colorselector.min.js') }}"></script>

<script>
    jQuery('#start_date, #end_date').datepicker({
        autoclose: true,
        todayHighlight: true
    })

    $('#edit-colorselector').colorselector();

    $('#start_time, #end_time').timepicker();

    $(".select3").select2();


    $('.save-event').click(function () {
        $.easyAjax({
            url: '{{route('designer.events.update', $event->id)}}',
            container: '#modal-data-application',
            type: "PUT",
            data: $('#updateEvent').serialize(),
            success: function (response) {
                if(response.status == 'success'){
                    let org_event = $.CalendarApp.$calendarObj.getEventById(response.id);
                    $('#eventDetailModal').modal('hide');
                    $('#updateEvent')[0].reset();
                    let event = {
                        id: response.id,
                        resourceId: response.resource_id,
                        title: response.title,
                        start: response.start_date_time,
                        end:  response.end_date_time,
                        className: response.label_color,
                        event_type: response.type
                    }
                    org_event.remove();
                    $.CalendarApp.$calendarObj.addEvent(event);
                }
            }
        })
    })

    $('#edit-repeat-event').change(function () {
        if($(this).is(':checked')){
            $('#edit-repeat-fields').show();
        }
        else{
            $('#edit-repeat-fields').hide();
        }
    })

    $('#edit-send-reminder').change(function () {
        if($(this).is(':checked')){
            $('#edit-reminder-fields').show();
        }
        else{
            $('#edit-reminder-fields').hide();
        }
    })

    $('#show-attendees').click(function () {
        $('#edit-attendees').slideToggle();
    })

    $('.remove-attendee').click(function () {
        var row = $(this);
        var attendeeId = row.data('attendee-id');
        var url = '{{route('designer.events.removeAttendee')}}';

        $.easyAjax({
            url: url,
            type: "POST",
            data: { attendeeId: attendeeId, _token: '{{ csrf_token() }}'},
            success: function (response) {
                if(response.status == 'success'){
                    row.closest('.list-group-item').fadeOut();
                }
            }
        })
    })

</script>
