@extends('layouts.installer-app')

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
                <li><a href="{{ route('installer.projects.index') }}">{{ $pageTitle }}</a></li>
                <li class="active">@lang('app.addNew')</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@push('head-script')
    <link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/summernote/dist/summernote.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/custom-select/custom-select.css') }}">
    <style>
        .panel-black .panel-heading a, .panel-inverse .panel-heading a {
            color: unset!important;
        }
        .bootstrap-select.btn-group .dropdown-menu li a span.text {
            color: #000;
        }
        .panel-black .panel-heading a:hover, .panel-inverse .panel-heading a:hover {
            color: #000 !important;
        }
        .panel-black .panel-heading a, .panel-inverse .panel-heading a {
            color: #000 !important;
        }
        .btn-info.active, .btn-info:active, .open>.dropdown-toggle.btn-info {
            background-color:unset !important; ;
            border-color: #269abc;
        }
    </style>
@endpush

@section('content')

    <div class="row">
        <div class="col-md-12">

            <div class="panel panel-inverse">
                <div class="panel-heading"> @lang('modules.projects.createTitle')

                </div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        {!! Form::open(['id'=>'createProject','class'=>'ajax-form','method'=>'POST']) !!}
                        @if(isset($leadDetail->id))
                            <input type="hidden" name="leadDetail" value="{{ $leadDetail->id }}">
                        @endif
                        @if(isset($clientDetail->id))
                            <input type="hidden" name="clientDetal" value="{{ $clientDetail->id }}">
                        @endif
                        <div class="form-body">
                            <h3 class="box-title m-b-30">@lang('modules.projects.projectInfo')</h3>
                            <div class="row">
                                <div class="col-md-7">
                                    <div class="form-group">
                                        <label>@lang('modules.projects.projectName')</label>
                                        <input type="text" name="project_name" id="project_name" class="form-control">
                                        <input type="hidden" name="template_id" id="template_id">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">@lang('app.address') 1</label>
                                        @if(isset($leadDetail->project_location))
                                            <input type="text" name="address1"  id="address1" class="form-control" value="{{ $leadDetail->project_location->address1 ?? '' }}">
                                        @elseif(isset($clientDetail))
                                            <input type="text" name="address1"  id="address1" class="form-control" value="{{ $clientDetail->address1 ?? '' }}">
                                        @else
                                            <input type="text" name="address1"  id="address1" class="form-control">
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">@lang('app.address') 2</label>
                                        @if(isset($leadDetail->project_location))
                                            <input type="text" name="address2"  id="address2" class="form-control" value="{{ $leadDetail->project_location->address2 ?? '' }}">
                                        @elseif(isset($clientDetail))
                                            <input type="text" name="address2"  id="address2" class="form-control" value="{{ $clientDetail->address2 ?? '' }}">
                                        @else
                                            <input type="text" name="address2"  id="address2" class="form-control">
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">@lang('modules.projects.city')</label>
                                        @if(isset($leadDetail->project_location))
                                            <input type="text" name="city"  id="city" class="form-control" value="{{ $leadDetail->project_location->city ?? '' }}">
                                        @elseif(isset($clientDetail))
                                            <input type="text" name="city"  id="city" class="form-control" value="{{ $clientDetail->city ?? '' }}">
                                        @else
                                            <input type="text" name="city"  id="city" class="form-control">
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <div class="col-md-6 no-padding-left">
                                            <div class="form-group">
                                                <label class="control-label">@lang('modules.projects.state')</label>
                                                <select class="form-control" id="state" name="state">
                                                    @foreach($states as $key => $value)
                                                        <option @if(isset($leadDetail->project_location) && $leadDetail->project_location->state == $key || isset($clientDetail) && $clientDetail->state == $key) selected @elseif($key == 'CA') selected @endif value="{{ $key }}">{{ucfirst($value)}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 no-padding">
                                            <div class="form-group">
                                                <label class="control-label">@lang('modules.projects.zip')</label>
                                                @if(isset($leadDetail->project_location))
                                                    <input type="text" name="zip"  id="zip" class="form-control" value="{{ $leadDetail->project_location->zip ?? '' }}">
                                                @elseif(isset($clientDetail))
                                                    <input type="text" name="zip"  id="zip" class="form-control" value="{{ $clientDetail->zip ?? '' }}">
                                                @else
                                                    <input type="text" name="zip"  id="zip" class="form-control" value="10001">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <div class="col-md-8 no-padding-left">
                                            <div class="form-group">
                                                <label>@lang('modules.projects.phone')</label>
                                                @if(isset($leadDetail))
                                                    <input type="tel" name="phone"  id="phone" class="form-control" value="{{ $leadDetail->phone ?? '' }}">
                                                @elseif(isset($clientDetail))
                                                    <input type="tel" name="phone"  id="phone" class="form-control" value="{{ $clientDetail->phone_number ?? '' }}">
                                                @else
                                                    <input type="tel" name="phone"  id="phone" class="form-control">
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4 no-padding">
                                            <div class="form-group">
                                                <label>@lang('modules.projects.ext')</label>
                                                @if(isset($leadDetail))
                                                    <input type="text" name="ext"  id="ext" class="form-control" value="{{ $leadDetail->ext ?? '' }}">
                                                @elseif(isset($clientDetail))
                                                    <input type="text" name="ext"  id="ext" class="form-control" value="{{ $clientDetail->ext ?? '' }}">
                                                @else
                                                    <input type="text" name="ext"  id="ext" class="form-control">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>@lang('modules.projects.cell')</label>
                                        @if(isset($leadDetail))
                                            <input type="tel" name="cell"  id="cell" class="form-control" value="{{ $leadDetail->cell ?? '' }}">
                                        @elseif(isset($clientDetail))
                                            <input type="tel" name="cell"  id="cell" class="form-control" value="{{ $clientDetail->cell ?? '' }}">
                                        @else
                                            <input type="tel" name="cell"  id="cell" class="form-control">
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label>@lang('modules.projects.cell') 2</label>
                                        @if(isset($leadDetail))
                                            <input type="tel" name="cell2"  id="cell2" class="form-control" value="{{ $leadDetail->cell2 ?? '' }}">
                                        @elseif(isset($clientDetail))
                                            <input type="tel" name="cell2"  id="cell2" class="form-control" value="{{ $clientDetail->cell2 ?? '' }}">
                                        @else
                                            <input type="tel" name="cell2"  id="cell2" class="form-control">
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label>@lang('modules.projects.fax')</label>
                                        @if(isset($leadDetail))
                                            <input type="tel" name="fax"  id="fax" class="form-control" value="{{ $leadDetail->fax ?? '' }}">
                                        @elseif(isset($clientDetail))
                                            <input type="tel" name="fax"  id="fax" class="form-control" value="{{ $clientDetail->fax ?? '' }}">
                                        @else
                                            <input type="tel" name="fax"  id="fax" class="form-control">
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label>@lang('modules.projects.email')</label>
                                        @if(isset($leadDetail))
                                            <input type="email" name="email"  id="email" class="form-control" value="{{ $leadDetail->email ?? '' }}">
                                        @elseif(isset($clientDetail))
                                            <input type="email" name="email"  id="email" class="form-control" value="{{ $clientDetail->email ?? '' }}">
                                        @else
                                            <input type="email" name="email"  id="email" class="form-control">
                                        @endif
                                    </div>

                                    <div class="form-group">
                                        <label>@lang('modules.projects.secondEmail')</label>
                                        @if(isset($leadDetail))
                                            <input type="email" name="second_email"  id="second_email" class="form-control" value="{{ $leadDetail->second_email ?? '' }}">
                                        @elseif(isset($clientDetail))
                                            <input type="email" name="second_email"  id="second_email" class="form-control" value="{{ $clientDetail->second_email ?? '' }}">
                                        @else
                                            <input type="email" name="second_email"  id="second_email" class="form-control">
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">@lang('modules.projects.installStartDate')</label>
                                        <input type="text" name="install_start_date" id="install_start_date" class="form-control" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">@lang('modules.projects.installEndDate')</label>
                                        <input type="text" name="install_end_date"  id="install_end_date" class="form-control" autocomplete="off">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 clear">
                                <div class="form-group">
                                    <label class="control-label">@lang('modules.projects.contact')</label>
                                    <input type="text" name="contact"  id="contact" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6 clear">
                                <div class="form-group">
                                    <label class="control-label">@lang('app.project') @lang('app.status')</label>
                                    <select name="status" id="" class="form-control">
                                        <option
                                                value="not started">@lang('app.notStarted')
                                        </option>
                                        <option
                                                value="in progress">@lang('app.inProgress')
                                        </option>
                                        <option
                                                value="on hold">@lang('app.onHold')
                                        </option>
                                        <option
                                                value="canceled">@lang('app.canceled')
                                        </option>
                                        <option
                                                value="completed">@lang('app.completed')
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <h3 class="box-title m-b-10">@lang('modules.projects.salesInfo')</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">@lang('modules.projects.salesPrice')</label>
                                    <div class="col-md-12 input-group">
                                        <div class="input-group-addon">
                                            <i class="fa fa-usd" aria-hidden="true"></i>
                                        </div>
                                        <input type="number" min="0.01" step="0.01" class="form-control" name="sales_price"/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">@lang('modules.projects.soldDate')</label>
                                    <input type="text" name="sold_date" id="sold_date" class="form-control" autocomplete="off">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Discount</label>
                                    <div class="col-md-12 input-group">
                                        <input type="number" min="0.01" step="0.01" class="form-control" name="discount"/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Discount Type</label>
                                    <select name="discount_type" id="discount_type" class="form-control">
                                        <option value="dollar">Dollar ($)</option>
                                        <option value="percentage">Percentage (%)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <h3 class="box-title m-b-10">@lang('modules.projects.clientInfo')</h3>
                        <div class="row">
                            <div class="col-xs-12 ">
                                <div class="form-group">
                                    <label class="control-label">@lang('modules.projects.selectClient')</label>
                                    <select class="select2 form-control" name="client_id" id="client_id" data-placeholder="@lang('modules.projects.selectClient')"
                                            data-style="form-control">
                                        <option value=""></option>
                                        @foreach($clients as $client)
                                            <option
                                                    @if(isset($leadDetail->client_id) && $leadDetail->client_id == $client->id || isset($clientDetail) && $clientDetail->id == $client->id) selected @endif value="{{ $client->id }}">{{ ucwords($client->first_name). ' ' . ucwords($client->last_name) }}
                                                @if($client->company_name != '') {{ '('.$client->company_name.')' }} @endif</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" id="save-form" class="btn btn-success"><i class="fa fa-check"></i>
                                @lang('app.save')
                            </button>
                            <button type="reset" class="btn btn-default">@lang('app.reset')</button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>    <!-- .row -->

    {{--Ajax Modal--}}
    <div class="modal fade bs-modal-md in" id="projectCategoryModal" role="dialog" aria-labelledby="myModalLabel"
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
    <script src="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('plugins/bower_components/summernote/dist/summernote.min.js') }}"></script>
    <script src="{{ asset('plugins/bower_components/custom-select/custom-select.min.js') }}"></script>
    <script src="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('js/jquery.inputmask.min.js') }}"></script>
    <script>
        $(".select2").select2({
            formatNoMatches: function () {
                return "{{ __('messages.noRecordFound') }}";
            }
        });
        $('#clientNotification').hide();
        $("#start_date").datepicker({
            format: '{{ $global->date_picker_format }}',
            todayHighlight: true,
            autoclose: true,
        }).on('changeDate', function (selected) {
            $('#deadline').datepicker({
                format: '{{ $global->date_picker_format }}',
                autoclose: true,
                todayHighlight: true
            });
            var minDate = new Date(selected.date.valueOf());
            $('#deadline').datepicker('setStartDate', minDate);
        });

        $("#sold_date").datepicker({
            format: '{{ $global->date_picker_format }}',
            todayHighlight: true,
            autoclose: true,
        });

        $("#install_start_date").datepicker({
            format: '{{ $global->date_picker_format }}',
            todayHighlight: true,
            autoclose: true,
        }).on('changeDate', function (selected) {
            $('#install_end_date').datepicker({
                format: '{{ $global->date_picker_format }}',
                autoclose: true,
                todayHighlight: true
            });
            var minDate = new Date(selected.date.valueOf());
            $('#install_end_date').datepicker("update", minDate);
            $('#install_end_date').datepicker('setStartDate', minDate);
        });

        $("#install_end_date").datepicker({
            format: '{{ $global->date_picker_format }}',
            autoclose: true
        }).on('changeDate', function (selected) {
            var maxDate = new Date(selected.date.valueOf());
            $('#install_start_date').datepicker('setEndDate', maxDate);
        });

        // check client view task checked
        function checkTask()
        {
            var chVal = $('#client_view_task').is(":checked") ? true : false;
            if(chVal == true){
                $('#clientNotification').show();
            }
            else{
                $('#clientNotification').hide();
            }
        }

        $('#without_deadline').click(function () {
            var check = $('#without_deadline').is(":checked") ? true : false;
            if(check == true){
                $('#deadlineBox').hide();
            }
            else{
                $('#deadlineBox').show();
            }
        });

        $("#deadline").datepicker({
            format: '{{ $global->date_picker_format }}',
            autoclose: true
        }).on('changeDate', function (selected) {
            var maxDate = new Date(selected.date.valueOf());
            $('#start_date').datepicker('setEndDate', maxDate);
        });

        $('#save-form').click(function () {
            $.easyAjax({
                url: '{{route('installer.projects.store')}}',
                container: '#createProject',
                type: "POST",
                redirect: true,
                data: $('#createProject').serialize()
            })
        });

        $('.summernote').summernote({
            height: 200,                 // set editor height
            minHeight: null,             // set minimum height of editor
            maxHeight: null,             // set maximum height of editor
            focus: false                 // set focus to editable area after initializing summernote
        });

        $(':reset').on('click', function(evt) {
            evt.preventDefault()
            $form = $(evt.target).closest('form')
            $form[0].reset()
            $form.find('.selectpicker').selectpicker('render');
            $(".select2").select2("val", "");
        });

        $('#btn_google_map').click(function(){
            if(!$('#address').val() && !$('#city').val()){
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
                let address = $('#address').val();

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
            $('#phone').inputmask("999-999-9999");
            $('#cell').inputmask("999-999-9999");
            $('#cell2').inputmask("999-999-9999");
            $('#fax').inputmask("999-999-9999");
        });
    </script>

    <script>
        {{--$('#createProject').on('click', '#addProjectCategory', function () {--}}
        {{--    var url = '{{ route('installer.projectCategory.create-cat')}}';--}}
        {{--    $('#modelHeading').html('Manage Project Category');--}}
        {{--    $.ajaxModal('#projectCategoryModal', url);--}}
        {{--})--}}
    </script>
@endpush

