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
                <li><a href="{{ route('admin.payments.index') }}">{{ $pageTitle }}</a></li>
                <li class="active">@lang('app.update')</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@push('head-script')
<link rel="stylesheet" href="{{ asset('plugins/bower_components/custom-select/custom-select.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/switchery/dist/switchery.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datetime-picker/datetimepicker.css') }}">

@endpush

@section('content')

    <div class="row">
        <div class="col-md-12">

            <div class="panel ">
                <div class="panel-heading"> @lang('modules.payments.updatePayment') - #{{$payment->id}}</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        {!! Form::open(['id'=>'updatePayment','class'=>'ajax-form','method'=>'PUT']) !!}
                        @if(empty($payment->commission))
                            <input type="hidden" name="create_commission" id="create_commission" value="0">
                        @else
                            <input type="hidden" name="update_commission" id="update_commission" value="0">
                        @endif
                        <div class="form-body">

                            <div class="row">

                                <div class="col-md-12 ">
                                    <div class="form-group">
                                        <label>@lang('app.selectProject')</label>
                                        <select class="select2 form-control" data-placeholder="@lang('app.selectProject')" name="project_id">
                                            <option value=""></option>
                                            @foreach($projects as $project)
                                                <option
                                                        @if($project->id == $payment->project_id) selected @endif
                                                        value="{{ $project->id }}">{{ $project->project_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Client</label>
                                        <input type="text" class="form-control" id="client_name" value="{{ $payment->project->client->full_name ?? '' }}" disabled>
                                    </div>
                                </div>
                                <div class="col-md-6 ">
                                    <div class="form-group">
                                        <label class="control-label">Designer</label>
                                        <input type="text" class="form-control" id="designer_name" value="{{ $payment->project->designer->name ?? '' }}" disabled>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label">Sales Price</label>
                                        <input type="text" class="form-control" id="sales_price" value="{{ '$'.number_format($payment->project->sales_price, 2, '.', ',') }}"  disabled>
                                    </div>
                                </div>

                                <?php
                                if($payment->project->discount_type == 'dollar'){
                                    $due_amount = $payment->project->sales_price - $payment->project->discount - $paid_amount;
                                    $discount_amount = $payment->project->discount;
                                    $discount_type = '$';
                                }
                                else{
                                    $due_amount = $payment->project->sales_price - $payment->project->sales_price * $payment->project->discount / 100 - $paid_amount;
                                    $discount_amount = $payment->project->sales_price * $payment->project->discount / 100;
                                    $discount_type = '%';
                                }
                                if($due_amount < 0){
                                    $due_amount = 0;
                                }
                                ?>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label">Discount</label>
                                        <input type="text" class="form-control" id="discount" value="{{ !empty($payment->project->discount) ? $payment->project->discount.$discount_type : '' }}" disabled>
                                    </div>
                                </div>


                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label">Paid Amount</label>
                                        <input type="text" class="form-control" id="paid_amount" value="{{ '$'.number_format($paid_amount, 2, '.', ',') ?? '' }}" disabled>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label">Due Amount</label>
                                        <input type="text" class="form-control" id="due_amount" value="{{ '$'.number_format($due_amount, 2, '.', ',') }}" disabled>
                                    </div>
                                </div>

                                <?php
                                if($payment->project->commission_type == 'dollar'){
                                    $commission_price = $payment->project->commission;
                                    if(!empty($commissions->paidCommissions)){
                                        $due_commission = $commission_price - $commissions->paidCommissions;
                                    }
                                    else{
                                        $due_commission = $commission_price;
                                    }
                                    $commission_type = '$';
                                }
                                else{
                                    $commission_price= ($payment->project->sales_price - $discount_amount) * $payment->project->commission/100 ;
                                    if(!empty($commissions->paidCommissions)){
                                        $due_commission = $commission_price - $commissions->paidCommissions;
                                    }
                                    else{
                                        $due_commission = $commission_price;
                                    }

                                    $commission_type = '%';
                                }
                                if($due_commission < 0){
                                    $due_commission = 0;
                                }
                                ?>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label">Commission</label>
                                        <input type="text" class="form-control" id="commission_rate" value="{{ !empty($payment->project->commission) ? $payment->project->commission.$commission_type : '' }}" disabled>
                                        <div class="help-block" style="display: none"></div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label">Commission Price</label>
                                        <input type="text" class="form-control" id="commission_price" value="{{ '$'.number_format($commission_price, 2, '.', ',') }}" disabled>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label">Paid Commission</label>
                                        <input type="text" class="form-control" id="paid_commission" value="{{ '$'.number_format($commissions->paidCommissions, 2, '.', ',') }}" disabled>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label">Due Commission</label>
                                        <input type="text" class="form-control" id="due_commission" value="{{ '$'.number_format($due_commission, 2, '.', ',') }}" disabled>
                                    </div>
                                </div>


                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>@lang('modules.invoices.amount')</label>
                                        <div class="col-md-12 input-group">
                                            <div class="input-group-addon">
                                                <i class="fa fa-usd" aria-hidden="true"></i>
                                            </div>
                                            <input type="text" name="amount" id="amount" value="{{ number_format((float)$payment->amount, 2, '.', ',') }}" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <!--/span-->

                            </div>
                            <!--/row-->

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">@lang('modules.payments.paidOn')</label>
                                        <input type="text" class="form-control" name="paid_on"  id="paid_on" @if(is_null($payment->paid_on)) value="{{ Carbon\Carbon::today()->format($global->date_format) }}" @else value="{{ $payment->paid_on->format($global->date_format) }}" @endif>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">@lang('app.remark')</label>
                                        <textarea id="remarks" name="remarks" class="form-control">{{ $payment->remarks }}</textarea>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">Payment Type</label>
                                        <select name="payment_type" id="payment_type" class="form-control">
                                            <option @if($payment->payment_type == 'deposit') selected @endif value="deposit">Deposit</option>
                                            <option @if($payment->payment_type == 'progress') selected @endif value="progress">Progress</option>
                                            <option @if($payment->payment_type == 'final') selected @endif value="final">Final</option>
                                            <option @if($payment->payment_type == 'adjustment') selected @endif value="adjustment">Adjustment</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">@lang('app.status')</label>
                                        <select name="status" id="status" class="form-control">
                                            <option @if($payment->status == 'complete') selected @endif value="complete">@lang('app.completed')</option>
                                            <option @if($payment->status == 'pending') selected @endif value="pending">@lang('app.pending')</option>
                                        </select>
                                    </div>
                                </div>

                            </div>
                            <!--/span-->

                            @if(!empty($payment->commission))
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Commission ID</label>
                                            <input type="text" id="commission_id" value="{{ $payment->commission->id }}" class="form-control" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Commission @lang('modules.invoices.amount')</label>
                                            <div class="col-md-12 input-group">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-usd" aria-hidden="true"></i>
                                                </div>
                                                <input type="text" id="commission_amount" value="{{ number_format((float)$payment->commission->amount, 2, '.', ',') }}" class="form-control" disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="form-actions">
                            <button type="submit" id="save-form-2" class="btn btn-success"><i class="fa fa-check"></i>
                                @lang('app.update')
                            </button>
                            <button type="reset" class="btn btn-default">@lang('app.reset')</button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>    <!-- .row -->

@endsection

@push('footer-script')
<script src="{{ asset('plugins/bower_components/custom-select/custom-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/switchery/dist/switchery.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/moment/moment.js') }}"></script>
<script src="{{ asset('plugins/datetime-picker/datetimepicker.js') }}"></script>
<script>
    var isCommission = 0;
    @if(!empty($payment->commission))
        isCommission = 1;
    @endif
    // Switchery
    var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
    $('.js-switch').each(function() {
        new Switchery($(this)[0], $(this).data());

    });

    $(".select2").select2({
        formatNoMatches: function () {
            return "{{ __('messages.noRecordFound') }}";
        }
    });

    jQuery('#paid_on').datepicker({
        format: '{{ $global->date_picker_format }}',
        autoclose: true,
        todayHighlight: true
    });

    $('.select2').on('change.select2', function (e) {
        let project_id = $(this).val();
        var url = '{{route("admin.projects.getdetail", ":id")}}';
        url = url.replace(':id', project_id);
        var token = "{{ csrf_token() }}";
        $.easyAjax({
            url: url,
            container: '#updatePayment',
            type: "POST",
            data: { _token : token },
            success: function (response) {
                if(response.status == 'success'){
                    let discount_type = ''; let commission_type = '';
                    let discount_amount = 0, commission_price = 0, due_commission = 0, paid_amount=0, due_amount = 0, paid_commission = 0;
                    $('#client_name').val(response.data.client_name);
                    $('#designer_name').val(response.data.designer_name);
                    $('#sales_price').val('$'+number_format(response.data.sales_price, 2));
                    if(response.data.discount_type && response.data.discount_type == 'percentage'){
                        discount_type = '%';
                        discount_amount = response.data.sales_price * response.data.discount / 100;
                    }
                    else if(response.data.discount_type && response.data.discount_type == 'dollar'){
                        discount_type = '$';
                        discount_amount = response.data.discount;
                    }
                    if(response.data.discount <= 0){
                        discount_type = '';
                    }
                    $('#discount').val(response.data.discount + discount_type);
                    if(response.data.paid_amount){
                        paid_amount = '$'+number_format(response.data.paid_amount, 2);
                    }
                    else{
                        paid_amount = '$' + number_format(0.00,2);
                    }
                    $('#paid_amount').val(paid_amount);
                    due_amount = response.data.sales_price - discount_amount - response.data.paid_amount;
                    if(due_amount < 0){
                        due_amount = 0;
                    }
                    $('#due_amount').val('$' + number_format(due_amount, 2));

                    if(response.data.commission_type && response.data.commission_type == 'percentage'){
                        commission_type = '%';
                        commission_price = (response.data.sales_price - discount_amount) * response.data.commission/100;
                        if(response.data.paid_commissions){
                            due_commission = commission_price - response.data.paid_commissions;
                        }
                        else{
                            due_commission = commission_price;
                        }

                    }
                    else if(response.data.commission_type && response.data.commission_type == 'dollar'){
                        commission_type = '$';
                        commission_price = response.data.commission;
                        if(response.data.paid_commissions){
                            due_commission = response.data.commission - response.data.paid_commissions;
                        }
                        else{
                            due_commission = response.data.commission;
                        }
                    }

                    if(!response.data.commission){
                        $('#commission_rate').parent().addClass("has-error");
                        $('#commission_rate').parent().find($('div')).text('This field should not be 0');
                        $('#commission_rate').parent().find($('div')).show();
                    }
                    else{
                        $('#commission_rate').parent().removeClass("has-error");
                        $('#commission_rate').parent().find($('div')).hide();
                    }

                    commission_price = '$'+number_format(commission_price, 2)
                    paid_commission = '$'+number_format(response.data.paid_commissions, 2);
                    $('#commission_rate').val(response.data.commission + commission_type);
                    $('#commission_price').val(commission_price);

                    $('#paid_commission').val(paid_commission);
                    if(due_commission && due_commission > 0){
                        due_commission = '$'+number_format(due_commission, 2);
                    }
                    else{
                        due_commission = '$' + number_format(0.00,2);
                    }
                    $('#due_commission').val(due_commission);
                }
            }
        })
    });

    if(isCommission){
        $('#save-form-2').click(function () {
            swal({
                title: "Do you want to update a commission for this payment?",
                type: "info",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes",
                cancelButtonText: "No",
                closeOnConfirm: true,
                closeOnCancel: true
            }, function(isConfirm){
                if (isConfirm) {
                    $('#update_commission').val(1);
                    $.easyAjax({
                        url: '{{route('admin.payments.update', $payment->id)}}',
                        container: '#updatePayment',
                        type: "POST",
                        data: $('#updatePayment').serialize()
                    })
                }
                else{
                    $.easyAjax({
                        url: '{{route('admin.payments.update', $payment->id)}}',
                        container: '#updatePayment',
                        type: "POST",
                        data: $('#updatePayment').serialize()
                    })
                }
            });

        });
    }
    else{
        $('#save-form-2').click(function () {
            swal({
                title: "Do you want to create a commission for this payment?",
                type: "info",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes",
                cancelButtonText: "No",
                closeOnConfirm: true,
                closeOnCancel: true
            }, function(isConfirm){
                if (isConfirm) {
                    $('#create_commission').val(1);
                    $.easyAjax({
                        url: '{{route('admin.payments.update', $payment->id)}}',
                        container: '#updatePayment',
                        type: "POST",
                        redirect: true,
                        data: $('#updatePayment').serialize()
                    })
                }
                else{
                    $.easyAjax({
                        url: '{{route('admin.payments.update', $payment->id)}}',
                        container: '#updatePayment',
                        type: "POST",
                        redirect: true,
                        data: $('#updatePayment').serialize()
                    })
                }
            });

        });
    }
</script>
@endpush