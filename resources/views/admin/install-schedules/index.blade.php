@extends('layouts.app')

@section('page-title')
    <div class="row bg-title">
        <!-- .page title -->
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title"><i class="{{ $pageIcon }}"></i> {{ $pageTitle }}</h4>
        </div>
        <!-- /.page title -->
        <!-- .breadcrumb -->
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="{{ route('admin.dashboard') }}">@lang('app.menu.home')</a></li>
                <li class="active">{{ $pageTitle }}</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@push('head-script')
    <link rel="stylesheet" href="{{ asset('plugins/full-calendar/packages/core/main.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/full-calendar/packages/daygrid/main.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/full-calendar/packages/timegrid/main.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/context-menu/jquery.contextMenu.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/timepicker/bootstrap-timepicker.min.css') }}">

    <link rel="stylesheet" href="{{ asset('plugins/bower_components/custom-select/custom-select.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/multiselect/css/multi-select.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/bootstrap-colorselector/bootstrap-colorselector.min.css') }}">
    <link href="{{ asset('css/spectrum.css') }}" rel="stylesheet">
    <style>
        .tentative{
            background-color: #009aa3 !important;
            background-image: url("/img/white-brick-wall.png") !important;
            /* This is mostly intended for prototyping; please download the pattern and re-host for production environments. Thank you! */
        }
    </style>
@endpush

