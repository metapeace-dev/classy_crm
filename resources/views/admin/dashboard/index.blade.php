@extends('layouts.app')


@push('head-script')
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
@endpush
@section('page-title')
    <div class="row bg-title">
        <!-- .page title -->
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title"><i class="{{ $pageIcon }}"></i> {{ $pageTitle }}</h4>
        </div>
        <!-- /.page title -->
        @if($user->hasRole('admin'))
        <!-- .breadcrumb -->
        {!! Form::open(['id'=>'createProject','class'=>'ajax-form','method'=>'POST']) !!}
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <div class="btn-group dropdown keep-open pull-right m-l-10">
                <button aria-expanded="true" data-toggle="dropdown"
                        class="btn b-all dropdown-toggle waves-effect waves-light"
                        type="button"><i class="icon-settings"></i>
                </button>
                <ul role="menu" class="dropdown-menu  dropdown-menu-right dashboard-settings">
                    <li class="b-b"><h4>@lang('modules.dashboard.dashboardWidgets')</h4></li>

                    @foreach ($widgets as $widget)
                        @php
                            $wname = \Illuminate\Support\Str::camel($widget->widget_name);
                        @endphp
                        <li>
                            <div class="checkbox checkbox-info ">
                                <input id="{{ $widget->widget_name }}" name="{{ $widget->widget_name }}" value="true"
                                    @if ($widget->status)
                                        checked
                                    @endif
                                        type="checkbox">
                                <label for="{{ $widget->widget_name }}">@lang('modules.dashboard.' . $wname)</label>
                            </div>
                        </li>
                    @endforeach

                    <li>
                        <button type="button" id="save-form" class="btn btn-success btn-sm btn-block">@lang('app.save')</button>
                    </li>

                </ul>
            </div>
            {!! Form::close() !!}

            {!! Form::open(['id'=>'periodForm','class'=>'ajax-form','method'=>'POST']) !!}
                <div class="btn-group dropdown keep-open pull-right m-l-10">
                    <button aria-expanded="true" data-toggle="dropdown"
                            class="btn b-all dropdown-toggle waves-effect waves-light"
                            type="button"><i class="icon-calender"></i>
                    </button>
                    <ul role="menu" class="dropdown-menu  dropdown-menu-right dashboard-settings">
                        <li class="b-b"><h4>@lang('modules.dashboard.dashboardPeriod')</h4></li>

                            <li style="width: inherit;">

                                <div class="form-group" style="margin-bottom: 0px">
                                    <div class="radio-list">
                                        <label class="radio-inline col-md-3">
                                            <div class="radio radio-info">
                                                <input type="radio" name="period" @if($period->period == '1') checked @endif id="period1" value="1">
                                                <label for="period1">1 month</label>
                                            </div>
                                        </label>
                                        <label class="radio-inline col-md-3">
                                            <div class="radio radio-info">
                                                <input type="radio" name="period" id="period2" @if($period->period == '2') checked @endif value="2">
                                                <label for="period2">2 months</label>
                                            </div>
                                        </label>
                                        <label class="radio-inline col-md-3">
                                            <div class="radio radio-info">
                                                <input type="radio" name="period" id="period3" @if($period->period == '3') checked @endif value="3">
                                                <label for="period3">3 months</label>
                                            </div>
                                        </label>
                                        <label class="radio-inline col-md-3">
                                            <div class="radio radio-info">
                                                <input type="radio" name="period" id="period4" @if($period->period == '4') checked @endif value="4">
                                                <label for="period4">Custom</label>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </li>

                            <li id='dashboard_date' style="width: inherit; padding-top: 15px; padding-bottom: 15px; @if($period->period != '4' )display: none; @endif">
                                <div class="example">

                                    <div class="input-daterange input-group" id="date-range">
                                        <input type="text" class="form-control" id="start-date" name="from" placeholder="@lang('app.startDate')"
                                               value="{{ $fromDate->format($global->date_format) }}"/>
                                        <span class="input-group-addon bg-info b-0 text-white">@lang('app.to')</span>
                                        <input type="text" class="form-control" id="end-date" name="to" placeholder="@lang('app.endDate')"
                                               value="{{ $toDate->format($global->date_format) }}"/>
                                    </div>
                                </div>
                            </li>

                        <li style="margin-top: 10px;">
                            <button type="button" id="save-period-form" class="btn btn-success btn-sm btn-block" style="margin-top: 50px">@lang('app.save')</button>
                        </li>

                    </ul>
                </div>
            {!! Form::close() !!}
            @endif

            <ol class="breadcrumb">
                <li><a href="{{ route('admin.dashboard') }}">@lang('app.menu.home')</a></li>
                <li class="active">{{ $pageTitle }}</li>
            </ol>

           
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@push('head-script')
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/calendar/dist/fullcalendar.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">

    <link rel="stylesheet" href="{{ asset('plugins/bower_components/morrisjs/morris.css') }}"><!--Owl carousel CSS -->
    <link rel="stylesheet"
          href="{{ asset('plugins/bower_components/owl.carousel/owl.carousel.min.css') }}"><!--Owl carousel CSS -->
    <link rel="stylesheet"
          href="{{ asset('plugins/bower_components/owl.carousel/owl.theme.default.css') }}"><!--Owl carousel CSS -->

    <style>
        .col-in {
            padding: 0 20px !important;

        }

        .fc-event {
            font-size: 10px !important;
        }

        @media (min-width: 769px) {
            #wrapper .panel-wrapper {
                height: 530px;
                overflow-y: auto;
            }
        }

        div.panel-body{
            min-height:320px; max-height: 320px; overflow-y: scroll; padding-top: 5px !important;
        }

    </style>
