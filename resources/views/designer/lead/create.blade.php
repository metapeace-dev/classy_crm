@extends('layouts.designer-app')

@section('page-title')
    <div class="row bg-title">
        <!-- .page title -->
        <div class="col-lg-6 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title"><i class="{{ $pageIcon }}"></i> {{ $pageTitle }}</h4>
        </div>
        <!-- /.page title -->
        <!-- .breadcrumb -->
        <div class="col-lg-6 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="{{ route('designer.dashboard') }}">@lang('app.menu.home')</a></li>
                <li><a href="{{ route('designer.leads.index') }}">{{ $pageTitle }}</a></li>
                <li class="active">@lang('app.addNew')</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@push('head-script')
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
@endpush

@section('content')

    <div class="row">
        <div class="col-md-12">

            <div class="panel panel-inverse">
                <div class="panel-heading"> @lang('modules.lead.createTitle')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        {!! Form::open(['id'=>'createLead','class'=>'ajax-form','method'=>'POST']) !!}
                            <div class="form-body">
                            <div class="row">
                                <div class="col-md-7">
                                    <div class="form-group">
                                        <label class="control-label">@lang('modules.lead.companyName')</label>
                                        <input type="text" id="company_name" name="company_name" class="form-control" >
                                    </div>
                                    <div class="form-group">
                                        <div class="col-md-6 no-padding-left">
                                            <div class="form-group">
                                                <label class="control-label">@lang('modules.lead.first_name')</label>
                                                <input type="text" id="first_name" name="first_name" class="form-control" >
                                            </div>
                                        </div>
                                        <div class="col-md-6 no-padding">
                                            <div class="form-group">
                                                <label class="control-label">@lang('modules.lead.last_name')</label>
                                                <input type="text" id="last_name" name="last_name" class="form-control" >
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">@lang('app.address')</label>
                                        <input type="text" name="address"  id="address" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">@lang('modules.lead.city')</label>
                                        <input type="text" name="city"  id="city" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <div class="col-md-6 no-padding-left">
                                            <div class="form-group">
                                                <label class="control-label">@lang('modules.lead.state')</label>
                                                <select class="form-control" id="state" name="state">
                                                    @foreach($states as $key => $value)
                                                        <option value="{{ $key }}">{{ucfirst($value)}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 no-padding">
                                            <div class="form-group">
                                                <label class="control-label">@lang('modules.lead.zip')</label>
                                                <input type="text" name="zip"  id="zip" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <div class="col-md-8 no-padding-left">
                                            <div class="form-group">
                                                <label>@lang('modules.lead.phone')</label>
                                                <input type="tel" name="phone" id="phone" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-4 no-padding">
                                            <div class="form-group">
                                                <label>@lang('modules.lead.ext')</label>
                                                <input type="text" name="ext" id="ext" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>@lang('modules.lead.cell')</label>
                                        <input type="tel" name="cell" id="cell" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>@lang('modules.lead.fax')</label>
                                        <input type="tel" name="fax" id="fax" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>@lang('modules.lead.email')</label>
                                        <input type="email" name="email" id="email" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>@lang('modules.lead.ref')</label>
                                        <input type="text" name="ref" id="ref" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                            <div class="form-actions">
                                <button type="submit" id="save-form" class="btn btn-success"> <i class="fa fa-check"></i> @lang('app.save')</button>
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
<script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>

<script>
    $(".date-picker").datepicker({
        todayHighlight: true,
        autoclose: true
    });

    $('#save-form').click(function () {
        $.easyAjax({
            url: '{{route('designer.leads.store')}}',
            container: '#createLead',
            type: "POST",
            redirect: true,
            data: $('#createLead').serialize()
        })
    });

</script>
@endpush

