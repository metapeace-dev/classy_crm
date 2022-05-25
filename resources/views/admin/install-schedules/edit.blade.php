

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title"><i class="icon-pencil"></i> @lang('app.edit') @lang('app.menu.installSchedules')</h4>
</div>
<div class="modal-body">
    {!! Form::open(['id'=>'updateEvent','class'=>'ajax-form','method'=>'PUT']) !!}
    <div class="form-body">
        <div class="row">
            <div class="col-md-6 ">
                <div class="form-group">
                    <label>@lang('modules.install_schedules.scheduleName')</label>
                    <input type="text" name="schedule_name" id="schedule_name" value="{{ $schedule->schedule_name }}" class="form-control">
                </div>
            </div>
{{--            <div class="col-md-2 ">--}}
{{--                <div class="form-group">--}}
{{--                    <label>@lang('modules.sticky.colors')</label>--}}
{{--                    <select id="edit-colorselector" name="label_color">--}}
{{--                        <option value="bg-info" data-color="#5475ed" @if($schedule->label_color == 'bg-info') selected @endif>Blue</option>--}}
{{--                        <option value="bg-purple" data-color="#ab8ce4" @if($schedule->label_color == 'bg-purple') selected @endif>Purple</option>--}}
{{--                        <option value="bg-inverse" data-color="#4c5667" @if($schedule->label_color == 'bg-inverse') selected @endif>Grey</option>--}}
{{--                        <option value="bg-warning" data-color="#f1c411" @if($schedule->label_color == 'bg-warning') selected @endif>Yellow</option>--}}
{{--                    </select>--}}
{{--                </div>--}}
{{--            </div>--}}
        </div>

        <div class="row">
            <div class="col-xs-12 ">
                <div class="form-group">
                    <label>@lang('app.description')</label>
                    <textarea name="description" id="description" class="form-control">{{ $schedule->description }}</textarea>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-6 col-md-3 ">
                <div class="form-group">
                    <label>@lang('modules.install_schedules.startOn')</label>
                    <input type="text" name="start_date" id="start_date" value="{{ $schedule->start_date_time->format('m/d/Y') }}" class="form-control">
                </div>
            </div>
            <div class="col-xs-5 col-md-3">
                <div class="input-group bootstrap-timepicker timepicker">
                    <label>&nbsp;</label>
                    <input type="text" name="start_time" id="start_time" value="{{ $schedule->start_date_time->format('h:i A') }}"
                           class="form-control">
                </div>
            </div>

            <div class="col-xs-6 col-md-3">
                <div class="form-group">
                    <label>@lang('modules.install_schedules.endOn')</label>
                    <input type="text" name="end_date" id="end_date" value="{{ $schedule->end_date_time->format('m/d/Y') }}" class="form-control">
                </div>
            </div>
            <div class="col-xs-5 col-md-3">
                <div class="input-group bootstrap-timepicker timepicker">
                    <label>&nbsp;</label>
                    <input type="text" name="end_time" id="end_time" value="{{ $schedule->end_date_time->format('h:i A') }}"
                           class="form-control">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="form-group">
                    <label class="control-label">@lang('modules.install_schedules.scheduleType')</label>
                    <select class="select2 form-control"
                            data-placeholder="@lang('modules.install_schedules.scheduleType')" name="type_id" id="type_id">
                        @foreach($schedule_types as $type)
                            <option @if($schedule->type_id == $type->id) selected @endif value="{{ $type->id }}">{{ ucwords($type->type) }}</option>
                        @endforeach
                    </select>

                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="form-group">
                    <label class="control-label">@lang('modules.install_schedules.status')</label>
                    <select class="select3 form-control"
                            data-placeholder="@lang('modules.install_schedules.status')" name="status" id="status">
                        <option @if ($schedule->status == 'incomplete') selected @endif value="incomplete">In Complete</option>
                        <option @if ($schedule->status == 'complete') selected @endif value="complete">Complete</option>
                    </select>

                </div>
            </div>
        </div>

        <div class="row" id="project_select" @if($schedule->type_id == 2) style="display: none" @endif>
            <div class="col-xs-12">
                <div class="form-group">
                    <label>@lang('modules.install_schedules.project')</label>
                    <select class="select3 form-control" data-placeholder="@lang('modules.timeLogs.selectProject')" name="project_id" id="project_id" >
                        <option value=""></option>
                        @foreach($projects as $project)
                            <option @if($schedule->project_id == $project->id) selected @endif value="{{ $project->id }}">{{ ucwords($project->project_name) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row" id="attendees_container" @if($schedule->type_id == 2) style="display: none" @endif>
            <div class="col-xs-12">
                <a href="javascript:;" id="show-attendees" class="text-info"><i class="icon-people"></i> @lang('modules.events.viewAttendees') ({{ count($schedule->attendees ?? []) }})</a>
            </div>
            <div class="col-xs-12"  id="edit-attendees" style="display: none;">
                <div class="col-xs-12" style="max-height: 210px; overflow-y: auto;">
                    <ul class="list-group">
                        @if(!empty($schedule->attendees))
                            @foreach($schedule->attendees as $emp)
                                <li class="list-group-item">{{ ucwords($emp->user->name) }}
                                    <a href="javascript:;" data-attendee-id="{{ $emp->id }}" data-project-id="{{$schedule->project_id}}" class="btn btn-xs btn-rounded btn-danger pull-right remove-attendee"><i class="fa fa-times"></i> @lang('app.remove')</a>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
                <div class="form-group">
                    <label class="col-xs-3 m-t-10">@lang('modules.events.addAttendees')</label>
                </div>
                <div class="form-group">
                    <select class="select3 m-b-10 select2-multiple " multiple="multiple"
                            data-placeholder="Choose Members" name="user_id[]">
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ ucwords($emp->name) }} @if($emp->id == $user->id)
                                    (YOU) @endif</option>
                        @endforeach
                    </select>

                </div>
            </div>

        </div>

        <div class="row" id="project_details" @if($schedule->type_id == 2) style="display: none" @endif>
            <label style="margin-left:10px;">@lang('modules.install_schedules.projectInfo')</label>
            <div class="col-xs-12">
                <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                        <label>@lang('modules.install_schedules.projectID')</label>
                        <input type="text" id="project_info_id" class="form-control" autocomplete="none" disabled="disabled">
                    </div>
                </div>
                <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                        <label>@lang('modules.install_schedules.clientLastName')</label>
                        <input type="text" id="project_client_last_name" class="form-control" autocomplete="none" disabled="disabled">
                    </div>
                </div>

                <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                        <label>@lang('modules.projects.city')</label>
                        <input type="text" id="project_city" class="form-control" autocomplete="none" disabled="disabled">
                    </div>
                </div>
                <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                        <label>@lang('modules.install_schedules.amount')</label>
                        <input type="text" id="project_amount" class="form-control" autocomplete="none" disabled="disabled">
                    </div>
                </div>
                <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                        <label>@lang('modules.install_schedules.designer')</label>
                        <input type="text" id="project_designer" class="form-control" autocomplete="none" disabled="disabled">
                    </div>
                </div>
                <div class="col-xs-12 col-md-4">
                    <label>@lang('modules.install_schedules.designerColor')</label>
                    <div class="form-group">
                        <input type="text" id="project_designer_color" name="designer_color" class="form-control" autocomplete="none" style="display: none">
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="tentative_details" @if($schedule->type_id != 2) style="display: none" @endif>
            <div class="col-xs-12">
                <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                        <label>@lang('modules.install_schedules.client')</label>
                        <input type="text" id="tentative_client" name="tentative_client" class="form-control"
                               value="{{$schedule->tentative_client ?? ''}}" autocomplete="none" @if($schedule->type_id != 2) disabled="disabled" @endif>
                    </div>
                </div>

                <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                        <label>@lang('modules.projects.city')</label>
                        <input type="text" id="tentative_city" name="tentative_city" class="form-control"
                               value="{{$schedule->tentative_city ?? ''}}" autocomplete="none" @if($schedule->type_id != 2) disabled="disabled" @endif>
                    </div>
                </div>
                <div class="col-xs-12 col-md-4">
                    <div class="form-group">
                        <label>@lang('modules.install_schedules.amount')</label>
                        <div class="col-md-12 input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-usd" aria-hidden="true"></i>
                            </div>
                            <input type="number" min="0.01" step="0.01" class="form-control" name="tentative_amount" id="tentative_amount"
                                   value="{{$schedule->tentative_amount ?? ''}}" @if($schedule->type_id != 2) disabled="disabled" @endif autocomplete="none"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group">
                <div class="col-xs-6">
                    <div class="checkbox checkbox-info">
                        <input id="edit-send-reminder" name="send_reminder" value="yes" @if($schedule->send_reminder == 'yes') checked @endif
                                type="checkbox">
                        <label for="edit-send-reminder">@lang('modules.tasks.reminder')</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="row" id="edit-reminder-fields" @if($schedule->send_reminder == 'no') style="display: none;" @endif>
            <div class="col-xs-6 col-md-3">
                <div class="form-group">
                    <label>@lang('modules.install_schedules.remindBefore')</label>
                    <input type="number" min="1" value="{{ $schedule->remind_time }}" name="remind_time" class="form-control">
                </div>
            </div>
            <div class="col-xs-6 col-md-3">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <select name="remind_type" id="" class="form-control">
                        <option @if ($schedule->remind_type == 'day')
                            selected
                        @endif value="day">@lang('app.day')</option>
                        <option @if ($schedule->remind_type == 'hour')
                            selected
                        @endif value="hour">@lang('app.hour')</option>
                        <option @if ($schedule->remind_type == 'minute')
                            selected
                        @endif value="minute">@lang('app.minute')</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}