@endpush

@section('content')
    <div class="row dashboard-stats">

        @if(in_array('leads',$modules) && in_array('total_leads',$activeWidgets))
            <div class="col-md-3 col-sm-6">
                <a href="{{ route('admin.leads.index') }}">
                    <div class="white-box">
                        <div class="row">
                            <div class="col-xs-3">
                                <div>
                                    <span class="bg-success-gradient"><i class="ti-receipt"></i></span>
                                </div>
                            </div>
                            <div class="col-xs-9 text-right">
                                <span class="widget-title"> @lang('modules.dashboard.totalLeads')</span><br>
                                <span class="counter">{{ $counts->totalLeads }}</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif

        @if(in_array('leads',$modules) && in_array('pending_leads',$activeWidgets))
            <div class="col-md-3 col-sm-6">
                <a href="{{ route('admin.leads.index') }}">
                    <div class="white-box">
                        <div class="row">
                            <div class="col-xs-3">
                                <div>
                                    <span class="bg-danger-gradient"><i class="ti-receipt"></i></span>
                                </div>
                            </div>
                            <div class="col-xs-9 text-right">
                                <span class="widget-title"> @lang('modules.dashboard.pendingLeads')</span><br>
                                <span class="counter">{{ $counts->pendingLeads }}</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif

        @if(in_array('projects',$modules) && in_array('total_projects',$activeWidgets))
            <div class="col-md-3 col-sm-6">
                <a href="{{ route('admin.projects.index') }}">
                    <div class="white-box">
                        <div class="row">
                            <div class="col-xs-3">
                                <div>
                                    <span class="bg-success-gradient"><i class="icon-layers"></i></span>
                                </div>
                            </div>
                            <div class="col-xs-9 text-right">
                                <span class="widget-title"> @lang('modules.dashboard.totalProjects')</span><br>
                                <span class="counter">{{ $counts->totalProjects }}</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif

        @if(in_array('projects',$modules) && in_array('total_sales_for_projects',$activeWidgets))
            <div class="col-md-3 col-sm-6">
                <a href="{{ route('admin.projects.index') }}">
                    <div class="white-box">
                        <div class="row">
                            <div class="col-xs-3">
                                <div>
                                    <span class="bg-success-gradient"><i class="icon-layers"></i></span>
                                </div>
                            </div>
                            <div class="col-xs-9 text-right">
                                <span class="widget-title"> @lang('modules.dashboard.totalSalesForProjects')</span><br>
                                <span class="counter">${{ $counts->totalSales }}</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif

        @if(in_array('events',$modules) && in_array('appointments',$activeWidgets))
            <div class="col-md-6" id="project-timeline">
                <div class="panel panel-default">
                    <div class="panel-heading">Appointments Today</div>
                    <div class="panel-body">
                        <div class="steamline">
                            @foreach($appointments as $appt)
                                <div class="sl-item">
                                    <div class="sl-left"><i class="fa fa-circle text-info"></i>
                                    </div>
                                    <div class="sl-right">
                                        <div><h6><a href="{{ route('admin.events.index') }}"
                                                    class="text-info">{{ $appt->attendee->user->name. ' : '. ucwords($appt->event_name) }}
                                                    :</a> {{ ' ('.date('g:i A', strtotime($appt->start_date_time)). ' ~ '.date('g:i A', strtotime($appt->end_date_time)).')' }}</h6>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(in_array('leads',$modules) && in_array('lead_allocation',$activeWidgets))
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">@lang('modules.dashboard.leadAllocation')</div>
                    <div class="panel-body" >
                        <div class="table-responsive">
                            <table>
                                <thead>
                                <tr>
                                    <th><strong>Designer</strong></th>
                                    <th><strong>Lead</strong></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($leadAllocation as $key=>$allocation)
                                    <tr>
                                        <td><a href="{{ route('admin.employees.show', [$key]) }}">{!! $allocation[0]->name . ' - ('. $allocation->count() . ')' !!}</a></td>
                                        <td>
                                            @foreach($allocation as $alloc)
                                                @if($alloc->lead_id)
                                                    <a href="{{ route('admin.leads.show', [$alloc->lead_id]) }}" class="label label-info">{{  '#'.$alloc->lead_id .' - ' .ucfirst($alloc->lead_first_name).' '.ucfirst($alloc->lead_last_name)}}</a>
                                                @endif
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(in_array('tasks',$modules) && in_array('all_tasks',$activeWidgets))
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">@lang('modules.dashboard.taskForDesigners')</div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                <tr>
                                    <th><strong>Name</strong></th>
                                    <th><strong>Task</strong></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($allTasks as $key=>$task)
                                    <tr>
                                        <td><a href="{{ route('admin.employees.show', [$key]) }}">{{ $task[0]->name  }}</a></td>
                                        <td>
                                            @foreach($task as $t)
                                                @if($t->task_id)
                                                    <a href="{{ route('admin.all-tasks.edit', [$t->task_id]) }}" class="label label-info">{{  '#'.$t->task_id .' - ' .ucfirst($t->task_name)}}</a>
                                                @endif
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(in_array('tasks',$modules) && in_array('user_tasks',$activeWidgets))
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">@lang('modules.dashboard.userTasks')</div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table>
                                <thead>
                                <tr>
                                    <th><strong>@lang('app.title')</strong></th>
                                    <th><strong>@lang('app.status')</strong></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($userTasks as $key=>$task)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.all-tasks.edit', [$task->task_id]) }}">
                                                {!! ($key+1).'. <a href="javascript:;" data-task-id="'.$task->id.'" class="show-task-detail">'.ucfirst($task->heading).'</a>' !!}
                                            </a>
                                        </td>
                                        <td>
                                            <label class="label" style="background-color: {{$task->board_column->label_color}}">{{ $task->board_column->column_name }}</label>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
{{--                            <ul class="list-task list-group" data-role="tasklist">--}}
{{--                                <li class="list-group-item" data-role="task">--}}
{{--                                    <strong>@lang('app.title')</strong> <span--}}
{{--                                            class="pull-right"><strong>@lang('app.status')</strong></span>--}}
{{--                                </li>--}}
{{--                                @forelse($userTasks as $key=>$task)--}}
{{--                                    <li class="list-group-item row" data-role="task">--}}
{{--                                        <div class="col-xs-9">--}}
{{--                                            <a href="{{ route('admin.all-tasks.edit', [$task->task_id]) }}">--}}
{{--                                            {!! ($key+1).'. <a href="javascript:;" data-task-id="'.$task->id.'" class="show-task-detail">'.ucfirst($task->heading).'</a>' !!}--}}
{{--                                            </a>--}}
{{--                                        </div>--}}
{{--                                        <label class="label label-danger pull-right col-xs-3">{{ $task->board_column->column_name }}</label>--}}
{{--                                    </li>--}}
{{--                                @empty--}}
{{--                                    <li class="list-group-item" data-role="task">--}}
{{--                                        @lang("messages.noOpenTasks")--}}
{{--                                    </li>--}}
{{--                                @endforelse--}}
{{--                            </ul>--}}
                    </div>
                </div>
            </div>
        @endif

        @if(in_array('projects',$modules) && in_array('installs',$activeWidgets))
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">@lang('modules.dashboard.installs')</div>
                        <div class="panel-body">
                            <ul class="list-task list-group" data-role="tasklist">
                                @forelse($installs as $key=>$install)
                                    <li class="list-group-item" data-role="task">
                                        {{ '#'.$install->id }}. <a href="{{ route('admin.projects.show', $install->id) }}"
                                                           class="text-danger"> {{  ucfirst($install->project_name) }}</a>
                                        <i>{{ ' ('.$install->install_start_date.' ~ ' . $install->install_end_date. ')' }}</i>
                                    </li>
                                @empty
                                    <li class="list-group-item" data-role="task">
                                        @lang("messages.noProjectFound")
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
        @endif


    </div>
    <!-- .row -->

    {{--Ajax Modal--}}
    <div class="modal fade bs-modal-md in" id="eventDetailModal" role="dialog" aria-labelledby="myModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" id="modal-data-application">
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
    {{--Ajax Modal--}}
    <div class="modal fade bs-modal-md in"  id="subTaskModal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" id="modal-data-application">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <span class="caption-subject font-red-sunglo bold uppercase" id="subTaskModelHeading">Sub Task e</span>
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
        <!-- /.modal-dialog -->.
    </div>
    {{--Ajax Modal Ends--}}
