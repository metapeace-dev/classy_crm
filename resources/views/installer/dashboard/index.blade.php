@extends('layouts.installer-app')


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
        <!-- .breadcrumb -->
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="{{ route('installer.dashboard') }}">@lang('app.menu.home')</a></li>
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

        .fc-event{
            font-size: 10px !important;
        }

        @media (min-width: 769px) {
            .panel-wrapper{
                height: 500px;
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

        @if(in_array('projects',$modules) && in_array('total_projects',$activeWidgets))
            <div class="col-md-6 col-sm-6">
                <a href="{{ route('installer.projects.index') }}">
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
            <div class="col-md-6 col-sm-6">
                <a href="{{ route('installer.projects.index') }}">
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

        @if(in_array('tasks',$modules) && in_array('user_tasks',$activeWidgets))
            <div class="col-md-12">
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
                                                {!! ($key+1).'. <a href="javascript:;" data-task-id="'.$task->task_id.'" class="show-task-detail">'.ucfirst($task->heading).'</a>' !!}
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
                                    {{ '#'.$install->id }}. <a href="{{ route('installer.projects.show', $install->id) }}"
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

        @if(in_array('employees',$modules) && in_array('user_activity_timeline',$activeWidgets))
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">@lang('modules.dashboard.userActivityTimeline')</div>
                    <div class="panel-wrapper collapse in">
                        <div class="panel-body">
                            <div class="steamline">
                                @forelse($userActivities as $key=>$activity)
                                    <div class="sl-item">
                                        <div class="sl-left">
                                            {!!  ($activity->user->image) ? '<img src="'.asset_url('avatar/'.$activity->user->image).'"
                                                                        alt="user" class="img-circle">' : '<img src="'.asset('img/default-profile-2.png').'"
                                                                        alt="user" class="img-circle">' !!}
                                        </div>
                                        <div class="sl-right">
                                            <div class="m-l-40"><a
                                                        href="{{ route('installer.employees.show', $activity->user_id) }}"
                                                        class="text-success">{{ ucwords($activity->user->name) }}</a>
                                                <span class="sl-date">{{ $activity->created_at->diffForHumans() }}</span>
                                                <p>{!! ucfirst($activity->activity) !!}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @if(count($userActivities) > ($key+1))
                                        <hr>
                                    @endif
                                @empty
                                    <div>@lang("messages.noActivityByThisUser")</div>
                                @endforelse
                            </div>
                        </div>
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
            var url = "{{ route('installer.all-tasks.show',':id') }}";
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
            var url = '{{ route('installer.sub-task.create')}}?task_id='+id;

            $('#subTaskModelHeading').html('Sub Task');
            $.ajaxModal('#subTaskModal', url);
        })

        $('.keep-open .dropdown-menu').on({
            "click":function(e){
                e.stopPropagation();
            }
        });


    </script>
@endpush
