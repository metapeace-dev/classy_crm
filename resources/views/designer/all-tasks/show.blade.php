<link rel="stylesheet" href="{{ asset('plugins/bower_components/summernote/dist/summernote.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/dropzone-master/dist/dropzone.css') }}">
<style>
    .file-bg {
        height: 150px;
        overflow: hidden;
        position: relative;
    }
    .file-bg .overlay-file-box {
        opacity: .9;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 100%;
        text-align: center;
    }
</style>
<div class="rpanel-title"> @lang('app.task') <span><i class="ti-close right-side-toggle"></i></span> </div>
<div class="r-panel-body">

        <div class="row">
        <div class="row">
            <div class="col-xs-12">
                <a href="javascript:;" id="completedButton" class="btn btn-success btn-sm m-b-10 btn-rounded btn-outline @if($task->board_column->slug == 'completed') hidden @endif "  onclick="markComplete('completed')" ><i class="fa fa-check"></i> @lang('modules.tasks.markComplete')</a>
                <a href="javascript:;" id="inCompletedButton" class="btn btn-default btn-outline btn-sm m-b-10 btn-rounded @if($task->board_column->slug != 'completed') hidden @endif"  onclick="markComplete('incomplete')"><i class="fa fa-times"></i> @lang('modules.tasks.markIncomplete')</a>
                @if($task->board_column->slug != 'completed' && $user->can('edit_tasks'))
                    <a href="javascript:;" id="reminderButton" class="btn btn-info btn-sm m-b-10 btn-rounded btn-outline pull-right" title="@lang('messages.remindToAssignedEmployee')"><i class="fa fa-envelope"></i> @lang('modules.tasks.reminder')</a>
                @endif
            </div>
                <div class="col-xs-12">
                    <h5>{{ ucwords($task->heading) }}

                        <label class="m-l-5 font-light label
                    @if($task->priority == 'high')
                                label-danger
                            @elseif($task->priority == 'medium')
                                label-warning
                            @else
                                label-success
                            @endif
                                ">
                            <span class="text-dark">@lang('modules.tasks.priority') ></span>  {{ ucfirst($task->priority) }}
                        </label>

                    </h5>
                </div>
        </div>
            <div class="col-xs-6 col-md-3 font-12 m-t-10">
                <label class="font-12" for="">@lang('modules.tasks.assignTo')</label><br>
                @foreach($task->attendees as $attendee)
                    @if($attendee->user->image)
                        <img data-toggle="tooltip" data-original-title="{{ucwords($attendee->user->name) }}" src="{{asset_url('avatar/' . $attendee->user->image)}}"
                             alt="user" class="img-circle" width="30">
                    @else <img data-toggle="tooltip" data-original-title="{{ucwords($attendee->user->name)}}" src="{{asset('img/default-profile-2.png')}}"
                               alt="user" class="img-circle" width="30">
                    @endif
                @endforeach
            </div>
            @if($task->create_by)
            <div class="col-xs-6 col-md-3 font-12 m-t-10">
                <label class="font-12" for="">@lang('modules.tasks.assignBy')</label><br>
                @if($task->create_by->image)
                    <img src="{{ asset_url('avatar/'.$task->create_by->image) }}" class="img-circle" width="25" alt="">
                @else
                    <img src="{{ asset('img/default-profile-2.png') }}" class="img-circle" width="25" alt="">
                @endif
    
                {{ ucwords($task->create_by->name) }}
            </div>
            @endif
            
            @if($task->start_date)
            <div class="col-xs-6 col-md-3 font-12 m-t-10">
                <label class="font-12" for="">@lang('app.startDate')</label><br>
                <span class="text-success" >{{ $task->start_date->format($global->date_format) }}</span><br>
            </div>
            @endif
            <div class="col-xs-6 col-md-3 font-12 m-t-10">
                <label class="font-12" for="">@lang('app.dueDate')</label><br>
                <span @if($task->due_date->isPast()) class="text-danger" @endif>
                    {{ $task->due_date->format($global->date_format) }}
                </span>

                <span style="color: {{ $task->board_column->label_color }}" id="columnStatus"> {{ $task->board_column->column_name }}</span>

            </div>
            <div class="col-xs-12 task-description b-all p-10 m-t-20">
                {!! ucfirst($task->description) !!}
            </div>
    
        </div>

        <section id="section-line-3" class="show">
        <div class="row">
            <div class="col-md-12" id="files-list-panel">
                <div class="white-box">
                    <h2>@lang('modules.projects.files')</h2>

                    <div class="row m-b-10">
                        <div class="col-md-3">
                            <a href="javascript:;" id="show-dropzone"
                               class="btn btn-success btn-outline"><i class="ti-upload"></i> @lang('modules.projects.uploadFile')</a>
                        </div>
                        <div class="col-md-3">
                            <a href="javascript:;" id="show-link-form"
                               class="btn btn-success btn-outline"><i class="ti-link"></i> @lang('modules.projects.addFileLink')</a>
                        </div>
                    </div>

                    <div class="row m-b-20 hide" id="file-dropzone">
                        <div class="col-md-12">
                            <form action="{{ route('designer.task-files.storeFile') }}" class="dropzone"
                                  id="file-upload-dropzone">
                                {{ csrf_field() }}

                                {!! Form::hidden('task_id', $task->id) !!}

                                <input name="view" type="hidden" id="view" value="list">

                                <div class="fallback">
                                    <input name="file" type="file" multiple/>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="row m-b-20 hide" id="file-link">
                        {!! Form::open(['id'=>'file-external-link','class'=>'ajax-form','method'=>'POST']) !!}


                        {!! Form::hidden('task_id', $task->id) !!}

                        <input name="view" type="hidden" id="view" value="list">
                        <div class="col-md-6">

                            <div class="form-group">
                                <label for="">@lang('app.name')</label>
                                <input type="text" name="filename" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">

                            <div class="form-group">
                                <label for="">@lang('modules.projects.addFileLink')</label>
                                <input type="text" name="external_link" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <button class="btn btn-success" id="save-link">@lang('app.submit')</button>
                            </div>
                        </div>

                        {!! Form::close() !!}
                    </div>

                    <ul class="nav nav-tabs" role="tablist" id="list-tabs">
                        <li role="presentation" class="active nav-item" data-pk="list"><a href="#list" class="nav-link" aria-controls="home" role="tab" data-toggle="tab" aria-expanded="true"><span class="visible-xs"><i class="ti-home"></i></span><span class="hidden-xs"> @lang('app.list')</span></a></li>
                        <li role="presentation" class="nav-item" data-pk="thumbnail"><a href="#thumbnail" class="nav-link thumbnail" aria-controls="profile" role="tab" data-toggle="tab" aria-expanded="false"><span class="visible-xs"><i class="ti-user"></i></span> <span class="hidden-xs">@lang('app.thumbnail')</span></a></li>
                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="list">
                            <ul class="list-group" id="files-list">
                                @forelse($task->files as $file)
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col-md-9">
                                                {{ $file->filename }}
                                            </div>
                                            <div class="col-md-3">
                                                @if($file->external_link != '')
                                                    <a target="_blank" href="{{ $file->external_link }}"
                                                       data-toggle="tooltip" data-original-title="View"
                                                       class="btn btn-info btn-circle"><i
                                                                class="fa fa-search"></i></a>
                                                @elseif(config('filesystems.default') == 'local')
                                                    <a target="_blank" href="{{ asset_url('task-files/'.$task->id.'/'.$file->hashname) }}"
                                                       data-toggle="tooltip" data-original-title="View"
                                                       class="btn btn-info btn-circle"><i
                                                                class="fa fa-search"></i></a>

                                                @elseif(config('filesystems.default') == 's3')
                                                    <a target="_blank" href="{{ $url.'task-files/'.$task->id.'/'.$file->filename }}"
                                                       data-toggle="tooltip" data-original-title="View"
                                                       class="btn btn-info btn-circle"><i
                                                                class="fa fa-search"></i></a>
                                                @elseif(config('filesystems.default') == 'google')
                                                    <a target="_blank" href="{{ $file->google_url }}"
                                                       data-toggle="tooltip" data-original-title="View"
                                                       class="btn btn-info btn-circle"><i
                                                                class="fa fa-search"></i></a>
                                                @elseif(config('filesystems.default') == 'dropbox')
                                                    <a target="_blank" href="{{ $file->dropbox_link }}"
                                                       data-toggle="tooltip" data-original-title="View"
                                                       class="btn btn-info btn-circle"><i
                                                                class="fa fa-search"></i></a>
                                                @endif

                                                @if(is_null($file->external_link))
                                                    &nbsp;&nbsp;
                                                    <a href="{{ route('designer.task-files.download', $file->id) }}"
                                                       data-toggle="tooltip" data-original-title="Download"
                                                       class="btn btn-inverse btn-circle"><i
                                                                class="fa fa-download"></i></a>
                                                @endif
                                                &nbsp;&nbsp;
                                                <a href="javascript:;" data-toggle="tooltip"
                                                   data-original-title="Delete"
                                                   data-file-id="{{ $file->id }}"
                                                   class="btn btn-danger btn-circle sa-params delete-file" data-pk="list"><i
                                                            class="fa fa-times"></i></a>

                                                <span class="m-l-10">{{ $file->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </li>
                                @empty
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col-md-10">
                                                @lang('messages.noFileUploaded')
                                            </div>
                                        </div>
                                    </li>
                                @endforelse

                            </ul>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="thumbnail">

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
    
    </div>



<script src="{{ asset('plugins/bower_components/moment/moment.js') }}"></script>
<script src="{{ asset('plugins/bower_components/summernote/dist/summernote.min.js') }}"></script>
{{--<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>--}}
<script src="{{ asset('plugins/bower_components/dropzone-master/dist/dropzone.js') }}"></script>
<script>

    $('body').on('click', '.edit-sub-task', function () {
        var id = $(this).data('sub-task-id');
        var url = '{{ route('designer.sub-task.edit', ':id')}}';
        url = url.replace(':id', id);

        $('#subTaskModelHeading').html('Sub Task');
        $.ajaxModal('#subTaskModal', url);
    })

    $('.add-sub-task').click(function () {
        var id = $(this).data('task-id');
        var url = '{{ route('designer.sub-task.create')}}?task_id='+id;

        $('#subTaskModelHeading').html('Sub Task');
        $.ajaxModal('#subTaskModal', url);
    })

    $('#reminderButton').click(function () {
        swal({
            title: "Are you sure?",
            text: "Do you want to send reminder to assigned employee?",
            dangerMode: true,
            icon: 'warning',
            buttons: {
                cancel: "No, cancel please!",
                confirm: {
                    text: "Yes, send it!",
                    value: true,
                    visible: true,
                    className: "danger",
                }
            }
        }, function (isConfirm) {
            if (isConfirm) {

                var url = '{{ route('designer.all-tasks.reminder', $task->id)}}';

                $.easyAjax({
                    type: 'GET',
                    url: url,
                    success: function (response) {
                        //
                    }
                });
            }
        });
    })

    function saveSubTask() {
        $.easyAjax({
            url: '{{route('designer.sub-task.store')}}',
            container: '#createSubTask',
            type: "POST",
            data: $('#createSubTask').serialize(),
            success: function (response) {
                $('#subTaskModal').modal('hide');
                $('#sub-task-list').html(response.view)
            }
        })
    }

    function updateSubTask(id) {
        var url = '{{ route('designer.sub-task.update', ':id')}}';
        url = url.replace(':id', id);
        $.easyAjax({
            url: url,
            container: '#createSubTask',
            type: "POST",
            data: $('#createSubTask').serialize(),
            success: function (response) {
                $('#subTaskModal').modal('hide');
                $('#sub-task-list').html(response.view)
            }
        })
    }
    $('.summernote').summernote({
        height: 100,                 // set editor height
        minHeight: null,             // set minimum height of editor
        maxHeight: null,             // set maximum height of editor
        focus: false,                 // set focus to editable area after initializing summernote,
        toolbar: [
            // [groupName, [list of button]]
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']]
        ]
    });


    $('body').on('click', '.delete-sub-task', function () {
        var id = $(this).data('sub-task-id');
        swal({
            title: "Are you sure?",
            text: "You will not be able to recover the deleted sub task!",
            dangerMode: true,
            icon: 'warning',
            buttons: {
                cancel: "No, cancel please!",
                confirm: {
                    text: "Yes, delete it!",
                    value: true,
                    visible: true,
                    className: "danger",
                }
            }
        }, function (isConfirm) {
            if (isConfirm) {

                var url = "{{ route('designer.sub-task.destroy',':id') }}";
                url = url.replace(':id', id);

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {'_token': token, '_method': 'DELETE'},
                    success: function (response) {
                        if (response.status == "success") {
                            $('#sub-task-list').html(response.view);
                        }
                    }
                });
            }
        });
    });

    //    change sub task status
    $('#sub-task-list').on('click', '.task-check', function () {
        if ($(this).is(':checked')) {
            var status = 'complete';
        }else{
            var status = 'incomplete';
        }

        var id = $(this).data('sub-task-id');
        var url = "{{route('designer.sub-task.changeStatus')}}";
        var token = "{{ csrf_token() }}";

        $.easyAjax({
            url: url,
            type: "POST",
            data: {'_token': token, subTaskId: id, status: status},
            success: function (response) {
                if (response.status == "success") {
                    $('#sub-task-list').html(response.view);
                }
            }
        })
    });

    $('#submit-comment').click(function () {
        var comment = $('#task-comment').val();
        var token = '{{ csrf_token() }}';
        $.easyAjax({
            url: '{{ route("designer.task-comment.store") }}',
            type: "POST",
            data: {'_token': token, comment: comment, taskId: '{{ $task->id }}'},
            success: function (response) {
                if (response.status == "success") {
                    $('#comment-list').html(response.view);
                    $('.note-editable').html('');
                    $('#task-comment').val('');
                }
            }
        })
    })

    $('body').on('click', '.delete-task-comment', function () {
        var commentId = $(this).data('comment-id');
        var token = '{{ csrf_token() }}';

        var url = '{{ route("designer.task-comment.destroy", ':id') }}';
        url = url.replace(':id', commentId);

        $.easyAjax({
            url: url,
            type: "POST",
            data: {'_token': token, '_method': 'DELETE', commentId: commentId},
            success: function (response) {
                if (response.status == "success") {
                    $('#comment-list').html(response.view);
                }
            }
        })
    })

    //    change task status
    function markComplete(status) {

        var id = {{ $task->id }};

        if(status == 'completed'){
            var checkUrl = '{{ route("designer.tasks.checkTask", ":id")}}';
            checkUrl = checkUrl.replace(':id', id);
            $.easyAjax({
                url: checkUrl,
                type: "GET",
                container: '#task-list-panel',
                data: {},
                success: function (data) {
                    if(data.taskCount > 0){
                        swal({
                            title: "Are you sure?",
                            text: "There is a incomplete sub-task in this task do you want to mark complete!",
                            dangerMode: true,
                            icon: 'warning',
                            buttons: {
                                cancel: "No, cancel please!",
                                confirm: {
                                    text: "Yes, complete it!",
                                    value: true,
                                    visible: true,
                                    className: "danger",
                                }
                            }
                        }).then(function (isConfirm) {
                            if (isConfirm) {
                                updateTask(id,status)
                            }
                        });
                    }
                    else{
                        updateTask(id,status)
                    }

                }
            });
        }
        else{
            updateTask(id,status)
        }


    }

    // Update Task
    function updateTask(id,status){
        var url = "{{route('designer.tasks.changeStatus')}}";
        var token = "{{ csrf_token() }}";
        $.easyAjax({
            url: url,
            type: "POST",
            container: '.r-panel-body',
            data: {'_token': token, taskId: id, status: status, sortBy: 'id'},
            success: function (data) {
                $('#columnStatus').css('color', data.textColor);
                $('#columnStatus').html(data.column);
                if(status == 'completed'){

                    $('#inCompletedButton').removeClass('hidden');
                    $('#completedButton').addClass('hidden');
                    if($('#reminderButton').length){
                        $('#reminderButton').addClass('hidden');
                    }
                }
                else{
                    $('#completedButton').removeClass('hidden');
                    $('#inCompletedButton').addClass('hidden');
                    if($('#reminderButton').length){
                        $('#reminderButton').removeClass('hidden');
                    }
                }

                if( typeof table !== 'undefined'){
                    table._fnDraw();
                }
                else{
                    loadData();
                }
            }
        })
    }

    $('#show-dropzone').click(function () {
        $('#file-dropzone').toggleClass('hide show');
    });

    $('#show-link-form').click(function () {
        $('#file-link').toggleClass('hide show');
    });

    // new Dropzone("div#file-upload-dropzone", { paramName: "/file/post"});
    $('body').find("#file-upload-dropzone").dropzone(
        {
            paramName: "file",
            dictDefaultMessage: "@lang('modules.projects.dropFile')",
            accept: function (file, done) {
                done();
            },
            init: function () {
                this.on("success", function (file, response) {
                    var viewName = $('#view').val();
                    if(viewName == 'list') {
                        $('#files-list-panel ul.list-group').html(response.html);
                    } else {
                        $('#thumbnail').empty();
                        $(response.html).hide().appendTo("#thumbnail").fadeIn(500);
                    }
                })
            }
        });

    $('body').on('click', '.delete-file', function () {
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
                var url = "{{ route('designer.task-files.deleteFile') }}";
                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {'_token': token, 'view': deleteView, 'id' : id},
                    success: function (response) {
                        if (response.status == "success") {
                            $.unblockUI();
                            if(deleteView == 'list') {
                                $('#files-list-panel ul.list-group').html(response.html);
                            } else {
                                $('#thumbnail').empty();
                                $(response.html).hide().appendTo("#thumbnail").fadeIn(500);
                            }
                        }
                    }
                });
            }
        });
    });

    $('.thumbnail').on('click', function(event) {
        event.preventDefault();
        $('#thumbnail').empty();
        var taskID = "{{ $task->id }}";
        $.easyAjax({
            type: 'GET',
            url: "{{ route('designer.task-files.thumbnail') }}",
            data: {
                id: taskID
            },
            success: function (response) {
                $(response.view).hide().appendTo("#thumbnail").fadeIn(500);
            }
        });
    });

    $('#save-link').click(function () {
        $.easyAjax({
            url: '{{route('designer.task-files.storeLink')}}',
            container: '#file-external-link',
            type: "POST",
            redirect: true,
            data: $('#file-external-link').serialize(),
            success: function () {
                window.location.reload();
            }
        })
    });


    $('#list-tabs').on("shown.bs.tab",function(event){
        var tabSwitch = $('#list').hasClass('active');
        if(tabSwitch == true) {
            $('#view').val('list');
        } else {
            $('#view').val('thumbnail');
        }
    });

</script>