@endsection


@push('footer-script')

    <script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('plugins/bower_components/bootstrap-daterangepicker/daterangepicker.js') }}"></script>

    <script>

        jQuery('#date-range').datepicker({
            toggleActive: true,
            format: '{{ $global->date_picker_format }}',
        });

        var calendarLocale = '{{ $global->locale }}';

        $('.leave-action').click(function () {
            var action = $(this).data('leave-action');
            var leaveId = $(this).data('leave-id');
            var url = '{{ route("admin.leaves.leaveAction") }}';

            $.easyAjax({
                type: 'POST',
                url: url,
                data: {'action': action, 'leaveId': leaveId, '_token': '{{ csrf_token() }}'},
                success: function (response) {
                    if (response.status == 'success') {
                        window.location.reload();
                    }
                }
            });
        })
    </script>


    <script src="{{ asset('plugins/bower_components/raphael/raphael-min.js') }}"></script>
    <script src="{{ asset('plugins/bower_components/morrisjs/morris.js') }}"></script>

    <script src="{{ asset('plugins/bower_components/waypoints/lib/jquery.waypoints.js') }}"></script>
    <script src="{{ asset('plugins/bower_components/counterup/jquery.counterup.min.js') }}"></script>

    <!-- jQuery for carousel -->
    <script src="{{ asset('plugins/bower_components/owl.carousel/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('plugins/bower_components/owl.carousel/owl.custom.js') }}"></script>

    <!--weather icon -->

    <script src="{{ asset('plugins/bower_components/calendar/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('plugins/bower_components/moment/moment.js') }}"></script>
    <script src="{{ asset('plugins/bower_components/calendar/dist/fullcalendar.min.js') }}"></script>
    <script src="{{ asset('plugins/bower_components/calendar/dist/jquery.fullcalendar.js') }}"></script>
    <script src="{{ asset('plugins/bower_components/calendar/dist/locale-all.js') }}"></script>
    {{--<script src="{{ asset('js/event-calendar.js') }}"></script>--}}

    <script>
        function showTable (){
            location.reload();
        }

        $('.show-task-detail').click(function () {
            $(".right-sidebar").slideDown(50).addClass("shw-rside");

            var id = $(this).data('task-id');
            var url = "{{ route('admin.all-tasks.show',':id') }}";
            url = url.replace(':id', id);

            $.easyAjax({
                type: 'GET',
                url: url,
                success: function (response) {
                    if (response.status == "success") {
                        $('#right-sidebar-content').html(response.view);
                    }
                }
            });
        })

        $('.add-sub-task').click(function () {
            var id = $(this).data('task-id');
            var url = '{{ route('admin.sub-task.create')}}?task_id='+id;

            $('#subTaskModelHeading').html('Sub Task');
            $.ajaxModal('#subTaskModal', url);
        })

        $('.keep-open .dropdown-menu').on({
            "click":function(e){
            e.stopPropagation();
            }
        });

        $('#save-form').click(function () {
            $.easyAjax({
                url: '{{route('admin.dashboard.widget')}}',
                container: '#createProject',
                type: "POST",
                redirect: true,
                data: $('#createProject').serialize()
            })
        });

        $('input[type=radio][name=period]').change(function() {
            if (this.value == '4') {
                $('#dashboard_date').show();
            }
            else {
                $('#dashboard_date').hide();
            }
        });

        $('#save-period-form').click(function () {
            $.easyAjax({
                url: '{{route('admin.dashboard.period')}}',
                container: '#periodForm',
                type: "POST",
                redirect: true,
                data: $('#periodForm').serialize()
            })
        });

    </script>
@endpush
