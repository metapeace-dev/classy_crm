@extends('layouts.designer-app')

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
                <li><a href="{{ route('designer.dashboard') }}">@lang('app.menu.home')</a></li>
                <li><a href="{{ route('designer.all-tasks.index') }}">{{ $pageTitle }}</a></li>
                <li class="active">@lang('app.addNew')</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@push('head-script')
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/custom-select/custom-select.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/summernote/dist/summernote.css') }}">
@endpush

@section('content')

    <div class="row">
        <div class="col-md-8">

            <div class="panel panel-inverse">
                <div class="panel-heading"> @lang('modules.tasks.newTask')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        {!! Form::open(['id'=>'storeTask','class'=>'ajax-form','method'=>'POST']) !!}

                        <div class="form-body">
                            <div class="row">

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">@lang('app.title')</label>
                                        <input type="text" id="heading" name="heading" class="form-control" >
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">@lang('app.description')</label>
                                        <textarea id="description" name="description" class="form-control summernote"></textarea>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">@lang('app.startDate')</label>
                                        <input type="text" name="start_date" id="start_date2" class="form-control" autocomplete="off">
                                    </div>
                                </div>
                                <!--/span-->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">@lang('app.dueDate')</label>
                                        <input type="text" name="due_date" id="due_date2" autocomplete="off" class="form-control">
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">@lang('modules.tasks.assignTo')</label>
                                        <select class="select2 m-b-10 select2-multiple " multiple="multiple"
                                                data-placeholder="@lang('modules.tasks.chooseAssignee')" name="user_id[]">
                                            @foreach($employees as $emp)
                                                <option value="{{ $emp->id }}">{{ ucwords($emp->name) }} @if($emp->id == $user->id)
                                                        (YOU) @endif</option>
                                            @endforeach
                                        </select>

                                    </div>
                                </div>

                                @if($user->hasRole('admin'))
                                    <input type="hidden" value="{{ $user->id }}" name="user_id">
                                @endif


                                <div class="col-md-12">
                                    <div class="form-group">

                                        <div class="checkbox checkbox-info">
                                            <input id="repeat-task" name="repeat" value="yes"
                                                   type="checkbox">
                                            <label for="repeat-task">@lang('modules.events.repeat')</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row" id="repeat-fields" style="display: none">
                                    <div class="col-xs-12 col-md-12">
                                        <div class="col-xs-6 col-md-3 ">
                                            <div class="form-group">
                                                <label>@lang('modules.events.repeatEvery')</label>
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
                                                <label>@lang('modules.events.cycles') <a class="mytooltip" href="javascript:void(0)"> <i class="fa fa-info-circle"></i><span class="tooltip-content5"><span class="tooltip-text3"><span class="tooltip-inner2">@lang('modules.tasks.cyclesToolTip')</span></span></span></a></label>
                                                <input type="number" name="repeat_cycles" id="repeat_cycles" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label">@lang('modules.tasks.priority')</label>

                                        <div class="radio radio-danger">
                                            <input type="radio" name="priority" id="radio13"
                                                   value="high">
                                            <label for="radio13" class="text-danger">
                                                @lang('modules.tasks.high') </label>
                                        </div>
                                        <div class="radio radio-warning">
                                            <input type="radio" name="priority"
                                                   id="radio14" checked value="medium">
                                            <label for="radio14" class="text-warning">
                                                @lang('modules.tasks.medium') </label>
                                        </div>
                                        <div class="radio radio-success">
                                            <input type="radio" name="priority" id="radio15"
                                                   value="low">
                                            <label for="radio15" class="text-success">
                                                @lang('modules.tasks.low') </label>
                                        </div>
                                    </div>
                                </div>
                                <!--/span-->

                            </div>
                            <!--/row-->

                        </div>
                        <div class="form-actions">
                            <button type="button" id="store-task" class="btn btn-success"><i class="fa fa-check"></i> @lang('app.save')</button>
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
<script src="{{ asset('plugins/bower_components/summernote/dist/summernote.min.js') }}"></script>

<script>
    //    update task
    $('#store-task').click(function () {
        $.easyAjax({
            url: '{{route('designer.all-tasks.store')}}',
            container: '#storeTask',
            type: "POST",
            data: $('#storeTask').serialize()
        })
    });

    jQuery('#start_date2').datepicker({
        format: '{{ $global->date_picker_format }}',
        autoclose: true,
        todayHighlight: true
    }).on('changeDate', function (selected) {
        $('#due_date2').datepicker({
            format: '{{ $global->date_picker_format }}',
            autoclose: true,
            todayHighlight: true
        });
        var minDate = new Date(selected.date.valueOf());
        $('#due_date2').datepicker("update", minDate);
        $('#due_date2').datepicker('setStartDate', minDate);        
    });

    $(".select2").select2({
        formatNoMatches: function () {
            return "{{ __('messages.noRecordFound') }}";
        }
    });

    $('#project_id').change(function () {
        var id = $(this).val();
        var url = '{{route('designer.all-tasks.designers', ':id')}}';
        url = url.replace(':id', id);

        $.easyAjax({
            url: url,
            type: "GET",
            redirect: true,
            success: function (data) {
                $('#user_id').html(data.html);
            }
        })
    });

    $('.summernote').summernote({
        height: 200,                 // set editor height
        minHeight: null,             // set minimum height of editor
        maxHeight: null,             // set maximum height of editor
        focus: false                 // set focus to editable area after initializing summernote
    });

    $('#repeat-task').change(function () {
        if($(this).is(':checked')){
            $('#repeat-fields').show();
        }
        else{
            $('#repeat-fields').hide();
        }
    })

</script>
@endpush

