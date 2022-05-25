
!function($) {
    "use strict";

    var CalendarApp = function() {
        this.$body = $("body")
        this.$calendar = $('#calendar'),
        this.$event = ('#calendar-events div.calendar-events'),
        this.$categoryForm = $('#add-new-event form'),
        this.$extEvents = $('#calendar-events'),
        this.$saveCategoryBtn = $('.save-category'),
        this.$calendarObj = null,
        this.$calArg = null
    };

        /* on drop */
        CalendarApp.prototype.onChangeEvent = function (info) {
            swal({
                title: "Are you sure?",
                text: "You have moved the appointment!",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes",
                cancelButtonText: "Cancel",
                closeOnConfirm: true,
                closeOnCancel: true
            }, function(isConfirm){
                if (isConfirm) {
                    var url = "/events/move_event/:id";
                    url = url.replace(':id', info.event.id);
                    var token = $('#createEvent [name="_token"]').val();
                    let start = info.event.start;
                    var curr_date = start.getDate();
                    if(curr_date < 10){
                        curr_date = '0'+curr_date;
                    }
                    var curr_month = start.getMonth();
                    curr_month = curr_month+1;
                    if(curr_month < 10){
                        curr_month = '0'+curr_month;
                    }
                    var curr_year = start.getFullYear();
                    var start_date = curr_month+'/'+curr_date+'/'+curr_year;
                    var start_time = formatAMPM(start);

                    let end = info.event.end;
                    var curr_date = end.getDate();
                    if(curr_date < 10){
                        curr_date = '0'+curr_date;
                    }
                    var curr_month = end.getMonth();
                    curr_month = curr_month+1;
                    if(curr_month < 10){
                        curr_month = '0'+curr_month;
                    }
                    var curr_year = end.getFullYear();
                    var end_date = curr_month+'/'+curr_date+'/'+curr_year;
                    var end_time = formatAMPM(end);

                    if(info.newResource){
                        var data = {
                            '_token': token,
                            'start_date' : start_date,
                            'start_time' : start_time,
                            'end_date' : end_date,
                            'end_time' : end_time,
                            'user_id' : info.newResource.id
                        };
                    }
                    else{
                        var data = {
                            '_token': token,
                            'start_date' : start_date,
                            'start_time' : start_time,
                            'end_date' : end_date,
                            'end_time' : end_time,
                        };
                    }

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: data,
                        success: function (response) {
                            if (response.status == "success") {
                                $.unblockUI();
                            }
                        },
                        error : function(xhr, status, error){
                            info.revert();
                        }
                    });
                }
                else{
                    info.revert();
                }
            });
        },
        /* on click on event */
        CalendarApp.prototype.onEventClick = function (info) {
            var $this = this;
            if(info.view.type === "dayGridMonth"){
                let dateStr = getDateStr(info.event.start);
                $('#select_datepicker').datepicker("setDate", info.event.start);
                $this.$calendarObj.changeView('resourceTimeGridDay', dateStr);
            }
        },

        /* on select */
        CalendarApp.prototype.onSelect = function (arg) {
            var $this = this;
            $this.$calArg = arg;
        },
        CalendarApp.prototype.enableDrag = function() {
            //init events
            $(this.$event).each(function () {
                // create an Event Object (http://arshaw.com/fullcalendar/docs/event_data/Event_Object/)
                // it doesn't need to have a start or end
                var eventObject = {
                    title: $.trim($(this).text()) // use the element's text as the event title
                };
                // store the Event Object in the DOM element so we can get to it later
                $(this).data('eventObject', eventObject);
                // make the event draggable using jQuery UI
                $(this).draggable({
                    zIndex: 999,
                    revert: true,      // will cause the event to go back to its
                    revertDuration: 0  //  original position after the drag
                });
            });
        }
    /* Initializing */
    CalendarApp.prototype.init = function() {
        //this.enableDrag();
        /*  Initialize the calendar  */
        var date = new Date();
        var d = date.getDate();
        var m = date.getMonth();
        var y = date.getFullYear();
        var form = '';
        var today = new Date($.now());

        var defaultEvents = taskEvents;
        var defaultResources = resources;
        var $this = this;
        $this.$calendarObj = new FullCalendar.Calendar(this.$calendar.get(0), {
            schedulerLicenseKey: '0376234288-fcs-1570810955',
            plugins: [ 'interaction', 'resourceDayGrid', 'resourceTimeGrid' ],
            aspectRatio: 1.8,
            scrollTime: '07:00',
            defaultDate: today,
            defaultView: 'resourceTimeGridDay',
            handleWindowResize: true,
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'resourceTimeGridDay,timeGridWeek,dayGridMonth'
            },
            resources: defaultResources,
            events: defaultEvents,
            editable: true,
            eventLimit: true, // allow "more" link when too many events
            selectable: true,
            eventDrop: function(info) { $this.onChangeEvent(info); },
            select: function (arg) { $this.onSelect(arg); },
            eventClick: function(info) { $this.onEventClick(info); },
            eventResize: function(info) {
                $this.onChangeEvent(info);
            },
            eventRender: function(info) {
                $(info.el).attr('data-event-id', info.event.id); $(info.el).attr('data-event-type', info.event.extendedProps.event_type);
            },
            dateClick: function(info) {
                if(info.view.type === "dayGridMonth"){
                    $('#select_datepicker').datepicker("setDate", info.date);
                    $this.$calendarObj.changeView('resourceTimeGridDay', info.dateStr);
                }
            }
        });

        $this.$calendarObj.render();

        $('body').on('click', '.fc-prev-button', function(){
            if($this.$calendarObj.state.viewType === "resourceTimeGridDay"){
                let cur_date = $this.$calendarObj.getDate();
                $('#select_datepicker').datepicker("setDate", cur_date);
            }
        });

        $('body').on('click', '.fc-next-button', function(){
            if($this.$calendarObj.state.viewType === "resourceTimeGridDay"){
                let cur_date = $this.$calendarObj.getDate();
                $('#select_datepicker').datepicker("setDate", cur_date);
            }
        });

        $('#calendar .fc-today-button').on('click', function(){
            if($this.$calendarObj.state.viewType === "resourceTimeGridDay"){
                $('#select_datepicker').datepicker("setDate", new Date());
            }
        });

        $('#calendar .fc-resourceTimeGridDay-button').on('click', function(){
            $('#select_datepicker').datepicker("setDate", $this.$calendarObj.state.dateProfile.currentRange.start);
        });

        //on new event
        this.$saveCategoryBtn.on('click', function(){
            var categoryName = $this.$categoryForm.find("input[name='category-name']").val();
            var categoryColor = $this.$categoryForm.find("select[name='category-color']").val();
            if (categoryName !== null && categoryName.length != 0) {
                $this.$extEvents.append('<div class="calendar-events bg-' + categoryColor + '" data-class="bg-' + categoryColor + '" style="position: relative;"><i class="fa fa-move"></i>' + categoryName + '</div>')
                $this.enableDrag();
            }

        });
        
    },

    //init CalendarApp
    $.CalendarApp = new CalendarApp, $.CalendarApp.Constructor = CalendarApp

}(window.jQuery),

//initializing CalendarApp
    function($) {
        "use strict";
        $.CalendarApp.init()
    }(window.jQuery);