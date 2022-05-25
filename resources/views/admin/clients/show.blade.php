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
                <li class="active">@lang('app.menu.projects')</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection


@section('content')

    <div class="row">
        <div class="sttabs tabs-style-line">
            <div class="white-box">
                <nav>
                    <ul>
                        <li class="tab-current"><a href="{{ route('admin.clients.show', $client->id) }}"><span>@lang('modules.lead.view')</span></a>
                        <li><a href="{{ route('admin.clients.projects', $client->id) }}"><span>@lang('app.menu.projects')</span></a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        <div class="col-md-12">
            <div class="white-box">
                <h2>@lang('modules.client.clientDetails') - #{{$client->id}}</h2>

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

        <div class="col-md-12">
            <section>
                <div class="sttabs tabs-style-line">
                    <div class="content-wrap">
                        <section id="section-line-1" class="show">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="white-box">
                                        <h3 class="box-title b-b"><i class="fa fa-layers"></i> @lang('app.menu.projects')</h3>
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>@lang('modules.projects.projectName')</th>
                                                    <th>@lang('modules.projects.designer')</th>
                                                    <th>@lang('modules.projects.status')</th>
                                                    <th>@lang('app.action')</th>
                                                </tr>
                                                </thead>
                                                <tbody id="timer-list">
                                                @forelse($client->projects as $key=>$project)
                                                    <tr>
                                                        <td>{{ $project->id }}</td>
                                                        <td>{{ ucwords($project->project_name) }}</td>
                                                        <td>{{ $designers[$project->user_id] ?? '' }}</td>
                                                        <td>{{ ucwords($project->status) ?? '' }}</td>
                                                        <td><a href="{{ route('admin.projects.show', $project->id) }}" class="label label-info">@lang('modules.client.viewDetails')</a></td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4">@lang('messages.noProjectFound')</td>
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

        <div class="col-md-12">
            <section>
                <div class="sttabs tabs-style-line">
                    <div class="content-wrap">
                        <section id="section-line-1" class="show">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="white-box">
                                        <h3 class="box-title b-b"><i class="fa fa-layers"></i> @lang('app.menu.lead')</h3>
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>@lang('modules.lead.first_name')</th>
                                                    <th>@lang('modules.lead.last_name')</th>
                                                    <th>@lang('modules.lead.clientEmail')</th>
                                                    <th>@lang('modules.lead.designer')</th>
                                                    <th>@lang('modules.lead.status')</th>
                                                    <th>@lang('app.action')</th>
                                                </tr>
                                                </thead>
                                                <tbody id="timer-list">
                                                @forelse($client->leads as $key=>$lead)
                                                    <?php 
                                                    $selected = '';
                                                    foreach ($status as $st) {
                                                        if($lead->status_id == $st->id){
                                                            $selected = $st->type; break;
                                                        }
                                                    } ?>
                                                    <tr>
                                                        <td>{{ $lead->id }}</td>
                                                        <td>{{ ucwords($lead->first_name) }}</td>
                                                        <td>{{ ucwords($lead->last_name) }}</td>
                                                        <td>{{ ucwords($lead->email) }}</td>
                                                        <td>{{ $designers[$lead->user_id] ?? '' }}</td>
                                                        <td>{{ ucwords($selected) ?? '' }}</td>
                                                        <td><a href="{{ route('admin.leads.show', $lead->id) }}" class="label label-info">@lang('modules.lead.viewDetails')</a></td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4">@lang('messages.noLeadFound')</td>
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
        <div class="col-md-12">
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
                                                    <th>@lang('modules.events.startOn')</th>
                                                    <th>@lang('modules.events.endOn')</th>
                                                    <th>@lang('modules.events.appointmentType')</th>
                                                </tr>
                                                </thead>
                                                <tbody id="timer-list">
                                                @forelse($appointments as $key=>$appointment)
                                                    <tr>
                                                        <td>{{ $key+1 }}</td>
                                                        <td>{{ ucwords($appointment->event_name) }}</td>
                                                        <td>{{ $designers[$appointment->attendee->user_id] ?? '' }}</td>
                                                        <td>{{ $appointment->start_date_time->format($global->date_format. ' g:i:s A') ?? '' }}</td>
                                                        <td>{{ $appointment->end_date_time->format($global->date_format. ' g:i:s A') ?? '' }}</td>
                                                        <td>{{ $eventTypes[$appointment->event_type] ?? '' }}</td>
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