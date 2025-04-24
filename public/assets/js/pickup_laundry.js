document.addEventListener('DOMContentLoaded', function () {
    if ($('#calendarForPickUp').length > 0) {
        var calendarEl = document.getElementById('calendarForPickUp');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            headerToolbar: {
                left: 'prev,next',
                center: 'title',
                right: 'timeGridWeek,timeGridDay'
            },
            slotLabelFormat: [
                {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: hour12FromBlade
                }
            ],
            eventTimeFormat: { // like '14:30:00'
                hour: '2-digit',
                minute: '2-digit',
                hour12: hour12FromBlade
            },
            navLinks: true,
            selectable: true,
            selectMirror: true,
            height: 'auto',
            editable: false,
            nowIndicator: true,
            eventMaxStack: 1,
            select: function (arg) {
                $('#pickup-standard-modal').modal({
                    keyboard: false
                });
                var day = arg.start.getDay() + 1;
                $('#pickup_day_' + day).prop('checked', true);

                if (arg.allDay == true) {
                    document.getElementById('pickup_start_time').value = "00:00";
                    document.getElementById('pickup_end_time').value = "23:59";
                } else {
                    var startTime = ("0" + arg.start.getHours()).slice(-2) + ":" + ("0" + arg.start.getMinutes()).slice(-2);
                    var EndTime = ("0" + arg.end.getHours()).slice(-2) + ":" + ("0" + arg.end.getMinutes()).slice(-2);

                    document.getElementById('pickup_start_time').value = startTime;
                    document.getElementById('pickup_end_time').value = EndTime;
                }


                $('#pickup_slot_date').flatpickr({
                    minDate: "today",
                    defaultDate: arg.start
                });
            },

            events: function (info, successCallback, failureCallback) {
                $.ajax({
                    url: getURLForPickUp,
                    type: "GET",
                    data: "start=" + info.startStr + "&end=" + info.endStr,
                    dataType: 'json',
                    success: function (response) {
                        var startDate = moment(info.start).format('MMM DD');
                        var endDate = moment(info.end - 1).format('DD, YYYY');
                        $("#calendar_pickup_slot_alldays_table thead th").html(startDate + " - " + endDate);
                        $("#calendar_pickup_slot_alldays_table tbody").html("");
                        var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                        var slotDayList = [];
                        var events = [];
                        $.each(response, function (index, data) {
                            var slotDay = parseInt(moment(data.start).format('d')) + 1;
                            if(hour12FromBlade){
                                var slotStartTime = moment(data.start).format('h:mm A');
                                var slotEndTime = moment(data.end).format('h:mm A');
                            }else{
                                var slotStartTime = moment(data.start).format('H:mm');
                                var slotEndTime = moment(data.end).format('H:mm');
                            }

                $.each(days, function (key, value) {
                    if (slotDay == key + 1) {
                        if (slotDayList.includes(slotDay)) {
                            $("#calendar_pickup_slot_alldays_table tbody tr[data-pickup_slotDay='" + slotDay + "'] td:nth-child(2)").append("<br>" + slotStartTime + " - " + slotEndTime);
                        }
                        else {
                            $("#calendar_pickup_slot_alldays_table tbody").append("<tr data-pickup_slotDay=" + slotDay + "><td>" + value + "</td><td>" + slotStartTime + " - " + slotEndTime + "</td></tr>");
                        }
                    }
                });
                slotDayList.push(slotDay);

                events.push({
                    title: data.title,
                    start: data.start,
                    end: data.end,
                    type: data.type,
                    color: data.color,
                    type_id: data.type_id,
                    slot_id: data.slot_id,
                    slot_dine_in: data.slot_dine_in,
                    slot_takeaway: data.slot_takeaway,
                    slot_delivery: data.slot_delivery,
                });
            });
        successCallback(events);
    }
});
        },
eventResize: function(arg) {
},
eventClick: function(ev) {
    $('#pickup_edit-slot-modal').modal({
        //backdrop: 'static',
        keyboard: false
    });
    var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
    var day = ev.event.start.getDay() + 1;

    document.getElementById('pickup_edit_type').value = ev.event.extendedProps.type;
    document.getElementById('pickup_edit_day').value = day;
    document.getElementById('pickup_edit_type_id').value = ev.event.extendedProps.type_id;

    // Delete Slot Form
    document.getElementById('pickup_deleteSlotDayid').value = ev.event.extendedProps.type_id;
    document.getElementById('pickup_deleteSlotId').value = ev.event.extendedProps.slot_id;
    document.getElementById('pickup_deleteSlotType').value = ev.event.extendedProps.type;
    document.getElementById('pickup_deleteSlotTypeOld').value = ev.event.extendedProps.type;

    if (ev.event.extendedProps.type == 'date') {
        $("#pickup_edit_slotDate").prop("checked", true);
        $(".modal .pickup_forDateEdit").show();
    } else {
        $("#pickup_edit_slotDay").prop("checked", true);
        $(".modal .pickup_forDateEdit").hide();
    }

    if (ev.event.extendedProps.slot_delivery == 0) {
        $("#pickup_edit_delivery").prop("checked", false);
    }
    if (ev.event.extendedProps.slot_takeaway == 0) {
        $("#pickup_edit_takeaway").prop("checked", false);
    }
    if (ev.event.extendedProps.slot_dine_in == 0) {
        $("#pickup_edit_dine_in").prop("checked", false);
    }

    $('#pickup_edit_slot_date').flatpickr({
        minDate: "today",
        defaultDate: (ev.event.extendedProps.type == 'date') ? ev.event.start : ev.event.start
    });

    $('#pickup_edit-slot-modal #pickup_edit_slotlabel').text('Edit For All ' + days[day - 1] + '   ');

    var startTime = ("0" + ev.event.start.getHours()).slice(-2) + ":" + ("0" + ev.event.start.getMinutes()).slice(-2);
    document.getElementById('pickup_edit_start_time').value = startTime;

    var EndTime = '';

    if (ev.event.end) {
        EndTime = ("0" + ev.event.end.getHours()).slice(-2) + ":" + ("0" + ev.event.end.getMinutes()).slice(-2);
    }
    document.getElementById('pickup_edit_end_time').value = EndTime;

}
    });

calendar.render();
    }

});


