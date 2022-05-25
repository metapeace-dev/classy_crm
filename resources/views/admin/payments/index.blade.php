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
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.13/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.1.1/css/responsive.bootstrap.min.css">
<link rel="stylesheet" href="//cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css">
<link rel="stylesheet" href="//cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/custom-select/custom-select.css') }}">
@endpush

@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <a href="{{ route('admin.payments.create') }}" class="btn btn-outline btn-success btn-sm">@lang('modules.payments.addPayment') <i class="fa fa-plus" aria-hidden="true"></i></a>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover toggle-circle default footable-loaded footable" id="invoice-table">
                        <thead>
                        <tr>
                            <th>@lang('app.id')</th>
                            <th>@lang('app.project') @lang('app.id')</th>
                            <th>@lang('app.project')</th>
                            <th>@lang('modules.payments.designer')</th>
                            <th>@lang('modules.payments.amount')</th>
                            <th>@lang('modules.payments.paidOn')</th>
                            <th>@lang('modules.payments.payment_type')</th>
                            <th>@lang('app.status')</th>
                            <th>@lang('app.remark')</th>
                            <th>@lang('app.action')</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- .row -->

@endsection

@push('footer-script')
<script src="{{ asset('plugins/bower_components/datatables/jquery.dataTables.min.js') }}"></script>
<script src="https://cdn.datatables.net/1.10.13/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.1.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.1.1/js/responsive.bootstrap.min.js"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('plugins/bower_components/custom-select/custom-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.js') }}"></script>


<script>
    $(".select2").select2({
        formatNoMatches: function () {
            return "{{ __('messages.noRecordFound') }}";
        }
    });
    var table;
    $(function() {
        jQuery('#date-range').datepicker({
            toggleActive: true,
            format: '{{ $global->date_picker_format }}',
            language: '{{ $global->locale }}',
            autoclose: true
        });
        loadTable();

        $('body').on('click', '.sa-params', function(){
            var id = $(this).data('payment-id');
            swal({
                title: "Are you sure?",
                text: "You will not be able to recover the deleted payment record!",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "No, cancel please!",
                closeOnConfirm: true,
                closeOnCancel: true
            }, function(isConfirm){
                if (isConfirm) {

                    var url = "{{ route('admin.payments.destroy',':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                            url: url,
                            data: {'_token': token, '_method': 'DELETE'},
                        success: function (response) {
                            if (response.status == "success") {
                                $.unblockUI();
//                                    swal("Deleted!", response.message, "success");
                                table._fnDraw();
                            }
                        }
                    });
                }
            });
        });

        $('#import-excel').click(function () {
            $.easyAjax({
                url: '{{route('admin.payments.importExcel')}}',
                container: '#importExcel',
                type: "POST",
                redirect: true,
                file: (document.getElementById("import_file").files.length == 0) ? false : true
            })
        });


    });

    function loadTable(){


        var startDate = '';

        if (startDate == '') {
            startDate = null;
        }

        var endDate = '';

        if (endDate == '') {
            endDate = null;
        }

        var status = '';
        var project = '';
        var client = '';

        table = $('#invoice-table').dataTable({
            lengthMenu: [ 10, 25, 50, 100 ],
            pageLength : 25,
            responsive: true,
            destroy: true,
            processing: true,
            serverSide: true,
            stateSave: false,
            ajax: '{!! route('admin.payments.data') !!}?startDate=' + startDate + '&endDate=' + endDate + '&status=' + status + '&project=' + project + '&client=' + client,
            "order": [[ 0, "desc" ]],
            language: {
                "url": "<?php echo __("app.datatable") ?>"
            },
            "fnDrawCallback": function( oSettings ) {
                $("body").tooltip({
                    selector: '[data-toggle="tooltip"]'
                });
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'project_id', name: 'project_id' },
                { data: 'project_name', name: 'project_name' },
                { data: 'designer', name: 'designer' },
                { data: 'amount', name: 'amount' },
                { data: 'paid_on', name: 'paid_on' },
                { data: 'payment_type', name: 'payment_type' },
                { data: 'status', name: 'status' },
                { data: 'remarks', name: 'remarks' },
                { data: 'action', name: 'action', width: '10%' }
            ]
        });
    }

    $('.toggle-filter').click(function () {
        $('#ticket-filters').toggle('slide');
    })

    $('#apply-filters').click(function () {
        loadTable();
    });

    $('#reset-filters').click(function () {
        $('#filter-form')[0].reset();
        $('.select2').val('all');
       $('#project').select2();
       $('#client').select2();
        loadTable();
    })

    function exportData(){

        var startDate = $('#start-date').val();

        if (startDate == '') {
            startDate = null;
        }

        var endDate = $('#end-date').val();

        if (endDate == '') {
            endDate = null;
        }

        var status = $('#status').val();

        var project = $('#project').val();

        var url = '{{ route('admin.payments.export', [':startDate', ':endDate', ':status', ':project']) }}';
        url = url.replace(':startDate', startDate);
        url = url.replace(':endDate', endDate);
        url = url.replace(':status', status);
        url = url.replace(':project', project);

        window.location.href = url;
    }


</script>
@endpush