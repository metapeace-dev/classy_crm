@extends('layouts.app')

@section('page-title')
    <div class="row bg-title">
        <!-- .page title -->
        <div class="col-lg-6 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title"><i class="{{ $pageIcon }}"></i> {{ $pageTitle }} #{{ $lead->id }} - <span
                        class="font-bold">{{ ucwords($lead->first_name).' '.ucwords($lead->last_name) }}</span></h4>
        </div>
        <!-- /.page title -->
        <!-- .breadcrumb -->
        <div class="col-lg-6 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="{{ route('admin.dashboard') }}">@lang('app.menu.home')</a></li>
                <li><a href="{{ route('admin.leads.index') }}">{{ $pageTitle }}</a></li>
                <li class="active">@lang('modules.lead.view')</li>
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
                                        <h2>@lang('modules.lead.leadDetail') - #{{$lead->id}}</h2>

                                        <div class="white-box">
                                            <div class="col-xs-7">
                                                <div class="row">
                                                    <div class="col-xs-12 b-r"> <strong>@lang('modules.lead.companyName')</strong> <br>
                                                        <p class="text-muted">{{ ucwords($lead->company_name) }}</p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-xs-6"> <strong>@lang('modules.lead.first_name')</strong> <br>
                                                        <p class="text-muted">{{ $lead->first_name ?? 'NA'}}</p>
                                                    </div>
                                                    <div class="col-xs-6 b-r"> <strong>@lang('modules.lead.last_name')</strong> <br>
                                                        <p class="text-muted">{{ $lead->last_name ?? 'NA'}}</p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-xs-12 b-r"> <strong>@lang('modules.lead.address') 1</strong> <br>
                                                        <p class="text-muted">{{ $lead->address1 }}</p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-xs-12 b-r"> <strong>@lang('modules.lead.address') 2</strong> <br>
                                                        <p class="text-muted">{{ $lead->address2 }}</p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-xs-12 b-r"> <strong>@lang('modules.lead.city')</strong> <br>
                                                        <p class="text-muted">{{ ucwords($lead->city) }}</p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-xs-6"> <strong>@lang('modules.lead.state')</strong> <br>
                                                        <p class="text-muted">{{ $lead->state ? ucfirst($states[$lead->state]) : 'NA'}}</p>
                                                    </div>
                                                    <div class="col-xs-6 b-r"> <strong>@lang('modules.lead.zip')</strong> <br>
                                                        <p class="text-muted">{{ $lead->zip ?? 'NA'}}</p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-xs-6"> <strong>@lang('modules.lead.status')</strong> <br>
                                                        <p class="text-muted">{{ (!empty($lead->status_id)) ? $lead->lead_status->type : 'NA' }}</p>
                                                    </div>
                                                    <div class="col-xs-6"> <strong>@lang('app.source')</strong> <br>
                                                        <p class="text-muted">{{ (!empty($lead->source_id)) ? $lead->lead_source->name : 'NA'}}</p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-xs-7"> <strong>@lang('modules.interest_area.area_of_interest')</strong> <br>
                                                        <?php
                                                            $selected_areas = []; $str_areas = '';
                                                            if(!empty($lead->interest_areas)){
                                                                $selected_areas = explode(',', $lead->interest_areas);
                                                                foreach ($selected_areas as $area_id){
                                                                    foreach ($areas as $area){
                                                                        if($area_id == $area['id']){
                                                                            $str_areas .= $area['type'].', ';
                                                                            break;
                                                                        }
                                                                    }

                                                                }
                                                                $str_areas = substr($str_areas, 0, -2);
                                                            }
                                                        ?>

                                                        <p class="text-muted">{{ $str_areas ?? 'NA'}}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xs-5">
                                                <div class="row">
                                                    <div class="col-xs-8"> <strong>@lang('modules.lead.phone')</strong> <br>
                                                        <p class="text-muted">{{ $lead->phone ?? 'NA'}}</p>
                                                    </div>
                                                    <div class="col-xs-4"> <strong>@lang('modules.lead.ext')</strong> <br>
                                                        <p class="text-muted">{{ $lead->ext ?? 'NA'}}</p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-xs-12"> <strong>@lang('modules.lead.cell')</strong> <br>
                                                        <p class="text-muted">{{ $lead->cell ?? 'NA'}}</p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-xs-12"> <strong>@lang('modules.lead.cell') 2</strong> <br>
                                                        <p class="text-muted">{{ $lead->cell2 ?? 'NA'}}</p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-xs-12"> <strong>@lang('modules.lead.fax')</strong> <br>
                                                        <p class="text-muted">{{ $lead->fax ?? 'NA'}}</p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-xs-12"> <strong>@lang('modules.lead.email')</strong> <br>
                                                        <p class="text-muted">{{ $lead->email ?? 'NA'}}</p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-xs-12"> <strong>@lang('modules.lead.secondEmail')</strong> <br>
                                                        <p class="text-muted">{{ $lead->second_email ?? 'NA'}}</p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-xs-12"> <strong>@lang('modules.lead.ref')</strong> <br>
                                                        <p class="text-muted">{{ $lead->ref ?? 'NA'}}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>

                                            <div class="row">
                                                <div class="col-xs-12">
                                                    <div class="col-xs-12"> <strong>@lang('modules.lead.designer')</strong> <br>
                                                        <p class="text-muted">{{ $lead->designer->name ?? 'NA'}}</p>
                                                    </div>
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
            <section>
                <div class="sttabs tabs-style-line">
                    <div class="content-wrap">
                        <section id="section-line-1" class="show">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="white-box">
                                        <h3 class="box-title b-b"><i class="fa fa-layers"></i> @lang('app.menu.appointmentHistory')</h3>
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>@lang('modules.events.eventName')</th>
                                                    <th>@lang('modules.events.designer')</th>
                                                    <th>@lang('modules.events.client')</th>
                                                    <th>@lang('modules.events.startOn')</th>
                                                    <th>@lang('modules.events.endOn')</th>
                                                    <th>@lang('modules.events.appointmentType')</th>
                                                    <th>@lang('modules.events.status')</th>
                                                </tr>
                                                </thead>
                                                <tbody id="timer-list">
                                                @forelse($appointments as $key=>$appointment)
                                                    <tr>
                                                        <td>{{ $key+1 }}</td>
                                                        <td>{{ ucwords($appointment->event_name) }}</td>
                                                        @if(!is_null($appointment->attendee))
                                                            <td>{{ $designers[$appointment->attendee->user_id] ?? '' }}</td>
                                                        @else
                                                            <td></td>
                                                        @endif
                                                        <td>{{ $lead->client->full_name ?? '' }}</td>
                                                        <td>{{ date('m-d-Y g:i A', strtotime($appointment->start_date_time)) ?? '' }}</td>
                                                        <td>{{ date('m-d-Y g:i A', strtotime($appointment->end_date_time)) ?? '' }}</td>
                                                        <td>{{ $eventTypes[$appointment->event_type] ?? '' }}</td>
                                                        <td>{{ $eventStatus[$appointment->status_id] ?? '' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4">@lang('messages.noAppointmentFound')</td>
                                                    </tr>
                                                @endforelse
                                                </tbody>
                                            </table>
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

@endpush