$(document).on('change', '.pickup_slotTypeRadio', function () {
    var val = $(this).val();
    if (val == 'day') {
        $('.modal .pickup_weekDays').show();
        $('.modal .pickup_forDate').hide();
    } else if (val == 'date') {
        $('.modal .pickup_weekDays').hide();
        $('.modal .pickup_forDate').show();
    }
});

$(document).on('change', '#btn-save-slot', function () {
    var val = $(this).val();
    if (val == 'day') {
        $('.modal .pickup_weekDays').show();
        $('.modal .pickup_forDate').hide();
    } else if (val == 'date') {
        $('.modal .pickup_weekDays').hide();
        $('.modal .pickup_forDate').show();
    }
});

$(document).on('change', '.pickup_slotTypeEdit', function () {
    var val = $(this).val();
    $('#pickup_edit-slot-modal #pickup_deleteSlotType').val(val);
    if (val == 'day') {
        $('.modal .pickup_weekDaysEdit').show();
        $('.modal .pickup_forDateEdit').hide();
    } else if (val == 'date') {
        $('.modal .pickup_weekDaysEdit').hide();
        $('.modal .pickup_forDateEdit').show();
    }
});

$(document).on('click', '#pickup_deleteSlotBtn', function () {
    var date = $('#pickup_edit_slot_date').val();
    $('#pickup_edit-slot-modal #pickup_deleteSlotDate').val(date);
    if (confirm("Are you sure? You want to delete this slot.")) {
        $('#pickup_deleteSlotForm').submit();
    }
    return false;
});