@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <div class="row">
                    <h3 class="box-title col-md-3">@lang('app.menu.installSchedules')</h3>

                    <div class="col-md-9">
                        <button id="add_schedule" class="btn btn-sm btn-success waves-effect waves-light  pull-right">
                            <i class="ti-plus"></i> @lang('modules.install_schedules.addSchedule')
                        </button>

                    </div>

                </div>


                <div id="calendar"></div>
            </div>
        </div>
    </div>
    <!-- .row -->

    <!-- BEGIN MODAL -->
    <div class="modal fade bs-modal-md in" id="my-event" role="dialog" aria-labelledby="myModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" id="modal-data-application">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="icon-plus"></i> @lang('modules.install_schedules.addSchedule')</h4>
                </div>
                <div class="modal-body">
                    {!! Form::open(['id'=>'createEvent','class'=>'ajax-form','method'=>'POST']) !!}
                    <div class="form-body">
                        <div class="row">
                            <div class="col-xs-12 col-md-6">
                                <div class="form-group">
                                    <label>@lang('modules.install_schedules.scheduleName')</label>
                                    <input type="text" name="schedule_name" id="schedule_name" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xs-12 col-md-12">
                                <div class="form-group">
                                    <label>@lang('app.description')</label>
                                    <textarea name="description" id="description" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-3 ">
                                <div class="form-group">
                                    <label>@lang('modules.install_schedules.startOn')</label>
                                    <input type="text" name="start_date" id="start_date" class="form-control" autocomplete="none">
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-3">
                                <div class="input-group bootstrap-timepicker timepicker">
                                    <label>&nbsp;</label>
                                    <input type="text" name="start_time" id="start_time"
                                           class="form-control" >
                                </div>
                            </div>

                            <div class="col-xs-12 col-md-3">
                                <div class="form-group">
                                    <label>@lang('modules.install_schedules.endOn')</label>
                                    <input type="text" name="end_date" id="end_date" class="form-control" autocomplete="none">
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-3">
                                <div class="input-group bootstrap-timepicker timepicker">
                                    <label>&nbsp;</label>
                                    <input type="text" name="end_time" id="end_time"
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
                                            <option value="{{ $type->id }}">{{ ucwords($type->type) }}</option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xs-12">
                                <div class="form-group">
                                    <label class="control-label">@lang('modules.install_schedules.status')</label>
                                    <select class="select2 form-control"
                                            data-placeholder="@lang('modules.install_schedules.status')" name="status" id="status">
                                            <option value="incomplete">In Complete</option>
                                            <option value="complete">Complete</option>
                                    </select>

                                </div>
                            </div>
                        </div>

                        <div class="row" id="project_select">
                            <div class="col-xs-12">
                                <div class="form-group">
                                    <label>@lang('modules.install_schedules.project')</label>
                                    <select class="select2 form-control" data-placeholder="@lang('modules.timeLogs.selectProject')" name="project_id" id="project_id" >
                                        <option value=""></option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}">{{ ucwords($project->project_name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xs-12"  id="attendees">
                                <label>@lang('modules.install_schedules.addAttendees')</label>
                                <div class="form-group">
                                    <select class="select2 m-b-10 select2-multiple " multiple="multiple"
                                            data-placeholder="@lang('modules.messages.chooseMember')" name="user_id[]" id="user_id">
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->id }}">{{ ucwords($emp->name) }} @if($emp->id == $user->id)
                                                    (YOU) @endif</option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>
                        </div>

                        <div class="row" id="project_details">
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

                        <div class="row" id="tentative_details" style="display: none">
                            <div class="col-xs-12">
                                <div class="col-xs-12 col-md-4">
                                    <div class="form-group">
                                        <label>@lang('modules.install_schedules.client')</label>
                                        <input type="text" id="tentative_client" name="tentative_client" class="form-control" autocomplete="none" disabled="disabled">
                                    </div>
                                </div>

                                <div class="col-xs-12 col-md-4">
                                    <div class="form-group">
                                        <label>@lang('modules.projects.city')</label>
                                        <input type="text" id="tentative_city" name="tentative_city" class="form-control" autocomplete="none" disabled="disabled">
                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-4">
                                    <div class="form-group">
                                        <label>@lang('modules.install_schedules.amount')</label>
                                        <div class="col-md-12 input-group">
                                            <div class="input-group-addon">
                                                <i class="fa fa-usd" aria-hidden="true"></i>
                                            </div>
                                            <input type="number" min="0.01" step="0.01" class="form-control" name="tentative_amount" id="tentative_amount" disabled="disabled" autocomplete="none"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group">
                                <div class="col-xs-6">
                                    <div class="checkbox checkbox-info">
                                        <input id="repeat-event" name="repeat" value="yes"
                                               type="checkbox">
                                        <label for="repeat-event">@lang('modules.install_schedules.repeat')</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row" id="repeat-fields" style="display: none">
                            <div class="col-xs-6 col-md-3 ">
                                <div class="form-group">
                                    <label>@lang('modules.install_schedules.repeatEvery')</label>
                                    <input type="number" min="1" value="1" name="repeat_count" class="form-control">
                                </div>
                            </div>
                            <div class="col-xs-6 col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <select name="repeat_type" id="" class="form-control">
                                        <option value="day">@lang('app.day')</option>
                                        <option value="week">@lang('app.week')</option>
                                        <option value="month">@lang('app.month')</option>
                                        <option value="year">@lang('app.year')</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-xs-6 col-md-3">
                                <div class="form-group">
                                    <label>@lang('modules.install_schedules.cycles') <a class="mytooltip" href="javascript:void(0)"> <i class="fa fa-info-circle"></i><span class="tooltip-content5"><span class="tooltip-text3"><span class="tooltip-inner2">@lang('modules.install_schedules.cyclesToolTip')</span></span></span></a></label>
                                    <input type="text" name="repeat_cycles" id="repeat_cycles" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group">
                                <div class="col-xs-6">
                                    <div class="checkbox checkbox-info">
                                        <input id="send_reminder" name="send_reminder" value="yes"
                                               type="checkbox">
                                        <label for="send_reminder">@lang('modules.tasks.reminder')</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row" id="reminder-fields" style="display: none;">
                            <div class="col-xs-6 col-md-3">
                                <div class="form-group">
                                    <label>@lang('modules.install_schedules.remindBefore')</label>
                                    <input type="number" min="1" value="1" name="remind_time" class="form-control">
                                </div>
                            </div>
                            <div class="col-xs-6 col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <select name="remind_type" id="" class="form-control">
                                        <option value="day">@lang('app.day')</option>
                                        <option value="hour">@lang('app.hour')</option>
                                        <option value="minute">@lang('app.minute')</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    {!! Form::close() !!}

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white waves-effect" data-dismiss="modal">@lang('app.close')</button>
                    <button type="button" class="btn btn-success save-event waves-effect waves-light">@lang('app.submit')</button>
                </div>
            </div>
        </div>
    </div>

    {{--Ajax Modal--}}
    <div class="modal fade bs-modal-md in" id="eventDetailModal" role="dialog" aria-labelledby="myModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" id="modal-detail-application">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <span class="caption-subject font-red-sunglo bold uppercase" id="modelHeading"></span>
                </div>
                <div class="modal-body">
                    Loading...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn blue">Save changes</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    {{--Ajax Modal Ends--}}

