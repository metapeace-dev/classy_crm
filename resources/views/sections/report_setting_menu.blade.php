<ul class="nav tabs-vertical">
    <li class="tab">
        <a href="{{ route('admin.report-settings.client-admin') }}">@lang('app.menu.clientReportSettings')</a></li>
    <li class="tab">
        <a href="{{ route('admin.report-settings.lead-admin') }}">@lang('app.menu.leadReportSettings')</a></li>
    <li class="tab">
        <a href="{{ route('admin.report-settings.project-admin') }}?type=employee">@lang('app.menu.projectReportSettings')</a></li>
    <li class="tab">
        <a href="{{ route('admin.report-settings.appointment-admin') }}?type=client">@lang('app.menu.appointmentReportSettings')</a></li>
    <li class="tab">
        <a href="{{ route('admin.report-settings.payment-admin') }}">@lang('app.menu.paymentReportSettings')</a></li>
    <li class="tab">
        <a href="{{ route('admin.report-settings.commission-admin') }}">@lang('app.menu.commissionReportSettings')</a></li>
    <li class="tab">
        <a href="{{ route('admin.report-settings.install-schedule-admin') }}">@lang('app.menu.installScheduleReportSettings')</a></li>
</ul>

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