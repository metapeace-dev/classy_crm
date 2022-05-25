@extends('layouts.installer-app')

@section('page-title')
    <div class="row bg-title">
        <!-- .page title -->
        <div class="col-lg-6 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title"><i class="{{ $pageIcon }}"></i> {{ $pageTitle }} #{{ $client->id }} - <span
                        class="font-bold">{{ ucwords($client->first_name).' '.ucwords($client->last_name) }}</span></h4>
        </div>
        <!-- /.page title -->
        <!-- .breadcrumb -->
        <div class="col-lg-6 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="{{ route('installer.dashboard') }}">@lang('app.menu.home')</a></li>
                <li class="active">@lang('modules.client.view')</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@push('head-script')

<link rel="stylesheet" href="{{ asset('plugins/bower_components/dropzone-master/dist/dropzone.css') }}">
@endpush

@section('content')

    <div class="row">
        <div class="col-md-12">

            <section>
                <div class="sttabs tabs-style-line">
                    <div class="content-wrap">
                        <section id="section-line-3" class="show">
                            <div class="row">
                                <div class="col-md-12" id="files-list-panel">
                                    <div class="white-box">
                                        <h2>@lang('modules.client.clientDetails')</h2>

                                        <div class="white-box">
                                            <div class="col-xs-7">
                                                <div class="row">
                                                    <div class="col-xs-12 b-r"> <strong>@lang('modules.client.companyName')</strong> <br>
                                                        <p class="text-muted">{{ ucwords($client->company_name) }}</p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-xs-6"> <strong>@lang('modules.client.firstName')</strong> <br>
                                                        <p class="text-muted">{{ $client->first_name ?? 'NA'}}</p>
                                                    </div>
                                                    <div class="col-xs-6 b-r"> <strong>@lang('modules.client.lastName')</strong> <br>
                                                        <p class="text-muted">{{ $client->last_name ?? 'NA'}}</p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-xs-12 b-r"> <strong>@lang('modules.client.address')</strong> <br>
                                                        <p class="text-muted">{{ $client->address }}</p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-xs-12 b-r"> <strong>@lang('modules.client.city')</strong> <br>
                                                        <p class="text-muted">{{ ucwords($client->city) }}</p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-xs-6"> <strong>@lang('modules.client.state')</strong> <br>
                                                        <p class="text-muted">{{ $client->state ? ucfirst($states[$client->state]) : 'NA'}}</p>
                                                    </div>
                                                    <div class="col-xs-6 b-r"> <strong>@lang('modules.client.zip')</strong> <br>
                                                        <p class="text-muted">{{ $client->zip ?? 'NA'}}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-5">
                                                <div class="row">
                                                    <div class="col-xs-8"> <strong>@lang('modules.client.mobile')</strong> <br>
                                                        <p class="text-muted">{{ $client->phone_number ?? 'NA'}}</p>
                                                    </div>
                                                    <div class="col-xs-4"> <strong>@lang('modules.client.ext')</strong> <br>
                                                        <p class="text-muted">{{ $client->ext ?? 'NA'}}</p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-xs-12"> <strong>@lang('modules.client.cell')</strong> <br>
                                                        <p class="text-muted">{{ $client->cell ?? 'NA'}}</p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-xs-12"> <strong>@lang('modules.client.cell') 2</strong> <br>
                                                        <p class="text-muted">{{ $client->cell2 ?? 'NA'}}</p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-xs-12"> <strong>@lang('modules.lead.fax')</strong> <br>
                                                        <p class="text-muted">{{ $client->fax ?? 'NA'}}</p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-xs-12"> <strong>@lang('modules.client.clientEmail')</strong> <br>
                                                        <p class="text-muted">{{ $client->email ?? 'NA'}}</p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-xs-12"> <strong>@lang('modules.client.secondEmail')</strong> <br>
                                                        <p class="text-muted">{{ $client->second_email ?? 'NA'}}</p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-xs-12"> <strong>@lang('modules.lead.ref')</strong> <br>
                                                        <p class="text-muted">{{ $client->ref ?? 'NA'}}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>

                                            <div class="row">
                                                <div class="col-xs-12">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </section>

                    </div><!-- /content -->
                </div><!-- /tabs -->
            </section>
        </div>


    </div>
    <!-- .row -->

@endsection

@push('footer-script')
<script src="{{ asset('plugins/bower_components/dropzone-master/dist/dropzone.js') }}"></script>
<script>
    $('#show-dropzone').click(function () {
        $('#file-dropzone').toggleClass('hide show');
    });

    $("body").tooltip({
        selector: '[data-toggle="tooltip"]'
    });

    // "myAwesomeDropzone" is the camelized version of the HTML element's ID
    Dropzone.options.fileUploadDropzone = {
        paramName: "file", // The name that will be used to transfer the file
//        maxFilesize: 2, // MB,
        dictDefaultMessage: "@lang('modules.projects.dropFile')",
        accept: function (file, done) {
            done();
        },
        init: function () {
            this.on("success", function (file, response) {
                console.log(response);
                $('#files-list-panel ul.list-group').html(response.html);
            })
        }
    };

    $('body').on('click', '.sa-params', function () {
        var id = $(this).data('file-id');
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

                var url = "{{ route('installer.files.destroy',':id') }}";
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
                            $('#files-list-panel ul.list-group').html(response.html);

                        }
                    }
                });
            }
        });
    });

</script>
@endpush