@endsection

@push('footer-script')

    <script>
        var schedules = [
            @foreach($schedules as $schedule)
            {
                id: '{!! ucfirst($schedule->id) !!}',
                title: '{!! ucfirst($schedule->schedule_name) !!}',
                start: '{!! $schedule->start_date_time !!}',
                end:  '{!! $schedule->end_date_time !!}',
                @if(!empty($schedule->type) && $schedule->type->id == 2)
                    className: 'tentative',
                @else
                    color: '{{$schedule->project->designer->color->color_code ?? ""}}',
                @endif
            },
            @endforeach
        ];

        var getEventEdit = function (id) {
            var url = '{{ route('admin.install_schedules.edit', ':id')}}';
            url = url.replace(':id', id);

            $('#modelHeading').html('Edit Install Schedules');
            $.ajaxModal('#eventDetailModal', url);
        }

        var resetForm = function(){
            $('#createEvent')[0].reset();
            $("#createEvent #status").select2('val', 'incomplete');
            $("#createEvent #type_id").select2('val', '1');
            $("#createEvent #user_id").select2('val', []);
            $("#createEvent #project_id").select2('val', []);
            $('#createEvent #attendees').parent().show();
            $('#createEvent #project_select').show();
            $('#createEvent #tentative_details').hide();
            $('#createEvent #project_details').show();
            $('#createEvent #tentative_details').find('input').prop('disabled', true);
            $("#createEvent #project_designer_color").spectrum('destroy');
            $('#createEvent #project_designer_color').val('');
            $("#createEvent #project_designer_color").hide();
        }

        var calendarLocale = '{{ $global->locale }}';
    </script>

    <script src="{{ asset('plugins/bower_components/calendar/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('plugins/bower_components/moment/moment.js') }}"></script>
    <script src="{{ asset('plugins/context-menu/jquery.ui.position.min.js') }}"></script>
    <script src="{{ asset('plugins/context-menu/jquery.contextMenu.min.js') }}"></script>
    <script src="{{ asset('plugins/full-calendar/packages/core/main.js') }}"></script>
    <script src="{{ asset('plugins/full-calendar/packages/interaction/main.js') }}"></script>
    <script src="{{ asset('plugins/full-calendar/packages/daygrid/main.js') }}"></script>
    <script src="{{ asset('plugins/full-calendar/packages/timegrid/main.js') }}"></script>
    <script src="{{ asset('js/install-calendar.js') }}"></script>

    <script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('plugins/bower_components/timepicker/bootstrap-timepicker.min.js') }}"></script>

    <script src="{{ asset('js/cbpFWTabs.js') }}"></script>
    <script src="{{ asset('plugins/bower_components/custom-select/custom-select.min.js') }}"></script>
    <script src="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('plugins/bower_components/multiselect/js/jquery.multi-select.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap-colorselector/bootstrap-colorselector.min.js') }}"></script>
    <script src="{{ asset('js/spectrum.js') }}"></script>

    <script>
        jQuery('#createEvent #start_date, #createEvent #end_date').datepicker({
            autoclose: true,
            todayHighlight: true
        })

        $('#createEvent #colorselector').colorselector();

        $('#createEvent #start_time,#createEvent #end_time').timepicker();
        $('#createEvent #start_time').timepicker('setTime', '00:00 AM');
        $('#createEvent #end_time').timepicker('setTime', '11:59 PM');

        $(".select2").select2({
            formatNoMatches: function () {
                return "{{ __('messages.noRecordFound') }}";
            }
        });

        $('#add_schedule').on('click',function () {
            resetForm();
            $('#my-event').modal('show');
        });

        function formatAMPM(date) {
            let hours = date.getHours();
            let minutes = date.getMinutes();
            let ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12; // the hour '0' should be '12'
            minutes = minutes < 10 ? '0'+minutes : minutes;
            let strTime = hours + ':' + minutes + ' ' + ampm;
            return strTime;
        }

        function addEventModal(start, end, allDay){
            resetForm();
            if(start){
                var curr_date = start.getDate();
                if(curr_date < 10){
                    curr_date = '0'+curr_date;
                }
                var curr_month = start.getMonth();
                curr_month = curr_month+1;
                if(curr_month < 10){
                    curr_month = '0'+curr_month;
                }
                var curr_year = start.getFullYear();

                $('#start_date').val(curr_month+'/'+curr_date+'/'+curr_year);
                $('#start_time').val(formatAMPM(start));

                var curr_date = end.getDate();
                if(curr_date < 10){
                    curr_date = '0'+curr_date;
                }
                var curr_month = end.getMonth();
                curr_month = curr_month+1;
                if(curr_month < 10){
                    curr_month = '0'+curr_month;
                }
                var curr_year = end.getFullYear();
                $('#end_date').val(curr_month+'/'+curr_date+'/'+curr_year);
                $('#end_time').val(formatAMPM(end));

                $('#createEvent #start_date').datepicker('destroy');
                $('#createEvent #end_date').datepicker('destroy');
                jQuery('#createEvent #start_date, #createEvent #end_date').datepicker({
                    autoclose: true,
                    todayHighlight: true
                })
            }

            $('#my-event').modal('show');

        }

        $('.save-event').click(function () {
            $.easyAjax({
                url: '{{route('admin.install_schedules.store')}}',
                container: '#modal-data-application',
                type: "POST",
                data: $('#createEvent').serialize(),
                success: function (response) {
                    if(response.status == 'success'){
                        $('#my-event').modal('hide');
                        resetForm();
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
                        $.CalendarApp.$calendarObj.addEvent(schedule);
                    }
                }
            })
        })

        $('#createEvent #repeat-event').change(function () {
            if($(this).is(':checked')){
                $('#createEvent #repeat-fields').show();
            }
            else{
                $('#createEvent #repeat-fields').hide();
            }
        })

        $('#createEvent #send_reminder').change(function () {
            if($(this).is(':checked')){
                $('#createEvent #reminder-fields').show();
            }
            else{
                $('#createEvent #reminder-fields').hide();
            }
        });

        $('#createEvent #type_id').change(function () {
            let typeID = $(this).val();
            if(typeID === '2'){
                $('#createEvent #attendees').parent().hide();
                $('#createEvent #project_details').hide();
                $('#createEvent #project_select').hide();
                $('#createEvent #tentative_details').show();
                $('#createEvent #tentative_details').find('input').prop('disabled', false);
            }
            else{
                $('#createEvent #tentative_details').hide();
                $('#createEvent #tentative_details').find('input').prop('disabled', true);
                $('#createEvent #attendees').parent().show();
                $('#createEvent #project_select').show();
                $('#createEvent #project_details').show();

            }
        });

        $('#createEvent #project_id').change(function () {
            let project_id = $(this).val();
            var url = '{{route("admin.projects.getdetail", ":id")}}';
            url = url.replace(':id', project_id);
            var token = "{{ csrf_token() }}";
            $.easyAjax({
                url: url,
                container: '#modal-data-application',
                type: "POST",
                data: { _token : token },
                success: function (response) {
                    if(response.status == 'success'){
                        $('#createEvent #project_info_id').val(response.data.id);
                        $('#createEvent #project_client_last_name').val(response.data.client_last_name);
                        $('#createEvent #project_city').val(response.data.city);
                        $('#createEvent #project_amount').val('$' + number_format(response.data.sales_price, 2));
                        $('#createEvent #user_id').select2('val', response.data.installers);
                        $('#createEvent #project_designer').val(response.data.designer_name);
                        $('#createEvent #project_designer_color').hide();
                        $('#createEvent #project_designer_color').val('');
                        $('#createEvent #project_designer_color').spectrum('destroy');
                        if(response.data.designer_color.length > 0){
                            $('#createEvent #project_designer_color').spectrum({
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
                            $('#createEvent #project_designer_color').spectrum({
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

        $(document).ready(function() {
            $.contextMenu({
                // define which elements trigger this menu
                selector: "a.fc-event",
                // define the elements of the menu
                items: {
                    edit: {
                        name: "Edit",
                        callback: function (key, opt) {
                            let schedule_id = $(this).data('schedule-id');
                            getEventEdit(schedule_id);
                        }
                    },
                    delete: {
                        name: "Delete",
                        callback: function (key, opt) {
                            var schedule_id = $(this).data('schedule-id');
                            swal({
                                title: "Are you sure?",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonColor: "#DD6B55",
                                confirmButtonText: "Yes",
                                cancelButtonText: "Cancel",
                                closeOnConfirm: true,
                                closeOnCancel: true
                            }, function (isConfirm) {
                                if (isConfirm) {
                                    var url = "/install_schedules/:id";
                                    url = url.replace(':id', schedule_id);
                                    var token = $('#createEvent [name="_token"]').val();

                                    $.easyAjax({
                                        type: 'POST',
                                        url: url,
                                        data: {'_token': token, '_method': 'DELETE'},
                                        success: function (response) {
                                            if (response.status == "success") {
                                                let event = $.CalendarApp.$calendarObj.getEventById(schedule_id);
                                                event.remove();
                                            }
                                        }
                                    });
                                }
                            });
                        }
                    }
                }
                // there's more, have a look at the demos and docs...
            });

            // $.contextMenu({
            //     selector: ".fc-day-grid .fc-highlight",
            //     items: {
            //         first: {
            //             name: "Schedule First Appt",
            //             callback: function(key, opt){
            //                 addEventModal($.CalendarApp.$calArg.start, $.CalendarApp.$calArg.end, $.CalendarApp.$calArg.allDay, $.CalendarApp.$calArg.resource.id, 1);
            //             }
            //         },
            //         follow_up: {
            //             name: "Schedule Follow-up Appt",
            //             callback: function(key, opt){
            //                 addEventModal($.CalendarApp.$calArg.start, $.CalendarApp.$calArg.end, $.CalendarApp.$calArg.allDay, $.CalendarApp.$calArg.resource.id, 2);
            //             }
            //         },
            //         blocked: {
            //             name: "Schedule Blocked Time",
            //             callback: function(key, opt){
            //                 addEventModal($.CalendarApp.$calArg.start, $.CalendarApp.$calArg.end, $.CalendarApp.$calArg.allDay, $.CalendarApp.$calArg.resource.id, 3);
            //             }
            //         },
            //         personal: {
            //             name: "Schedule Personal Time Off",
            //             callback: function(key, opt){
            //                 addEventModal($.CalendarApp.$calArg.start, $.CalendarApp.$calArg.end, $.CalendarApp.$calArg.allDay, $.CalendarApp.$calArg.resource.id, 4);
            //             }
            //         }
            //     }
            //     // there's more, have a look at the demos and docs...
            // });
        });

    </script>

@endpush
