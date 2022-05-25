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
        <section>
            <div class="sttabs tabs-style-line">
                <div class="white-box">
                    <nav>
                        <ul>
                            <li><a href="{{ route('admin.clients.show', $client->id) }}"><span>@lang('modules.lead.view')</span></a>
                            <li class="tab-current"><a href="{{ route('admin.clients.projects', $client->id) }}"><span>@lang('app.menu.projects')</span></a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </section>

        <div class="col-md-12">
            <div class="content-wrap">
                <div class="panel-group" id="accordion">
                    @forelse($client->projects as $project)
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#accordion" href="{{ '#project'. $project->id }}">
                                        <label>
                                            Project : {{ucwords($project->project_name)}} - #{{ $project->id }}
                                        </label>
                                    </a>
                                </h4>
                            </div>
                            <div id="{{ 'project'. $project->id }}" class="panel-collapse collapse">
                                <div class="panel-body" style="background-color: cadetblue;">
                                    <div class="white-box">
                                        <h3 class="box-title b-b"><i class="fa fa-layers"></i> @lang('modules.projects.projectInfo')</h3>
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>@lang('modules.projects.projectName')</th>
                                                    <th>@lang('modules.projects.designer')</th>
                                                    <th>@lang('modules.projects.salesPrice')</th>
                                                    <th>@lang('modules.projects.status')</th>
                                                    <th>@lang('app.action')</th>
                                                </tr>
                                                </thead>
                                                <tbody id="timer-list">
                                                    <tr>
                                                        <td>{{ $project->id }}</td>
                                                        <td>{{ ucwords($project->project_name) }}</td>
                                                        <td>{{ $designers[$project->user_id] ?? '' }}</td>
                                                        <td>{{ '$'.number_format($project->sales_price, 2, '.', ',') }}</td>
                                                        <td>{{ ucwords($project->status) ?? '' }}</td>
                                                        <td><a href="{{route("admin.projects.edit", $project->id)}}" data-toggle="tooltip" data-original-title="Edit" class="btn btn-info btn-circle"><i class="fa fa-pencil"></i></a></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="white-box">
                                        <h3 class="box-title b-b"><i class="fa fa-layers"></i> @lang('modules.lead.leadInfo')</h3>
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>@lang('modules.lead.first_name')</th>
                                                    <th>@lang('modules.lead.last_name')</th>
                                                    <th>@lang('modules.lead.designer')</th>
                                                    <th>@lang('modules.lead.status')</th>
                                                    <th>@lang('app.action')</th>
                                                </tr>
                                                </thead>
                                                <tbody id="timer-list">
                                                @if(!empty($project->lead))
                                                    <?php
                                                    $selected = '';
                                                    foreach ($status as $st) {
                                                        if($project->lead->status_id == $st->id){
                                                            $selected = $st->type; break;
                                                        }
                                                    } ?>
                                                    <tr>
                                                        <td>{{ $project->lead->id }}</td>
                                                        <td>{{ ucwords($project->lead->first_name) }}</td>
                                                        <td>{{ ucwords($project->lead->last_name) }}</td>
                                                        <td>{{ $designers[$project->lead->user_id] ?? '' }}</td>
                                                        <td>{{ ucwords($selected) ?? '' }}</td>
                                                        <td><a href="{{route("admin.leads.edit", $project->lead->id)}}" data-toggle="tooltip" data-original-title="Edit" class="btn btn-info btn-circle"><i class="fa fa-pencil"></i></a></td>
                                                    </tr>
                                                @else
                                                    <tr>
                                                        <td colspan="4">@lang('messages.noLeadFound')</td>
                                                    </tr>
                                                @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

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
                                                @forelse($project->appointments() as $appointment)
                                                    <tr>
                                                        <td>{{ $appointment->id }}</td>
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

                                    <div class="white-box">
                                        <h3 class="box-title b-b"><i class="fa fa-layers"></i> @lang('modules.payments.paymentInfo')</h3>
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>@lang('modules.payments.designer')</th>
                                                    <th>@lang('modules.payments.amount')</th>
                                                    <th>@lang('modules.payments.paidOn')</th>
                                                    <th>@lang('modules.payments.payment_type')</th>
                                                    <th>@lang('app.status')</th>
                                                    <th>@lang('app.action')</th>
                                                </tr>
                                                </thead>
                                                <tbody id="timer-list">
                                                @forelse($project->payments as $payment)
                                                    <tr>
                                                        <td>{{ $payment->id }}</td>
                                                        <td>{{ $designers[$project->user_id] ?? '' }}</td>
                                                        <td>{{ '$' . number_format((float)$payment->amount, 2, '.', '') }}</td>
                                                        <td>{{ $payment->paid_on->format($global->date_format) ?? '' }}</td>
                                                        <td>{{ strtoupper($payment->payment_type)}}</td>
                                                        <td>{{ strtoupper($payment->status)}}</td>
                                                        <td><a href="{{route("admin.payments.edit", $payment->id)}}" data-toggle="tooltip" data-original-title="Edit" class="btn btn-info btn-circle"><i class="fa fa-pencil"></i></a></td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4">No Payment Found</td>
                                                    </tr>
                                                @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="white-box">
                                        <h3 class="box-title b-b"><i class="fa fa-layers"></i> @lang('modules.commissions.commissionInfo')</h3>
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>@lang('modules.commissions.designer')</th>
                                                    <th>@lang('modules.commissions.amount')</th>
                                                    <th>@lang('modules.commissions.pay_start_date')</th>
                                                    <th>@lang('modules.commissions.pay_end_date')</th>
                                                    <th>@lang('modules.commissions.payment_type')</th>
                                                    <th>@lang('app.status')</th>
                                                    <th>@lang('app.action')</th>
                                                </tr>
                                                </thead>
                                                <tbody id="timer-list">
                                                @forelse($project->commissions as $commission)
                                                    <tr>
                                                        <td>{{ $commission->id }}</td>
                                                        <td>{{ $designers[$project->user_id] ?? '' }}</td>
                                                        <td>{{ '$' . number_format((float)$commission->amount, 2, '.', '') }}</td>
                                                        <td>{{ $commission->pay_start_date->format($global->date_format) ?? '' }}</td>
                                                        <td>{{ $commission->pay_end_date->format($global->date_format) ?? '' }}</td>
                                                        <td>{{ strtoupper($commission->payment->payment_type ?? '')}}</td>
                                                        <td>{{ strtoupper($commission->status)}}</td>
                                                        <td><a href="{{route("admin.commissions.edit", $commission->id)}}" data-toggle="tooltip" data-original-title="Edit" class="btn btn-info btn-circle"><i class="fa fa-pencil"></i></a></td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4">No Commission Found</td>
                                                    </tr>
                                                @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="panel panel-default">
                            <div>@lang('messages.noProjectFound')</div>
                        </div>
                    @endforelse
                </div>
            </div><!-- /content -->
        </div>
    </div>
    <!-- .row -->

@endsection