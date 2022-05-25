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
                <li><a href="{{ route('admin.clients.index') }}">{{ $pageTitle }}</a></li>
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
            {{--{!!   $smtpSetting->set_smtp_message !!}--}}
            <div class="panel panel-inverse">
                <div class="panel-heading"> @lang('modules.client.createTitle')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        {!! Form::open(['id'=>'createClient','class'=>'ajax-form','method'=>'POST']) !!}
                        @if(isset($leadDetail->id))
                            <input type="hidden" name="lead" value="{{ $leadDetail->id }}">
                        @endif
                        @if(isset($convert_sale))
                            <input type="hidden" name="convert_sale" value="1">
                        @endif
                            <div class="form-body">
                                <div class="row col-md-12">
                                    <div class="col-md-6">
                                        {{--<h3 class="box-title">@lang('modules.client.companyDetails')</h3>--}}
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="control-label">@lang('modules.client.companyName')</label>
                                                    <input type="text" id="company_name" name="company_name" value="{{ $leadDetail->company_name ?? '' }}" class="form-control" >
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>@lang('modules.client.firstName')</label>
                                                    <input type="text" name="first_name" id="first_name"  value="{{ $leadDetail->first_name ?? '' }}"   class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>@lang('modules.client.lastName')</label>
                                                    <input type="text" name="last_name" id="last_name"  value="{{ $leadDetail->last_name ?? '' }}"   class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <div class="form-group">
                                                    <label class="control-label">@lang('app.address') 1</label>
                                                    <input type="text" name="address1" id="address1" value="{{ $leadDetail->address1 ?? '' }}"  class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <div class="form-group">
                                                    <label class="control-label">@lang('app.address') 2</label>
                                                    <input type="text" name="address2" id="address2" value="{{ $leadDetail->address2 ?? '' }}"  class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>@lang('modules.client.city')</label>
                                                    <input type="text" name="city" id="city" class="form-control" value="{{ $leadDetail->city ?? '' }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">@lang('modules.client.state')</label>
                                                    <select class="form-control selectpicker" id="state" name="state" data-style="form-control select-with-transition">
                                                        @forelse($states as $key => $value)
                                                            <option @if(isset($leadDetail) && $leadDetail->state == $key) selected @elseif($key == 'CA') selected
                                                                    @endif value="{{ $key }}"> {{ $value }}</option>
                                                        @empty

                                                        @endforelse
                                                    </select>
                                                </div>
                                            </div>
                                            <!--/span-->

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>@lang('modules.client.zip')</label>
                                                    <input type="text" name="zip" id="zip" class="form-control" value="{{ $leadDetail->zip ?? '10001' }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 text-center">
                                            <div class="form-group">
                                                <button type="button" id="btn_google_map" class="btn btn-primary"> <i class="fa fa-globe" aria-hidden="true"></i> Google Map</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        {{--<h3 class="box-title">@lang('modules.client.clientDetails')</h3>--}}
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">@lang('modules.client.phone_number')</label>
                                                    <input type="text" id="phone_number" name="phone_number" value="{{ $leadDetail->phone ?? '' }}" class="form-control" >
                                                </div>
                                            </div>
                                            <!--/span-->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">@lang('modules.client.ext')</label>
                                                    <input type="text" id="ext" name="ext" value="{{ $leadDetail->ext ?? '' }}" class="form-control" >
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-9">
                                                <div class="form-group">
                                                    <label>@lang('modules.client.cell')</label>
                                                    <input type="tel" name="cell" id="cell" value="{{ $leadDetail->cell ?? '' }}" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>@lang('modules.client.cell') @lang('modules.client.contact')</label>
                                                    <input type="tel" name="cell_contact" id="cell_contact" value="{{ $leadDetail->cell_contact ?? '' }}" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-9">
                                                <div class="form-group">
                                                    <label>@lang('modules.client.cell') 2</label>
                                                    <input type="tel" name="cell2" id="cell2" value="{{ $leadDetail->cell2 ?? '' }}" class="form-control">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>@lang('modules.client.cell') 2 @lang('modules.client.contact')</label>
                                                    <input type="text" name="cell2_contact" id="cell2_contact" value="{{ $leadDetail->cell2_contact ?? '' }}" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <label>@lang('app.fax')</label>
                                                <div class="form-group">
                                                    <input type="fax" name="fax" id="fax" value="{{ $leadDetail->fax ?? '' }}" class="form-control">
                                                </div>
                                            </div>
                                            <!--/span-->
                                        </div>
                                        <!--/row-->
                                        <div class="row">

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>@lang('modules.client.clientEmail')</label>
                                                    <input type="email" name="email" id="email" value="{{ $leadDetail->email ?? '' }}" class="form-control">
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>@lang('modules.client.secondEmail')</label>
                                                    <input type="email" name="second_email" id="second_email" value="{{ $leadDetail->second_email ?? '' }}" class="form-control">
                                                </div>
                                            </div>

                                            <!--/span-->
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Ref #</label>
                                                    <input type="text" name="ref" id="ref" class="form-control" value="{{ $leadDetail->ref ?? '' }}">
                                                </div>
                                            </div>
                                            <!--/span-->
                                        </div>
                                    </div>
                                </div>

                                <!--/row-->
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
<script src="{{ asset('js/jquery.inputmask.min.js') }}"></script>
<script>
    $(".date-picker").datepicker({
        todayHighlight: true,
        autoclose: true
    });

    $('#save-form').click(function () {
        $.easyAjax({
            url: '{{route('admin.clients.store')}}',
            container: '#createClient',
            type: "POST",
            redirect: true,
            data: $('#createClient').serialize()
        })
    });

    $('#random_password').change(function () {
        var randPassword = $(this).is(":checked");

        if(randPassword){
            $('#password').val('{{ str_random(8) }}');
            $('#password').attr('readonly', 'readonly');
        }
        else{
            $('#password').val('');
            $('#password').removeAttr('readonly');
        }
    });

     $('#btn_google_map').click(function(){
        if(!$('#address1').val() && !$('#city').val()){
             $.toast({
                heading: 'Error',
                text: "Please input address fields.",
                position: 'top-right',
                loaderBg:'#ff6849',
                icon: 'error',
                hideAfter: 3500
            });
            return false;
        }
        else{
            let address = $('#address1').val();
            
            if($('#address2').val()){
                address += ',' + $('#address2').val();
            }
            if($('#city').val()){
                address += ',' + $('#city').val();
            }
            if($('#state').val()){
                address += ',' + $('#state').val();
            }
            if($('#zip').val()){
                address += ',' + $('#zip').val();
            }
            
            url = encodeURI(address)
            window.open('https://www.google.com/maps/search/?api=1&query=' + url, '_blank');
        }
        
    });

    $(document).ready(function(){
        $('#phone_number').inputmask("999-999-9999");
        $('#cell').inputmask("999-999-9999");
        $('#cell2').inputmask("999-999-9999");
        $('#fax').inputmask("999-999-9999");
    });
</script>
@endpush

