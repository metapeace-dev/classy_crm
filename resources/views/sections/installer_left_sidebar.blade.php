<div class="navbar-default sidebar" role="navigation">
    <div class="sidebar-nav navbar-collapse slimscrollsidebar">
        <!-- .User Profile -->
        <ul class="nav" id="side-menu">


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
                    <li><a href="{{ route('installer.profile.index') }}"><i class="ti-user"></i> @lang("app.menu.profileSettings")</a></li>
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


            <li><a href="{{ route('installer.dashboard') }}" class="waves-effect"><i class="icon-speedometer"></i> <span class="hide-menu">@lang('app.menu.dashboard') </span></a> </li>

            @if(in_array('projects',$modules))
                <li><a href="{{ route('installer.projects.index') }}" class="waves-effect"><i class="icon-layers"></i> <span class="hide-menu">@lang('app.menu.projects') </span> @if($unreadProjectCount > 0) <div class="notify notification-color"><span class="heartbit"></span><span class="point"></span></div>@endif</a> </li>
            @endif

            @if(in_array('tasks',$modules))
                <li><a href="{{ route('installer.task.index') }}" class="waves-effect"><i class="icon-doc"></i> <span class="hide-menu">@lang('app.menu.tasks') </span></a> </li>
            @endif

{{--            @if(in_array('events',$modules))--}}
{{--                <li><a href="{{ route('installer.events.index') }}" class="waves-effect"><i class="icon-calender"></i> <span class="hide-menu">@lang('app.menu.Events')</span></a> </li>--}}
{{--            @endif--}}

            <li><a href="{{ route('installer.project-report.index') }}" class="waves-effect"><i class="ti-pie-chart"></i> <span class="hide-menu"> @lang('app.menu.reports') <span class="fa arrow"></span> </span></a>
                <ul class="nav nav-second-level">
                    <li><a href="{{ route('installer.project-report.index') }}">@lang('app.menu.projectReport')</a></li>
                </ul>
            </li>
        </ul>
    </div>
</div>
