<div class="panel panel-default">
    <div class="panel-heading "><i class="fa fa-stop-circle-o"></i> @lang('modules.projects.activeTimers')
        <div class="panel-action">
            <a href="javascript:;" class="close" data-dismiss="modal"><i class="ti-close"></i></a>
        </div>
    </div>
    <div class="panel-wrapper collapse in">
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>@lang('modules.projects.whoWorking')</th>
                        <th> @if($logTimeFor->log_time_for == 'task')
                                @lang('app.task')
                            @else
                                @lang('app.project')
                            @endif
                            @lang('app.name')</th>

                        <th>@lang('modules.employees.memo')</th>
                        <th>@lang('modules.projects.activeSince')</th>
                        <td> </td>
                    </tr>
                    </thead>
                    <tbody id="active-timer-list">
                    @forelse($activeTimers as $key=>$time)
                        <tr>
                            <td>{{ $key+1 }}</td>
                            <td>{{ ucwords($time->name) }}</td>
                            <td>{{ ucwords($time->project_name) }}</td>
                            <td class="font-bold ">{{ ucwords(substr($time->memo, 0, 50)) }}</td>
                            <td class="font-bold "><i class="ti-alarm-clock"></i> <span class="timer">{{ $time->timer }}</span></td>
                            <td><a href="javascript:;" data-time-id="{{ $time->id }}" class="label label-danger stop-active-timer">@lang('app.stop')</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">@lang('messages.noActiveTimer')</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>

    $(document).ready(function(e) {
            function updateTimer($worked) {
                var myTime = $worked.html();
                var ss = myTime.split(":");
                var hours = ss[0];
                var mins = ss[1];
                var secs = ss[2];
                secs = parseInt(secs)+1;

                if(secs > 59){
                    secs = '00';
                    mins = parseInt(mins)+1;
                }

                if(mins > 59){
                    secs = '00';
                    mins = '00';
                    hours = parseInt(hours)+1;
                }

                if(hours.toString().length < 2) {
                    hours = '0'+hours;
                }
                if(mins.toString().length < 2) {
                    mins = '0'+mins;
                }
                if(secs.toString().length < 2) {
                    secs = '0'+secs;
                }
                var ts = hours+':'+mins+':'+secs;

                $worked.html(ts);
            }
            $timers = $(".timer");
            $timers.each(function ($key, $timer) {
                setInterval(function() {
                    updateTimer($($timer))
                }, 1000);
            });

        $('#active-timer-list').on('click', '.stop-active-timer', function () {
            var id = $(this).data('time-id');
            var url = '{{route('installer.all-time-logs.stopTimer', ':id')}}';
            url = url.replace(':id', id);
            var token = '{{ csrf_token() }}';
            $.easyAjax({
                url: url,
                type: "POST",
                data: {timeId: id, _token: token},
                success: function (data) {

                    if(data.buttonHtml !== '' && data.buttonHtml !== undefined){
                        $('#timer-section').html(data.buttonHtml);
                    }

                    $('#active-timer-list').html(data.html);
                    $('#activeCurrentTimerCount').html(data.activeTimers);
                }
            })

        });
    });

</script>