</div>
<div class="modal-footer">
    <button type="button" class="btn btn-white waves-effect" data-dismiss="modal">Close</button>
    <button type="button" class="btn btn-success update-event waves-effect waves-light">@lang('app.update')</button>
</div>



<script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/timepicker/bootstrap-timepicker.min.js') }}"></script>

<script src="{{ asset('js/cbpFWTabs.js') }}"></script>
<script src="{{ asset('plugins/bower_components/custom-select/custom-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/multiselect/js/jquery.multi-select.js') }}"></script>
<script src="{{ asset('plugins/bootstrap-colorselector/bootstrap-colorselector.min.js') }}"></script>

<script>

    $(".select3").select2({
        formatNoMatches: function () {
            return "{{ __('messages.noRecordFound') }}";
        }
    });

    jQuery('#updateEvent #start_date,  #updateEvent #end_date').datepicker({
        autoclose: true,
        todayHighlight: true
    })

    $('#edit-colorselector').colorselector();

    $('#updateEvent #start_time,#updateEvent #end_time').timepicker();

    $('#updateEvent #type_id').change(function () {
        let typeID = $(this).val();
        if(typeID === '2'){
            $('#updateEvent #attendees_container').hide();
            $('#updateEvent #project_select').hide();
            $('#updateEvent #project_details').hide();
            $('#updateEvent #tentative_details').show();
            $('#updateEvent #tentative_details').find('input').prop('disabled', false);
        }
        else{
            $('#updateEvent #attendees_container').show();
            $('#updateEvent #project_select').show();
            $('#updateEvent #tentative_details').hide();
            $('#updateEvent #project_details').show();
            $('#updateEvent #tentative_details').find('input').prop('disabled', true);
        }
    });

    $('#updateEvent #project_id').change(function () {
        let project_id = $(this).val();
        var url = '{{route("admin.projects.getdetail", ":id")}}';
        url = url.replace(':id', project_id);
        var token = "{{ csrf_token() }}";
        $.easyAjax({
            url: url,
            container: '#modal-detail-application',
            type: "POST",
            data: { _token : token },
            success: function (response) {
                if(response.status == 'success'){
                    $('#updateEvent #project_info_id').val(response.data.id);
                    $('#updateEvent #project_client_last_name').val(response.data.client_last_name);
                    $('#updateEvent #project_city').val(response.data.city);
                    $('#updateEvent #project_amount').val('$' + number_format(response.data.sales_price, 2));
                    $('#updateEvent #project_designer').val(response.data.designer_name);
                    $('#updateEvent #project_designer_color').hide();
                    $('#updateEvent #project_designer_color').val('');
                    $('#updateEvent #project_designer_color').spectrum('destroy');
                    if(response.data.designer_color.length > 0){
                        $('#updateEvent #project_designer_color').spectrum({
                            color: response.data.designer_color,
                            showInput: true,
                            className: "full-spectrum",
                            showInitial: true,
                            showPalette: true,
                            showSelectionPalette: true,
                            maxPaletteSize: 10,
                            preferredFormat: "hex",
                            localStorageKey: "spectrum.demo",
                            palette: [
                                ["rgb(0, 0, 0)", "rgb(67, 67, 67)", "rgb(102, 102, 102)",
                                    "rgb(204, 204, 204)", "rgb(217, 217, 217)","rgb(255, 255, 255)"],
                                ["rgb(152, 0, 0)", "rgb(255, 0, 0)", "rgb(255, 153, 0)", "rgb(255, 255, 0)", "rgb(0, 255, 0)",
                                    "rgb(0, 255, 255)", "rgb(74, 134, 232)", "rgb(0, 0, 255)", "rgb(153, 0, 255)", "rgb(255, 0, 255)"],
                                ["rgb(230, 184, 175)", "rgb(244, 204, 204)", "rgb(252, 229, 205)", "rgb(255, 242, 204)", "rgb(217, 234, 211)",
                                    "rgb(208, 224, 227)", "rgb(201, 218, 248)", "rgb(207, 226, 243)", "rgb(217, 210, 233)", "rgb(234, 209, 220)",
                                    "rgb(221, 126, 107)", "rgb(234, 153, 153)", "rgb(249, 203, 156)", "rgb(255, 229, 153)", "rgb(182, 215, 168)",
                                    "rgb(162, 196, 201)", "rgb(164, 194, 244)", "rgb(159, 197, 232)", "rgb(180, 167, 214)", "rgb(213, 166, 189)",
                                    "rgb(204, 65, 37)", "rgb(224, 102, 102)", "rgb(246, 178, 107)", "rgb(255, 217, 102)", "rgb(147, 196, 125)",
                                    "rgb(118, 165, 175)", "rgb(109, 158, 235)", "rgb(111, 168, 220)", "rgb(142, 124, 195)", "rgb(194, 123, 160)",
                                    "rgb(166, 28, 0)", "rgb(204, 0, 0)", "rgb(230, 145, 56)", "rgb(241, 194, 50)", "rgb(106, 168, 79)",
                                    "rgb(69, 129, 142)", "rgb(60, 120, 216)", "rgb(61, 133, 198)", "rgb(103, 78, 167)", "rgb(166, 77, 121)",
                                    "rgb(91, 15, 0)", "rgb(102, 0, 0)", "rgb(120, 63, 4)", "rgb(127, 96, 0)", "rgb(39, 78, 19)",
                                    "rgb(12, 52, 61)", "rgb(28, 69, 135)", "rgb(7, 55, 99)", "rgb(32, 18, 77)", "rgb(76, 17, 48)"]
                            ]
                        });
                    }
                    else{
                        $('#updateEvent #project_designer_color').spectrum({
                            allowEmpty:true,
                            showInput: true,
                            className: "full-spectrum",
                            showInitial: true,
                            showPalette: true,
                            showSelectionPalette: true,
                            maxPaletteSize: 10,
                            preferredFormat: "hex",
                            localStorageKey: "spectrum.demo",
                            palette: [
                                ["rgb(0, 0, 0)", "rgb(67, 67, 67)", "rgb(102, 102, 102)",
                                    "rgb(204, 204, 204)", "rgb(217, 217, 217)","rgb(255, 255, 255)"],
                                ["rgb(152, 0, 0)", "rgb(255, 0, 0)", "rgb(255, 153, 0)", "rgb(255, 255, 0)", "rgb(0, 255, 0)",
                                    "rgb(0, 255, 255)", "rgb(74, 134, 232)", "rgb(0, 0, 255)", "rgb(153, 0, 255)", "rgb(255, 0, 255)"],
                                ["rgb(230, 184, 175)", "rgb(244, 204, 204)", "rgb(252, 229, 205)", "rgb(255, 242, 204)", "rgb(217, 234, 211)",
                                    "rgb(208, 224, 227)", "rgb(201, 218, 248)", "rgb(207, 226, 243)", "rgb(217, 210, 233)", "rgb(234, 209, 220)",
                                    "rgb(221, 126, 107)", "rgb(234, 153, 153)", "rgb(249, 203, 156)", "rgb(255, 229, 153)", "rgb(182, 215, 168)",
                                    "rgb(162, 196, 201)", "rgb(164, 194, 244)", "rgb(159, 197, 232)", "rgb(180, 167, 214)", "rgb(213, 166, 189)",
                                    "rgb(204, 65, 37)", "rgb(224, 102, 102)", "rgb(246, 178, 107)", "rgb(255, 217, 102)", "rgb(147, 196, 125)",
                                    "rgb(118, 165, 175)", "rgb(109, 158, 235)", "rgb(111, 168, 220)", "rgb(142, 124, 195)", "rgb(194, 123, 160)",
                                    "rgb(166, 28, 0)", "rgb(204, 0, 0)", "rgb(230, 145, 56)", "rgb(241, 194, 50)", "rgb(106, 168, 79)",
                                    "rgb(69, 129, 142)", "rgb(60, 120, 216)", "rgb(61, 133, 198)", "rgb(103, 78, 167)", "rgb(166, 77, 121)",
                                    "rgb(91, 15, 0)", "rgb(102, 0, 0)", "rgb(120, 63, 4)", "rgb(127, 96, 0)", "rgb(39, 78, 19)",
                                    "rgb(12, 52, 61)", "rgb(28, 69, 135)", "rgb(7, 55, 99)", "rgb(32, 18, 77)", "rgb(76, 17, 48)"]
                            ]
                        });
                    }
                }
            }
        })
    });

    $('.update-event').click(function () {
        $.easyAjax({
            url: '{{route("admin.install_schedules.update", $schedule->id)}}',
            container: '#modal-detail-application',
            type: "PUT",
            data: $('#updateEvent').serialize(),
            success: function (response) {
                if(response.status == 'success'){
                    let org_event = $.CalendarApp.$calendarObj.getEventById(response.id);
                    $('#eventDetailModal').modal('hide');
                    $('#updateEvent')[0].reset();
                    let schedule = {};
                    if(response.type != 2){
                        schedule = {
                            id: response.id,
                            title: response.title,
                            start: response.start_date_time,
                            end:  response.end_date_time,
                            color: response.label_color
                        };
                    }
                    else{
                        schedule = {
                            id: response.id,
                            title: response.title,
                            start: response.start_date_time,
                            end:  response.end_date_time,
                            className: 'tentative'
                        };
                    }

                    org_event.remove();
                    $.CalendarApp.$calendarObj.addEvent(schedule);
                }
            }
        })
    })

    $('#updateEvent #edit-repeat-event').change(function () {
        if($(this).is(':checked')){
            $('#updateEvent #edit-repeat-fields').show();
        }
        else{
            $('#updateEvent #edit-repeat-fields').hide();
        }
    })

    $('#updateEvent #edit-send-reminder').change(function () {
        if($(this).is(':checked')){
            $('#updateEvent #edit-reminder-fields').show();
        }
        else{
            $('#updateEvent #edit-reminder-fields').hide();
        }
    })

    $('#updateEvent #show-attendees').click(function () {
        $('#updateEvent #edit-attendees').slideToggle();
    })

    $('#updateEvent .remove-attendee').click(function () {
        var row = $(this);
        var attendeeId = row.data('attendee-id');
        var projectId = row.data('project-id');
        var url = '{{route("admin.install_schedules.removeAttendee")}}';

        $.easyAjax({
            url: url,
            type: "POST",
            data: { attendeeId: attendeeId, projectId: projectId, _token: '{{ csrf_token() }}'},
            success: function (response) {
                if(response.status == 'success'){
                    row.closest('.list-group-item').fadeOut();
                }
            }
        })
    });

</script>
