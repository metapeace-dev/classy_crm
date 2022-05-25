<div class="navbar-default sidebar" role="navigation">
    <div class="sidebar-nav navbar-collapse slimscrollsidebar">

        <!-- .User Profile -->
        <ul class="nav" id="side-menu">
            <li class="sidebar-search hidden-sm hidden-md hidden-lg">
                <!-- input-group -->
                <div class="input-group custom-search-form">
                    <input type="text" class="form-control" placeholder="Search..."> <span class="input-group-btn">
                            <button class="btn btn-default" type="button"> <i class="fa fa-search"></i> </button>
                            </span> </div>
                <!-- /input-group -->
            </li>
            <li class="user-pro">
                @if(is_null($user->image))
                    <a href="#" class="waves-effect"><img src="{{ asset('img/default-profile-3.png') }}" alt="user-img" class="img-circle"> <span class="hide-menu">{{ (strlen($user->name) > 24) ? substr(ucwords($user->name), 0, 20).'..' : ucwords($user->name) }}
                    <span class="fa arrow"></span></span>
                    </a>
                @else
                    <a href="#" class="waves-effect"><img src="{{ asset_url('avatar/'.$user->image) }}" alt="user-img" class="img-circle"> <span class="hide-menu">{{ ucwords($user->name) }}
                            <span class="fa arrow"></span></span>
                    </a>
                @endif
                <ul class="nav nav-second-level">
                    @if(!$user->hasRole('admin'))
                        <li><a href="{{ route('admin.profile.index') }}"><i class="ti-user"></i> @lang("app.menu.profileSettings")</a></li>
                    @endif
                    <li role="separator" class="divider"></li>
                    <li><a href="{{ route('logout') }}" onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();"
                        ><i class="fa fa-power-off"></i> @lang('app.logout')</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            {{ csrf_field() }}
                        </form>
                    </li>
                </ul>
            </li>
            <li><a href="{{ route('admin.dashboard') }}" class="waves-effect"><i class="icon-speedometer"></i> <span class="hide-menu">@lang('app.menu.dashboard') </span></a> </li>

            @if($user->hasRole('admin'))
                @if(in_array('employees',$modules))
                    <li>
                        <a href="{{ route('admin.employees.index') }}" class="waves-effect"><i class="fa fa-users" aria-hidden="true"></i> <span class="hide-menu"> @lang('app.menu.employees')</span></a>
                    </li>
                    <li>
                        <a href="{{ route('admin.clients.index') }}" class="waves-effect"><i class="fa fa-users" aria-hidden="true"></i> <span class="hide-menu"> @lang('app.menu.clients')</span></a>
                    </li>
                @endif
            @elseif($user->hasRole('employee'))
                <li><a href="{{ route('admin.clients.index') }}" class="waves-effect"><i class="fa fa-users" aria-hidden="true"></i> <span class="hide-menu"> @lang('app.menu.clients')</span></a>
                </li>
            @endif
            @if(in_array('leads',$modules))
                <li><a href="{{ route('admin.leads.index') }}" class="waves-effect"><i class="ti-receipt"></i> <span class="hide-menu"> @lang('app.menu.lead')</span></a>
                </li>
            @endif

            @if(in_array('projects',$modules))
                <li><a href="{{ route('admin.projects.index') }}" class="waves-effect"><i class="icon-layers"></i> <span class="hide-menu">@lang('app.menu.projects') </span></a> </li>
            @endif

            @if(in_array('payments',$modules) || in_array('expenses',$modules) )
                <li><a href="{{ route('admin.finance.index') }}" class="waves-effect"><i class="fa fa-money"></i> <span class="hide-menu"> @lang('app.menu.finance') @if($unreadExpenseCount > 0) <div class="notify notification-color"><span class="heartbit"></span><span class="point"></span></div>@endif <span class="fa arrow"></span> </span></a>
                    <ul class="nav nav-second-level">

                        @if(in_array('payments',$modules))
                            <li><a href="{{ route('admin.payments.index') }}">@lang('app.menu.payments')</a> </li>
                        @endif
                            <li><a href="{{ route('admin.commissions.index') }}">Commissions</a> </li>
                    </ul>
                </li>
            @endif

            @if(in_array('tasks',$modules))
                <li><a href="{{ route('admin.task.index') }}" class="waves-effect"><i class="ti-layout-list-thumb"></i> <span class="hide-menu"> @lang('app.menu.tasks') </span></a>
              </li>
            @endif

            @if(in_array('events',$modules))
                <li><a href="{{ route('admin.events.index') }}" class="waves-effect"><i class="icon-calender"></i> <span class="hide-menu">@lang('app.menu.appointments')</span></a> </li>
            @endif

            <li><a href="{{ route('admin.install_schedules.index') }}" class="waves-effect"><i class="icon-calender"></i> <span class="hide-menu">@lang('app.menu.installSchedules')</span></a> </li>


            <li><a href="{{ route('admin.reports.index') }}" class="waves-effect"><i class="ti-pie-chart"></i> <span class="hide-menu"> @lang('app.menu.reports') <span class="fa arrow"></span> </span></a>
                <ul class="nav nav-second-level">
                    <li><a href="{{ route('admin.client-report.index') }}">@lang('app.menu.clientReport')</a></li>
                    <li><a href="{{ route('admin.lead-report.index') }}">@lang('app.menu.leadReport')</a></li>
                    <li><a href="{{ route('admin.project-report.index') }}">@lang('app.menu.projectReport')</a></li>
                    <li><a href="{{ route('admin.appointment-report.index') }}">@lang('app.menu.appointmentReport')</a></li>
                    <li><a href="{{ route('admin.payment-report.index') }}">@lang('app.menu.paymentReport')</a></li>
                    <li><a href="{{ route('admin.commission-report.index') }}">@lang('app.menu.commissionReport')</a></li>
                    <li><a href="{{ route('admin.lead-source-report.index') }}">@lang('app.menu.leadSourceReport')</a></li>
                    <li><a href="{{ route('admin.install-schedule-report.index') }}">@lang('app.menu.installScheduleReport')</a></li>
               </ul>
            </li>
            @if($user->hasRole('admin'))
                <li><a href="{{ route('admin.report-settings.index') }}" class="waves-effect"><i class="ti-settings"></i> <span class="hide-menu"> @lang('app.menu.settings') <span class="fa arrow"></span> </span></a>
                    <ul class="nav nav-second-level">
                        <li><a href="{{ route('admin.report-settings.index') }}">@lang('app.menu.reportSettings')</a></li>
                        <li class="tab @if(\Illuminate\Support\Facades\Route::currentRouteName() == 'admin.email-settings.index') active @endif">
                            <a href="{{ route('admin.email-settings.index') }}">@lang('app.menu.notificationSettings')</a></li>
                    </ul>
                </li>
            @endif
        </ul>
    </div>
</div>
