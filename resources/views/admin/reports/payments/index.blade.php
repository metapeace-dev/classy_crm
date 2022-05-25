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
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/custom-select/custom-select.css') }}">

<link rel="stylesheet" href="{{ asset('plugins/bower_components/morrisjs/morris.css') }}">

<link rel="stylesheet" href="https://cdn.datatables.net/1.10.13/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.1.1/css/responsive.bootstrap.min.css">
<link rel="stylesheet" href="//cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css">
@endpush

@section('content')

    <div class="col-md-12">

        <section>
            <div class="sttabs tabs-style-line">
                <div class="content-wrap">
                    <div class="white-box">
                        <div class="row m-b-10">
                            <h2>@lang("app.filterResults")</h2>
                            {!! Form::open(['id'=>'report_filter_form','class'=>'ajax-form','method'=>'POST']) !!}

                            <div class="col-md-4">
                                <div class="example">
                                    <h5 class="box-title m-t-30">@lang('app.selectDateRange')</h5>

                                    <div class="input-daterange input-group" id="date-range">
                                        <input type="text" class="form-control" id="start-date" name="startDate" placeholder="@lang('app.startDate')"
                                               value="{{ $fromDate->format($global->date_format) }}"/>
                                        <span class="input-group-addon bg-info b-0 text-white">@lang('app.to')</span>
                                        <input type="text" class="form-control" id="end-date" name="endDate" placeholder="@lang('app.endDate')"
                                               value="{{ $toDate->format($global->date_format) }}"/>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <h5 class="box-title m-t-30">@lang('app.designer_role')</h5>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <select class="select2 form-control" data-placeholder="@lang('app.designer_role')" id="designer_id" name="designer_id">
                                                <option value="">All</option>
                                                @foreach($designers as $designer)
                                                    <option value="{{ $designer->id }}">{{ ucwords($designer->name) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <h5 class="box-title m-t-30">Status</h5>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <select class="select2 form-control" data-placeholder="Status" id="status" name="status">
                                                <option value="">All</option>
                                                @foreach($statuses as $status)
                                                    <option value="{{$status}}">{{ucfirst($status)}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <h5 class="box-title m-t-30">Projects</h5>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <select class="select2 form-control" data-placeholder="Projects" id="project" name="project">
                                                <option value="">All</option>
                                                @foreach($projects as $key => $project)
                                                    <option value="{{ $key }}">{{ ucwords($project) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <h5 class="box-title m-t-30">Payment Type</h5>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <select class="select2 form-control" data-placeholder="Payment Type" id="payment_type" name="payment_type">
                                                <option value="">All</option>
                                                @foreach($payment_types as $type)
                                                    <option value="{{$type}}">{{ucfirst($type)}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <button type="button" class="btn btn-success" id="filter-results"><i class="fa fa-check"></i> @lang('app.apply')
                                </button>
                            </div>
                            {!! Form::close() !!}

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="white-box">
                                <h2>@lang("app.results")</h2>
                                <div class="row">
                                    <div class="col-sm-6">
                                    </div>
                                    <div class="col-sm-6 text-right hidden-xs">
                                        <div class="form-group">
                                            <a href="javascript:;" onclick="exportData()" class="btn btn-info btn-sm"><i class="ti-export" aria-hidden="true"></i> @lang('app.exportExcel')</a>
                                            <a href="javascript:;" onclick="exportPDFData()" class="btn btn-info btn-sm"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> @lang('app.exportPDF')</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover toggle-circle default footable-loaded footable"
                                           id="tasks-table">
                                        <thead>
                                            <th>@lang('app.id')</th>
                                                @foreach($fieldsData as $key => $value)
                                                    @if($value == 'active')
                                                        <th>{{ Lang::has('modules.payments.'.$key) ? __('modules.payments.'.$key) : __('modules.payments.'.Illuminate\Support\Str::camel($key)) }}</th>
                                                    @endif
                                                @endforeach
                                            <th>@lang('app.createdOn')</th>
                                            <th>@lang('app.action')</th>
                                        </thead>
                                    </table>
                                </div>
                            </div>

                        </div>

                    </div>
                </div><!-- /content -->
            </div><!-- /tabs -->
        </section>
    </div>


@endsection

@push('footer-script')


<script src="{{ asset('plugins/bower_components/Chart.js/Chart.min.js') }}"></script>

<script src="{{ asset('plugins/bower_components/raphael/raphael-min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/morrisjs/morris.js') }}"></script>

<script src="{{ asset('plugins/bower_components/custom-select/custom-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>

<script src="{{ asset('plugins/bower_components/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('plugins/bower_components/waypoints/lib/jquery.waypoints.js') }}"></script>
<script src="{{ asset('plugins/bower_components/counterup/jquery.counterup.min.js') }}"></script>



<script src="{{ asset('plugins/bower_components/datatables/jquery.dataTables.min.js') }}"></script>
<script src="https://cdn.datatables.net/1.10.13/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.1.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.1.1/js/responsive.bootstrap.min.js"></script>

<script>

    $(".select2").select2({
        formatNoMatches: function () {
            return "{{ __('messages.noRecordFound') }}";
        }
    });

    jQuery('#date-range').datepicker({
        toggleActive: true,
        format: '{{ $global->date_picker_format }}',
    });

    $('#filter-results').click(function () {
        showTable();
    })

</script>

<script>
    var index = 1;
    var index_arr = [];
    @foreach($fieldsData as $key => $value)
        @if($value == 'active')
            @if($key == 'paid_on')
                index_arr.push(index);
            @endif
            index++;
        @endif
    @endforeach

    function setStyleColumns(){
        let res = [{
            targets: index,
            className: 'nowrap'
        }];
        index_arr.forEach(function(data, index){
            res.push({
                targets: data,
                className: 'nowrap'
            })
        });
        return res;
    }
    showTable();
    var table;

    function showTable() {
        let url = '{!!  route('admin.payment-report.data') !!}';

        let reqData = {};
        $.each($('#report_filter_form').serializeArray(), function(_, kv) {
            reqData[kv.name] = kv.value;
        });

        table = $('#tasks-table').dataTable({
            lengthMenu: [ 10, 25, 50, 100 ],
            pageLength : 25,
            destroy: true,
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: url,
                type: "POST",
                data : reqData,
            },
            deferRender: true,
            language: {
                "url": "<?php echo __("app.datatable") ?>"
            },
            "fnDrawCallback": function (oSettings) {
                $("body").tooltip({
                    selector: '[data-toggle="tooltip"]'
                });
            },
            order: [],
            columns: [
                {data: 'id', name: 'id'},
                    @foreach($fieldsData as $key => $value)
                    @if($value == 'active')
                {data: '{{ $key }}', name: '{{ $key }}'},
                    @endif
                    @endforeach
                {data: 'created_at', name: 'created_at'},
                {data: 'action', name: 'action'},
            ],
            columnDefs: setStyleColumns()
        });
    }

    function exportData(){
        let url = '{!!  route('admin.payment-report.export') !!}'+'?'+$('#report_filter_form').serialize();
        window.location.href = url;
    }

    function exportPDFData(){
        let url = '{!!  route('admin.payment-report.export-pdf') !!}';
        let form = document.getElementById("report_filter_form");
        form.action = url;
        form.submit();
    }

    $(document).on('click', '.btn_send_email',function(){
        let id = $(this).data('id');
        var url = '{{ route("admin.payment-report.email", [':id']) }}';
        url = url.replace(':id', id);

        $.easyAjax({
            url: url,
            type: "GET",
            success: function (response) {

            }
        })
    });

</script>
@endpush