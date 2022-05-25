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
                <li><a href="{{ route('admin.employees.index') }}">{{ $pageTitle }}</a></li>
                <li class="active">@lang('app.details')</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@push('head-script')
<style>
    .counter{
        font-size: large;
    }
</style>

<link rel="stylesheet" href="https://cdn.datatables.net/1.10.13/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.1.1/css/responsive.bootstrap.min.css">
<link rel="stylesheet" href="//cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css">
@endpush

@section('content')
        <!-- .row -->
<div class="row">
    <div class="col-md-5 col-xs-12">
        <div class="white-box">
            <div class="user-bg">
                {!!  ($employee->image) ? '<img src="'.asset_url('avatar/'.$employee->image).'"
                                                            alt="user" width="100%">' : '<img src="'.asset('img/default-profile-2.png').'"
                                                            alt="user" width="100%">' !!}
                <div class="overlay-box">
                    <div class="user-content"> <a href="javascript:void(0)">
                            {!!  ($employee->image) ? '<img src="'.asset_url('avatar/'.$employee->image).'"
                                                            alt="user" class="thumb-lg img-circle">' : '<img src="'.asset('img/default-profile-2.png').'"
                                                            alt="user" class="thumb-lg img-circle">' !!}
                            </a>
                        <h4 class="text-white">{{ ucwords($employee->name) }}</h4>
                        <h5 class="text-white">{{ $employee->email }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-7 col-xs-12">
        <div class="white-box">
            <ul class="nav nav-tabs tabs customtab">
                <li class="active tab"><a href="#profile" data-toggle="tab"> <span class="visible-xs"><i class="fa fa-user"></i></span> <span class="hidden-xs">@lang('modules.employees.profile')</span> </a> </li>
                <li class="tab"><a href="#home" data-toggle="tab"> <span class="visible-xs"><i class="fa fa-home"></i></span> <span class="hidden-xs">@lang('modules.employees.activity')</span> </a> </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane" id="home">
                    <div class="steamline">
                        @forelse($activities as $key=>$activity)
                        <div class="sl-item">
                            <div class="sl-left">
                                {!!  ($employee->image) ? '<img src="'.asset_url('avatar/'.$employee->image).'"
                                                            alt="user" class="img-circle">' : '<img src="'.asset('img/default-profile-2.png').'"
                                                            alt="user" class="img-circle">' !!}
                            </div>
                            <div class="sl-right">
                                <div class="m-l-40"><a href="#" class="text-info">{{ ucwords($employee->name) }}</a> <span  class="sl-date">{{ $activity->created_at->diffForHumans() }}</span>
                                    <p>{!! ucfirst($activity->activity) !!}</p>
                                </div>
                            </div>
                        </div>
                            @if(count($activities) > ($key+1))
                                <hr>
                            @endif
                        @empty
                            <div>@lang('messages.noActivityByThisUser')</div>
                        @endforelse
                    </div>
                </div>
                <div class="tab-pane active" id="profile">
                    <div class="row">
                        <div class="col-xs-6 col-md-4  b-r"> <strong>@lang('modules.employees.fullName')</strong> <br>
                            <p class="text-muted">{{ ucwords($employee->name) }}</p>
                        </div>
                        <div class="col-xs-6 col-md-4 "> <strong>@lang('app.mobile')</strong> <br>
                            <p class="text-muted">{{ $employee->mobile ?? 'NA'}}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 col-xs-6 b-r"> <strong>@lang('app.email')</strong> <br>
                            <p class="text-muted">{{ $employee->email }}</p>
                        </div>
                        <div class="col-md-4 col-xs-6"> <strong>@lang('app.address')</strong> <br>
                            <p class="text-muted">{{ (!is_null($employee->employeeDetail)) ? $employee->employeeDetail->address : 'NA'}}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 col-xs-6"> <strong>@lang('modules.employees.joiningDate')</strong> <br>
                            <p class="text-muted">{{ (!is_null($employee->employeeDetail)) ? $employee->employeeDetail->joining_date->format($global->date_format) : 'NA' }}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 col-xs-6 b-r"> <strong>@lang('modules.employees.gender')</strong> <br>
                            <p class="text-muted">{{ $employee->gender }}</p>
                        </div>
                    </div>
                    {{--Custom fields data--}}
                        @if(isset($fields))
                        <div class="row">
                            <hr>
                            @foreach($fields as $field)
                                <div class="col-md-6">
                                    <strong>{{ ucfirst($field->label) }}</strong> <br>
                                    <p class="text-muted">
                                        @if( $field->type == 'text')
                                            {{$employeeDetail->custom_fields_data['field_'.$field->id] ?? ''}}
                                        @elseif($field->type == 'password')
                                            {{$employeeDetail->custom_fields_data['field_'.$field->id] ?? ''}}
                                        @elseif($field->type == 'number')
                                            {{$employeeDetail->custom_fields_data['field_'.$field->id] ?? ''}}

                                        @elseif($field->type == 'textarea')
                                        {{$employeeDetail->custom_fields_data['field_'.$field->id] ?? ''}}

                                        @elseif($field->type == 'radio')
                                            {{ $field->values[$employeeDetail->custom_fields_data['field_'.$field->id]] }}
                                        @elseif($field->type == 'select')
                                                {{ $field->values[$employeeDetail->custom_fields_data['field_'.$field->id]] }}
                                        @elseif($field->type == 'checkbox')
                                            {{ $field->values[$employeeDetail->custom_fields_data['field_'.$field->id]] }}
                                        @elseif($field->type == 'date')
                                            {{ isset($employeeDetail->dob)?Carbon\Carbon::parse($employeeDetail->dob)->format($global->date_format):Carbon\Carbon::now()->format($global->date_format)}}
                                        @endif
                                    </p>

                                </div>
                            @endforeach
                        </div>
                        @endif

                    {{--custom fields data end--}}

                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.row -->
        {{--Ajax Modal--}}
        <div class="modal fade bs-modal-md in" id="edit-column-form" role="dialog" aria-labelledby="myModalLabel"
             aria-hidden="true">
            <div class="modal-dialog modal-md" id="modal-data-application">
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
<script src="{{ asset('plugins/bower_components/datatables/jquery.dataTables.min.js') }}"></script>
<script src="https://cdn.datatables.net/1.10.13/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.1.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.1.1/js/responsive.bootstrap.min.js"></script>
<script>
    // Show Create employeeDocs Modal
    function showAdd() {
        var url = "{{ route('admin.employees.docs-create', [$employee->id]) }}";
        $.ajaxModal('#edit-column-form', url);
    }

    $('body').on('click', '.sa-params', function () {
        var id = $(this).data('file-id');
        var deleteView = $(this).data('pk');
        swal({
            title: "Are you sure?",
            text: "You will not be able to recover the deleted file!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, cancel please!",
            closeOnConfirm: true,
            closeOnCancel: true
        }, function (isConfirm) {
            if (isConfirm) {

                var url = "{{ route('admin.employee-docs.destroy',':id') }}";
                url = url.replace(':id', id);

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {'_token': token, '_method': 'DELETE', 'view': deleteView},
                    success: function (response) {
                        console.log(response);
                        if (response.status == "success") {
                            $.unblockUI();
                            $('#employeeDocsList').html(response.html);
                        }
                    }
                });
            }
        });
    });

    $('#leave-table').dataTable({
        responsive: true,
        "columnDefs": [
            { responsivePriority: 1, targets: 0, 'width': '20%' },
            { responsivePriority: 2, targets: 1, 'width': '20%' }
        ],
        "autoWidth" : false,
        searching: false,
        paging: false,
        info: false
    });

    var table;

    function showTable() {

        if ($('#hide-completed-tasks').is(':checked')) {
            var hideCompleted = '1';
        } else {
            var hideCompleted = '0';
        }

        var url = '{{ route('admin.employees.tasks', [$employee->id, ':hideCompleted']) }}';
        url = url.replace(':hideCompleted', hideCompleted);

        table = $('#tasks-table').dataTable({
            destroy: true,
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: url,
            deferRender: true,
            language: {
                "url": "<?php echo __("app.datatable") ?>"
            },
            "fnDrawCallback": function (oSettings) {
                $("body").tooltip({
                    selector: '[data-toggle="tooltip"]'
                });
            },
            "order": [[0, "desc"]],
            columns: [
                {data: 'id', name: 'id'},
                {data: 'project_name', name: 'projects.project_name', width: '20%'},
                {data: 'heading', name: 'heading', width: '20%'},
                {data: 'due_date', name: 'due_date'},
                {data: 'status', name: 'status'}
            ]
        });
    }

    $('#hide-completed-tasks').click(function () {
        showTable();
    });

    showTable();
</script>

<script>
    var table2;

    function showTable2(){

        var url = '{{ route('admin.employees.time-logs', [$employee->id]) }}';

        table2 = $('#timelog-table').dataTable({
            destroy: true,
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: url,
            deferRender: true,
            language: {
                "url": "<?php echo __("app.datatable") ?>"
            },
            "fnDrawCallback": function( oSettings ) {
                $("body").tooltip({
                    selector: '[data-toggle="tooltip"]'
                });
            },
            "order": [[ 0, "desc" ]],
            columns: [
                { data: 'id', name: 'id' },
                { data: 'project_name', name: 'projects.project_name' },
                { data: 'start_time', name: 'start_time' },
                { data: 'end_time', name: 'end_time' },
                { data: 'total_hours', name: 'total_hours' },
                { data: 'memo', name: 'memo' }
            ]
        });
    }

    showTable2();
</script>
@endpush

