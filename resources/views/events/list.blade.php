@extends('layouts.app')

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-datetimepicker@2.5.20/jquery.datetimepicker.css">
    <style>
        #calendar a {
            color: #000000;
            text-decoration: none;
        }

        .mr-auto {
            margin-right: auto;
        }
    </style>
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div id="calendar">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->

<!-- Modal Body -->
<!-- if you want to close by clicking outside the modal, delete the last endpoint:data-bs-backdrop and data-bs-keyboard -->
<div class="modal fade" id="eventModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitleId">Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{-- <form action=""> --}}
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <input type="hidden" id="eventId">
                        <label for="">Title</label>
                        <input type="title" name="title" id="title" class="form-control" placeholder="Enter Title">
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" name="is_all_day"  type="checkbox" id="is_all_day">
                        <label class="form-check-label" for="is_all_day">
                          All Day
                        </label>
                      </div>
                    <div class="form-group mb-3">
                        <label for="">Start Date/Time</label>
                        <input type="text" class="form-control" name="startDate" id="startDateTime" placeholder="Select start date">
                    </div>
                    <div class="form-group mb-3">
                        <label for="">End Date/Time</label>
                        <input type="text" class="form-control" name="endDate" id="endDateTime" placeholder="Select end date">
                    </div>
                    <div class="form-group mb-3">
                        <label for="">Description</label>
                        <textarea name="description" id="description" cols="30" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger mr-auto" style="..." id="deleteEventBtn" onclick="deleteEvent()">Delete Event</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" onclick="submitEventFormData()">Save Changes</button>
                </div>
            {{-- </form> --}}
        </div>
    </div>
</div>
    
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script type="module" src="https://cdn.jsdelivr.net/npm/jquery-datetimepicker@2.5.20/build/jquery.datetimepicker.full.min.js"></script>
    <script>
        var calendar = null;
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                initialDate: new Date(),
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: `{{route('refetch-events')}}`,
                editable: true,
                dateClick: function (info){
                    let startDate, endDate, allDay;
                    allDay = $('#is_all_day').prop('checked');
                    if (allDay) {
                        startDate = moment(info.date).format("YYYY-MM-DD");
                        endDate = moment(info.date).format("YYYY-MM-DD");
                        initializeStartDateEndDateFormat('Y-m-d', true);
                    }else{
                        initializeStartDateEndDateFormat('Y-m-d H:i', false);
                        startDate = moment(info.date).format("YYYY-MM-DD HH:mm:ss");
                        endDate = moment(info.date).add(30, "minutes").format("YYYY-MM-DD HH:mm:ss");
                    }
                    $('#startDateTime').val(startDate);
                    $('#endDateTime').val(endDate);
                    modalReset();
                    $('#eventModal').modal('show');
                },
                eventClick: function (info) {
                    modalReset();
                    const event = info.event;
                    $('#title').val(event.title);
                    $('#eventId').val(event.id);
                    $('#description').val(event.extendedProps.description);
                    $('#startDateTime').val(event.extendedProps.startDay);
                    $('#endDateTime').val(event.extendedProps.endDay);
                    $('#is_all_day').prop('checked', event.allDay);
                    $('#deleteEventBtn').show();
                    $('#eventModal').modal('show');
                    if (event.allDay) {
                        initializeStartDateEndDateFormat('Y-m-d', true);
                    }else{
                        initializeStartDateEndDateFormat('Y-m-d H:i', false);
                    }
                },
                eventDrop: function(info){
                    const event = info.event;
                    resizeEventUpdate(event);
                },
                eventResize: function(info){
                    const event = info.event;
                    resizeEventUpdate(event);
                },
            });
            calendar.render();
            $('#is_all_day').change(function() {
                let is_all_day = $(this).prop('checked');
                if (is_all_day) {
                    let start = $('#startDateTime').val().slice(0, 10);
                    $('#startDateTime').val(start);
                    let endDateTime = $('#endDateTime').val().slice(0, 10);
                    $('#endDateTime').val(endDateTime);
                    initializeStartDateEndDateFormat('Y-m-d', is_all_day);
                }else{
                    let start = $('#startDateTime').val().slice(0, 10);
                    $('#startDateTime').val(start + " 00:00");
                    let endDateTime = $('#endDateTime').val().slice(0, 10);
                    $('#endDateTime').val(endDateTime + " 00:30");
                    initializeStartDateEndDateFormat('Y-m-d H:i', is_all_day);
                }
            })
        })
        function initializeStartDateEndDateFormat(format, allDay) {
            let timePicker = !allDay;
            $('#startDateTime').datetimepicker({
                format: format,
                timepicker: timePicker
            });
            $('#endDateTime').datetimepicker({
                format: format,
                timepicker: timePicker
            });
        }
        function modalReset(){
            $('#title').val("");
            $('#description').val("");
            $('#eventId').val("");
            $('#deleteEventBtn').hide();
        }

        function submitEventFormData(){
            let eventId = $('#eventId').val();
            let url = "{{route('events.store')}}";
            let postData = {
                start: $('#startDateTime').val(),
                end: $('#endDateTime').val(),
                title: $('#title').val(),
                description: $('#description').val(),
                is_all_day: $('#is_all_day').prop('checked') ? 1 : 0
            };
            // if (postData.is_all_day) {
                // postData.end = moment().add(1, "days").format("YYYY-MM-DD");
            // }
            if (eventId) {
                url = `{{url('/')}}` + `/events/${eventId}`;
                postData._method = "PUT";
            }
            //jQuery Ajax
            $.ajax({
                type: "POST",
                url: url,
                dataType: "json",
                data: postData,
                success: function (res) {
                    if (res.success) {
                        calendar.refetchEvents();
                        $('#eventModal').modal('hide');
                    }else{
                        alert('Something going wrong');
                    }
                }
            });
        }

        function deleteEvent() {
            if (window.confirm("Are you sure, you want to delete this event!")) {
                let eventId = $('#eventId').val();
                let url = '';
                if (eventId) {
                    url = `{{url('/')}}` + `/events/${eventId}`;
                }
                $.ajax({
                    type: "DELETE",
                    url: url,
                    dataType: "json",
                    data: {},
                    success: function (res) {
                        if (res.success) {
                            calendar.refetchEvents();
                            $('#eventModal').modal('hide');
                        }else{
                            alert('Something going wrong');
                        }
                    }
                })
            }
        }

        function resizeEventUpdate(event) {
            let eventId = event.id;
            let url = `{{url('/')}}` + `/events/${eventId}/resize`;
            let start  = null, end = null;

            if (event.allDay) {
                start = moment(event.start).format("YYYY-MM-DD");
                end = start;
                if (event.end) {
                    end = moment(event.end).format("YYYY-MM-DD");
                }
            }else{
                start = moment(event.start).format("YYYY-MM-DD HH:mm:ss");
                end = start;
                if (event.end) {
                    end = moment(event.end).format("YYYY-MM-DD HH:mm:ss");
                }
            }

            let postData = {
                start: start,
                end: end,
                is_all_day: event.allDay ? 1 : 0,
                _method: "PUT"
            };

            $.ajax({
                type: "POST",
                url: url,
                dataType: "json",
                data: postData,
                success: function (res) {
                    if (res.success) {
                        calendar.refetchEvents();
                    }else{
                        alert('Something going wrong');
                    }
                }
            })
        }
    </script>
@endpush