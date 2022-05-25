@if($type == 'lead')
<ul class="nav tabs-vertical">
    <li class="tab">
        <a href="{{ route('admin.report-settings.index') }}" class="text-danger"><i class="ti-arrow-left"></i> @lang('app.menu.reportSettings')</a></li>
    <li class="tab @if($role == 'admin') active @endif">
        <a href="{{ route('admin.report-settings.lead-admin') }}">@lang('app.admin')</a></li>
    <li class="tab @if($role == 'employee') active @endif">
        <a href="{{ route('admin.report-settings.lead-employee') }}">@lang('app.employee')</a></li>
    <li class="tab @if($role == 'designer') active @endif">
        <a href="{{ route('admin.report-settings.lead-designer') }}">@lang('app.designer_role')</a></li>
</ul>
@elseif($type == 'project')
    <ul class="nav tabs-vertical">
        <li class="tab">
            <a href="{{ route('admin.report-settings.index') }}" class="text-danger"><i class="ti-arrow-left"></i> @lang('app.menu.reportSettings')</a></li>
        <li class="tab @if($role == 'admin') active @endif">
            <a href="{{ route('admin.report-settings.project-admin') }}">@lang('app.admin')</a></li>
        <li class="tab @if($role == 'employee') active @endif">
            <a href="{{ route('admin.report-settings.project-employee') }}">@lang('app.employee')</a></li>
        <li class="tab @if($role == 'designer') active @endif">
            <a href="{{ route('admin.report-settings.project-designer') }}">@lang('app.designer_role')</a></li>
    </ul>
@elseif($type == 'appointment')
    <ul class="nav tabs-vertical">
        <li class="tab">
            <a href="{{ route('admin.report-settings.index') }}" class="text-danger"><i class="ti-arrow-left"></i> @lang('app.menu.reportSettings')</a></li>
        <li class="tab @if($role == 'admin') active @endif">
            <a href="{{ route('admin.report-settings.appointment-admin') }}">@lang('app.admin')</a></li>
        <li class="tab @if($role == 'employee') active @endif">
            <a href="{{ route('admin.report-settings.appointment-employee') }}">@lang('app.employee')</a></li>
        <li class="tab @if($role == 'designer') active @endif">
            <a href="{{ route('admin.report-settings.appointment-designer') }}">@lang('app.designer_role')</a></li>
    </ul>
@elseif($type == 'payment')
    <ul class="nav tabs-vertical">
        <li class="tab">
            <a href="{{ route('admin.report-settings.index') }}" class="text-danger"><i class="ti-arrow-left"></i> @lang('app.menu.reportSettings')</a></li>
        <li class="tab @if($role == 'admin') active @endif">
            <a href="{{ route('admin.report-settings.payment-admin') }}">@lang('app.admin')</a></li>
        <li class="tab @if($role == 'employee') active @endif">
            <a href="{{ route('admin.report-settings.payment-employee') }}">@lang('app.employee')</a></li>
        <li class="tab @if($role == 'designer') active @endif">
            <a href="{{ route('admin.report-settings.payment-designer') }}">@lang('app.designer_role')</a></li>
    </ul>
@elseif($type == 'commission')
    <ul class="nav tabs-vertical">
        <li class="tab">
            <a href="{{ route('admin.report-settings.index') }}" class="text-danger"><i class="ti-arrow-left"></i> @lang('app.menu.reportSettings')</a></li>
        <li class="tab @if($role == 'admin') active @endif">
            <a href="{{ route('admin.report-settings.commission-admin') }}">@lang('app.admin')</a></li>
        <li class="tab @if($role == 'employee') active @endif">
            <a href="{{ route('admin.report-settings.commission-employee') }}">@lang('app.employee')</a></li>
        <li class="tab @if($role == 'designer') active @endif">
            <a href="{{ route('admin.report-settings.commission-designer') }}">@lang('app.designer_role')</a></li>
    </ul>
@elseif($type == 'client')
    <ul class="nav tabs-vertical">
        <li class="tab">
            <a href="{{ route('admin.report-settings.index') }}" class="text-danger"><i class="ti-arrow-left"></i> @lang('app.menu.reportSettings')</a></li>
        <li class="tab @if($role == 'admin') active @endif">
            <a href="{{ route('admin.report-settings.client-admin') }}">@lang('app.admin')</a></li>
        <li class="tab @if($role == 'employee') active @endif">
            <a href="{{ route('admin.report-settings.client-employee') }}">@lang('app.employee')</a></li>
        <li class="tab @if($role == 'designer') active @endif">
            <a href="{{ route('admin.report-settings.client-designer') }}">@lang('app.designer_role')</a></li>
    </ul>
@else
    <ul class="nav tabs-vertical">
        <li class="tab">
            <a href="{{ route('admin.report-settings.index') }}" class="text-danger"><i class="ti-arrow-left"></i> @lang('app.menu.reportSettings')</a></li>
        <li class="tab @if($role == 'admin') active @endif">
            <a href="{{ route('admin.report-settings.install-schedule-admin') }}">@lang('app.admin')</a></li>
        <li class="tab @if($role == 'employee') active @endif">
            <a href="{{ route('admin.report-settings.install-schedule-employee') }}">@lang('app.employee')</a></li>
        <li class="tab @if($role == 'designer') active @endif">
            <a href="{{ route('admin.report-settings.install-schedule-designer') }}">@lang('app.designer_role')</a></li>
    </ul>
@endif
<script src="{{ asset('plugins/bower_components/jquery/dist/jquery.min.js') }}"></script>
<script>
    var screenWidth = $(window).width();
    if(screenWidth <= 768){

        $('.tabs-vertical').each(function() {
            var list = $(this), select = $(document.createElement('select')).insertBefore($(this).hide()).addClass('settings_dropdown form-control');

            $('>li a', this).each(function() {
                var target = $(this).attr('target'),
                    option = $(document.createElement('option'))
                        .appendTo(select)
                        .val(this.href)
                        .html($(this).html())
                        .click(function(){
                            if(target==='_blank') {
                                window.open($(this).val());
                            }
                            else {
                                window.location.href = $(this).val();
                            }
                        });

                if(window.location.href == option.val()){
                    option.attr('selected', 'selected');
                }
            });
            list.remove();
        });

        $('.settings_dropdown').change(function () {
            window.location.href = $(this).val();
        })

    }
</script>