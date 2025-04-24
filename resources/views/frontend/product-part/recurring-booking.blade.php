<style>
  /*!
* Datepicker for Bootstrap v1.5.0 (https://github.com/eternicode/bootstrap-datepicker)
*
* Copyright 2012 Stefan Petre
* Improvements by Andrew Rowls
* Licensed under the Apache License v2.0 (http://www.apache.org/licenses/LICENSE-2.0)
*/
.datepicker {
padding: 4px;
-webkit-border-radius: 4px;
-moz-border-radius: 4px;
border-radius: 4px;
direction: ltr;
}
.datepicker-inline {
width: 220px;
}
.datepicker.datepicker-rtl {
direction: rtl;
}
.datepicker.datepicker-rtl table tr td span {
float: right;
}
.datepicker-dropdown {
top: 0;
left: 0;
}
.datepicker-dropdown:before {
content: '';
display: inline-block;
border-left: 7px solid transparent;
border-right: 7px solid transparent;
border-bottom: 7px solid #999999;
border-top: 0;
border-bottom-color: rgba(0, 0, 0, 0.2);
position: absolute;
}
.datepicker-dropdown:after {
content: '';
display: inline-block;
border-left: 6px solid transparent;
border-right: 6px solid transparent;
border-bottom: 6px solid #ffffff;
border-top: 0;
position: absolute;
}
.datepicker-dropdown.datepicker-orient-left:before {
left: 6px;
}
.datepicker-dropdown.datepicker-orient-left:after {
left: 7px;
}
.datepicker-dropdown.datepicker-orient-right:before {
right: 6px;
}
.datepicker-dropdown.datepicker-orient-right:after {
right: 7px;
}
.datepicker-dropdown.datepicker-orient-bottom:before {
top: -7px;
}
.datepicker-dropdown.datepicker-orient-bottom:after {
top: -6px;
}
.datepicker-dropdown.datepicker-orient-top:before {
bottom: -7px;
border-bottom: 0;
border-top: 7px solid #999999;
}
.datepicker-dropdown.datepicker-orient-top:after {
bottom: -6px;
border-bottom: 0;
border-top: 6px solid #ffffff;
}
.datepicker > div {
display: none;
}
.datepicker table {
margin: 0;
-webkit-touch-callout: none;
-webkit-user-select: none;
-khtml-user-select: none;
-moz-user-select: none;
-ms-user-select: none;
user-select: none;
}
.datepicker td,
.datepicker th {
text-align: center;
width: 20px;
height: 20px;
-webkit-border-radius: 4px;
-moz-border-radius: 4px;
border-radius: 4px;
border: none;
}
.table-striped .datepicker table tr td,
.table-striped .datepicker table tr th {
background-color: transparent;
}
.datepicker table tr td.day:hover,
.datepicker table tr td.day.focused {
background: #eeeeee;
cursor: pointer;
}
.datepicker table tr td.old,
.datepicker table tr td.new {
color: #999999;
}
.datepicker table tr td.disabled,
.datepicker table tr td.disabled:hover {
background: none;
color: #999999;
cursor: default;
}
.datepicker table tr td.highlighted {
background: #d9edf7;
border-radius: 0;
}
.datepicker table tr td.today,
.datepicker table tr td.today:hover,
.datepicker table tr td.today.disabled,
.datepicker table tr td.today.disabled:hover {
background-color: #fde19a;
background-image: -moz-linear-gradient(to bottom, #fdd49a, #fdf59a);
background-image: -ms-linear-gradient(to bottom, #fdd49a, #fdf59a);
background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#fdd49a), to(#fdf59a));
background-image: -webkit-linear-gradient(to bottom, #fdd49a, #fdf59a);
background-image: -o-linear-gradient(to bottom, #fdd49a, #fdf59a);
background-image: linear-gradient(to bottom, #fdd49a, #fdf59a);
background-repeat: repeat-x;
filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#fdd49a', endColorstr='#fdf59a', GradientType=0);
border-color: #fdf59a #fdf59a #fbed50;
border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
filter: progid:DXImageTransform.Microsoft.gradient(enabled=false);
color: #000;
}
.datepicker table tr td.today:hover,
.datepicker table tr td.today:hover:hover,
.datepicker table tr td.today.disabled:hover,
.datepicker table tr td.today.disabled:hover:hover,
.datepicker table tr td.today:active,
.datepicker table tr td.today:hover:active,
.datepicker table tr td.today.disabled:active,
.datepicker table tr td.today.disabled:hover:active,
.datepicker table tr td.today.active,
.datepicker table tr td.today:hover.active,
.datepicker table tr td.today.disabled.active,
.datepicker table tr td.today.disabled:hover.active,
.datepicker table tr td.today.disabled,
.datepicker table tr td.today:hover.disabled,
.datepicker table tr td.today.disabled.disabled,
.datepicker table tr td.today.disabled:hover.disabled,
.datepicker table tr td.today[disabled],
.datepicker table tr td.today:hover[disabled],
.datepicker table tr td.today.disabled[disabled],
.datepicker table tr td.today.disabled:hover[disabled] {
background-color: #fdf59a;
}
.datepicker table tr td.today:active,
.datepicker table tr td.today:hover:active,
.datepicker table tr td.today.disabled:active,
.datepicker table tr td.today.disabled:hover:active,
.datepicker table tr td.today.active,
.datepicker table tr td.today:hover.active,
.datepicker table tr td.today.disabled.active,
.datepicker table tr td.today.disabled:hover.active {
background-color: #fbf069 \9;
}
.datepicker table tr td.today:hover:hover {
color: #000;
}
.datepicker table tr td.today.active:hover {
color: #fff;
}
.datepicker table tr td.range,
.datepicker table tr td.range:hover,
.datepicker table tr td.range.disabled,
.datepicker table tr td.range.disabled:hover {
background: #eeeeee;
-webkit-border-radius: 0;
-moz-border-radius: 0;
border-radius: 0;
}
.datepicker table tr td.range.today,
.datepicker table tr td.range.today:hover,
.datepicker table tr td.range.today.disabled,
.datepicker table tr td.range.today.disabled:hover {
background-color: #f3d17a;
background-image: -moz-linear-gradient(to bottom, #f3c17a, #f3e97a);
background-image: -ms-linear-gradient(to bottom, #f3c17a, #f3e97a);
background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#f3c17a), to(#f3e97a));
background-image: -webkit-linear-gradient(to bottom, #f3c17a, #f3e97a);
background-image: -o-linear-gradient(to bottom, #f3c17a, #f3e97a);
background-image: linear-gradient(to bottom, #f3c17a, #f3e97a);
background-repeat: repeat-x;
filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#f3c17a', endColorstr='#f3e97a', GradientType=0);
border-color: #f3e97a #f3e97a #edde34;
border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
filter: progid:DXImageTransform.Microsoft.gradient(enabled=false);
-webkit-border-radius: 0;
-moz-border-radius: 0;
border-radius: 0;
}
.datepicker table tr td.range.today:hover,
.datepicker table tr td.range.today:hover:hover,
.datepicker table tr td.range.today.disabled:hover,
.datepicker table tr td.range.today.disabled:hover:hover,
.datepicker table tr td.range.today:active,
.datepicker table tr td.range.today:hover:active,
.datepicker table tr td.range.today.disabled:active,
.datepicker table tr td.range.today.disabled:hover:active,
.datepicker table tr td.range.today.active,
.datepicker table tr td.range.today:hover.active,
.datepicker table tr td.range.today.disabled.active,
.datepicker table tr td.range.today.disabled:hover.active,
.datepicker table tr td.range.today.disabled,
.datepicker table tr td.range.today:hover.disabled,
.datepicker table tr td.range.today.disabled.disabled,
.datepicker table tr td.range.today.disabled:hover.disabled,
.datepicker table tr td.range.today[disabled],
.datepicker table tr td.range.today:hover[disabled],
.datepicker table tr td.range.today.disabled[disabled],
.datepicker table tr td.range.today.disabled:hover[disabled] {
background-color: #f3e97a;
}
.datepicker table tr td.range.today:active,
.datepicker table tr td.range.today:hover:active,
.datepicker table tr td.range.today.disabled:active,
.datepicker table tr td.range.today.disabled:hover:active,
.datepicker table tr td.range.today.active,
.datepicker table tr td.range.today:hover.active,
.datepicker table tr td.range.today.disabled.active,
.datepicker table tr td.range.today.disabled:hover.active {
background-color: #efe24b \9;
}
.datepicker table tr td.selected,
.datepicker table tr td.selected:hover,
.datepicker table tr td.selected.disabled,
.datepicker table tr td.selected.disabled:hover {
background-color: #9e9e9e;
background-image: -moz-linear-gradient(to bottom, #b3b3b3, #808080);
background-image: -ms-linear-gradient(to bottom, #b3b3b3, #808080);
background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#b3b3b3), to(#808080));
background-image: -webkit-linear-gradient(to bottom, #b3b3b3, #808080);
background-image: -o-linear-gradient(to bottom, #b3b3b3, #808080);
background-image: linear-gradient(to bottom, #b3b3b3, #808080);
background-repeat: repeat-x;
filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#b3b3b3', endColorstr='#808080', GradientType=0);
border-color: #808080 #808080 #595959;
border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
filter: progid:DXImageTransform.Microsoft.gradient(enabled=false);
color: #fff;
text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
}
.datepicker table tr td.selected:hover,
.datepicker table tr td.selected:hover:hover,
.datepicker table tr td.selected.disabled:hover,
.datepicker table tr td.selected.disabled:hover:hover,
.datepicker table tr td.selected:active,
.datepicker table tr td.selected:hover:active,
.datepicker table tr td.selected.disabled:active,
.datepicker table tr td.selected.disabled:hover:active,
.datepicker table tr td.selected.active,
.datepicker table tr td.selected:hover.active,
.datepicker table tr td.selected.disabled.active,
.datepicker table tr td.selected.disabled:hover.active,
.datepicker table tr td.selected.disabled,
.datepicker table tr td.selected:hover.disabled,
.datepicker table tr td.selected.disabled.disabled,
.datepicker table tr td.selected.disabled:hover.disabled,
.datepicker table tr td.selected[disabled],
.datepicker table tr td.selected:hover[disabled],
.datepicker table tr td.selected.disabled[disabled],
.datepicker table tr td.selected.disabled:hover[disabled] {
background-color: #808080;
}
.datepicker table tr td.selected:active,
.datepicker table tr td.selected:hover:active,
.datepicker table tr td.selected.disabled:active,
.datepicker table tr td.selected.disabled:hover:active,
.datepicker table tr td.selected.active,
.datepicker table tr td.selected:hover.active,
.datepicker table tr td.selected.disabled.active,
.datepicker table tr td.selected.disabled:hover.active {
background-color: #666666 \9;
}
.datepicker table tr td.active,
.datepicker table tr td.active:hover,
.datepicker table tr td.active.disabled,
.datepicker table tr td.active.disabled:hover {
background-color: #006dcc;
background-image: -moz-linear-gradient(to bottom, #0088cc, #0044cc);
background-image: -ms-linear-gradient(to bottom, #0088cc, #0044cc);
background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#0088cc), to(#0044cc));
background-image: -webkit-linear-gradient(to bottom, #0088cc, #0044cc);
background-image: -o-linear-gradient(to bottom, #0088cc, #0044cc);
background-image: linear-gradient(to bottom, #0088cc, #0044cc);
background-repeat: repeat-x;
filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#0088cc', endColorstr='#0044cc', GradientType=0);
border-color: #0044cc #0044cc #002a80;
border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
filter: progid:DXImageTransform.Microsoft.gradient(enabled=false);
color: #fff;
text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
}
.datepicker table tr td.active:hover,
.datepicker table tr td.active:hover:hover,
.datepicker table tr td.active.disabled:hover,
.datepicker table tr td.active.disabled:hover:hover,
.datepicker table tr td.active:active,
.datepicker table tr td.active:hover:active,
.datepicker table tr td.active.disabled:active,
.datepicker table tr td.active.disabled:hover:active,
.datepicker table tr td.active.active,
.datepicker table tr td.active:hover.active,
.datepicker table tr td.active.disabled.active,
.datepicker table tr td.active.disabled:hover.active,
.datepicker table tr td.active.disabled,
.datepicker table tr td.active:hover.disabled,
.datepicker table tr td.active.disabled.disabled,
.datepicker table tr td.active.disabled:hover.disabled,
.datepicker table tr td.active[disabled],
.datepicker table tr td.active:hover[disabled],
.datepicker table tr td.active.disabled[disabled],
.datepicker table tr td.active.disabled:hover[disabled] {
background-color: #0044cc;
}
.datepicker table tr td.active:active,
.datepicker table tr td.active:hover:active,
.datepicker table tr td.active.disabled:active,
.datepicker table tr td.active.disabled:hover:active,
.datepicker table tr td.active.active,
.datepicker table tr td.active:hover.active,
.datepicker table tr td.active.disabled.active,
.datepicker table tr td.active.disabled:hover.active {
background-color: #003399 \9;
}
.datepicker table tr td span {
display: block;
width: 23%;
height: 54px;
line-height: 54px;
float: left;
margin: 1%;
cursor: pointer;
-webkit-border-radius: 4px;
-moz-border-radius: 4px;
border-radius: 4px;
}
.datepicker table tr td span:hover {
background: #eeeeee;
}
.datepicker table tr td span.disabled,
.datepicker table tr td span.disabled:hover {
background: none;
color: #999999;
cursor: default;
}
.datepicker table tr td span.active,
.datepicker table tr td span.active:hover,
.datepicker table tr td span.active.disabled,
.datepicker table tr td span.active.disabled:hover {
background-color: #006dcc;
background-image: -moz-linear-gradient(to bottom, #0088cc, #0044cc);
background-image: -ms-linear-gradient(to bottom, #0088cc, #0044cc);
background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#0088cc), to(#0044cc));
background-image: -webkit-linear-gradient(to bottom, #0088cc, #0044cc);
background-image: -o-linear-gradient(to bottom, #0088cc, #0044cc);
background-image: linear-gradient(to bottom, #0088cc, #0044cc);
background-repeat: repeat-x;
filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#0088cc', endColorstr='#0044cc', GradientType=0);
border-color: #0044cc #0044cc #002a80;
border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
filter: progid:DXImageTransform.Microsoft.gradient(enabled=false);
color: #fff;
text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
}
.datepicker table tr td span.active:hover,
.datepicker table tr td span.active:hover:hover,
.datepicker table tr td span.active.disabled:hover,
.datepicker table tr td span.active.disabled:hover:hover,
.datepicker table tr td span.active:active,
.datepicker table tr td span.active:hover:active,
.datepicker table tr td span.active.disabled:active,
.datepicker table tr td span.active.disabled:hover:active,
.datepicker table tr td span.active.active,
.datepicker table tr td span.active:hover.active,
.datepicker table tr td span.active.disabled.active,
.datepicker table tr td span.active.disabled:hover.active,
.datepicker table tr td span.active.disabled,
.datepicker table tr td span.active:hover.disabled,
.datepicker table tr td span.active.disabled.disabled,
.datepicker table tr td span.active.disabled:hover.disabled,
.datepicker table tr td span.active[disabled],
.datepicker table tr td span.active:hover[disabled],
.datepicker table tr td span.active.disabled[disabled],
.datepicker table tr td span.active.disabled:hover[disabled] {
background-color: #0044cc;
}
.datepicker table tr td span.active:active,
.datepicker table tr td span.active:hover:active,
.datepicker table tr td span.active.disabled:active,
.datepicker table tr td span.active.disabled:hover:active,
.datepicker table tr td span.active.active,
.datepicker table tr td span.active:hover.active,
.datepicker table tr td span.active.disabled.active,
.datepicker table tr td span.active.disabled:hover.active {
background-color: #003399 \9;
}
.datepicker table tr td span.old,
.datepicker table tr td span.new {
color: #999999;
}
.datepicker .datepicker-switch {
width: 145px;
}
.datepicker .datepicker-switch,
.datepicker .prev,
.datepicker .next,
.datepicker tfoot tr th {
cursor: pointer;
}
.datepicker .datepicker-switch:hover,
.datepicker .prev:hover,
.datepicker .next:hover,
.datepicker tfoot tr th:hover {
background: #eeeeee;
}
.datepicker .cw {
font-size: 10px;
width: 12px;
padding: 0 2px 0 5px;
vertical-align: middle;
}
.input-append.date .add-on,
.input-prepend.date .add-on {
cursor: pointer;
}
.input-append.date .add-on i,
.input-prepend.date .add-on i {
margin-top: 3px;
}
.input-daterange input {
text-align: center;
}
.input-daterange input:first-child {
-webkit-border-radius: 3px 0 0 3px;
-moz-border-radius: 3px 0 0 3px;
border-radius: 3px 0 0 3px;
}
.input-daterange input:last-child {
-webkit-border-radius: 0 3px 3px 0;
-moz-border-radius: 0 3px 3px 0;
border-radius: 0 3px 3px 0;
}
.input-daterange .add-on {
display: inline-block;
width: auto;
min-width: 16px;
height: 18px;
padding: 4px 5px;
font-weight: normal;
line-height: 18px;
text-align: center;
text-shadow: 0 1px 0 #ffffff;
vertical-align: middle;
background-color: #eeeeee;
border: 1px solid #ccc;
margin-left: -5px;
margin-right: -5px;
}

ul.list.week-list {
  display: flex;
  align-items: center;
  justify-content: space-around;
  margin-bottom: 10px;
}
ul.list.week-list li {
  height: 50px;
  width: 50px;
  border-radius: 50px;
  line-height: 50px;
  border: 1px solid #ddd;
  text-align: center;
  cursor: pointer;
}
ul.list.week-list li.active,
ul.list.week-list li:hover{background-color: #ddd}
.datetime-datepicker.error {
  border: 1px solid red;
}

.alRecurringBookingSinglePageView .single_product-input input {
  width: 48%;
  display: inline-block;
  border: none;
  height: auto;
  padding: 30px 0px 10px 4px;
  font-size: 13px;
}
.alRecurringBookingSinglePageView .single_product-input {
  border: 1px solid#cfc9c9;
  width: 95%;
  border-radius: 5px;
  position: relative;
}
.alRecurringBookingSinglePageView .single_cart-temp_label{
width: 88%;
position: absolute;
z-index: 1;
}
.alRecurringBookingSinglePageView .single_cart-temp_label label{
  display: inline-block;
  width: 46%;
  font-size: 12px;
  padding: 6px 0px 0px 6px;
  color: #000;
}

.alRecurringBookingSinglePageView .single_product-input input:nth-child(1) {
border-right: 1px solid#cfc9c9;
  border: 1px solid#cfc9c9;
  border-top: none;
  border-bottom: none;
  border-left: none;
}
.disclaimer{
  font-style: italic;
}
.check_recurring  {
display: block;
position: relative;
padding-left: 22px;
margin-bottom: 12px;
cursor: pointer;
font-size: 12px;
-webkit-user-select: none;
-moz-user-select: none;
-ms-user-select: none;
user-select: none;
}

/* Hide the browser's default radio button */
.check_recurring  input {
position: absolute;
opacity: 0;
cursor: pointer;
}

/* Create a custom radio button */
.check_recurring  .checkmark {
  position: absolute;
  top: 0;
  left: 0;
  height: 20px;
  width: 20px;
  background-color: #eee;
  border-radius: 50%;
}

/* On mouse-over, add a grey background color */
.check_recurring :hover input ~ .checkmark {
background-color: #ccc;
}

/* When the radio button is checked, add a blue background */
.check_recurring  input:checked ~ .checkmark {
background-color: #2196F3;
}

/* Create the indicator (the dot/circle - hidden when not checked) */
.check_recurring :after {
content: "";
position: absolute;
display: none;
}

/* Show the indicator (dot/circle) when checked */
.check_recurring  input:checked ~ .checkmark:after {
display: block;
}

/* Style the indicator (dot/circle) */
.check_recurring  .checkmark:after {
  top: 7px;
  left: 8px;
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: white;
}
.check_recurring span {
  font-size: 16px;
  font-weight: 400;
}
div#date_recurring {
  width: 100%;
  margin-top: 18px;
}
div#custom_date_recurring {
  width: 100%;
  margin-top: 18px;
}
.recurring_booking_warpper{
  border: 1px solid #eee;
  padding: 10px;
  margin-left: 10px;
}
</style>

{{-- <link href="{{asset('assets/libs/flatpickr/flatpickr.min.js')}}" /> --}}
<div class="alRecurringBookingSinglePageView">
<div class="addManualTime">
  <div class="addManualTimeGroup" style="text-align:left;">
      <div class="row mb-3 recurring_booking_warpper">
          <div class="col-md-4 once mt-1">
              <label class="check_recurring m-0">
                  <span>{{__('Once')}}</span>
                  <input type="radio" name="booking_type" checked value="5">
                  <span class="checkmark"></span>
              </label>
          </div>
          <div class="col-md-4 mt-1">
              <label class="check_recurring m-0">
                  <span>{{__('Daily')}}</span>
                  <input type="radio" name="booking_type" value="1">
                  <span class="checkmark"></span>
              </label>
          </div>
          <div class="col-md-4 mt-1">
              <label class="check_recurring m-0">
                  <span>{{__('Weekly')}}</span>
                  <input type="radio" name="booking_type" value="2">
                  <span class="checkmark"></span>
              </label>
          </div>
          <div class="col-md-4 mt-1">
              <label class="check_recurring m-0">
                  <span>{{__('Monthly')}}</span>
                  <input type="radio" name="booking_type" value="3">
                  <span class="checkmark"></span>
              </label>
          </div>
          <div class="col-md-4 mt-1">
              <label class="check_recurring m-0">
                  <span>{{__('Custom')}}</span>
                  <input type="radio" name="booking_type" value="4">
                  <span class="checkmark"></span>
              </label>
          </div>
      </div>
      <div id="daily_booking" class="d-none">
          <div class="col-md-12">
              <div class="row">
                
                <div class="col-12" id="weeks">
                    <div class="weeknames">
                        @php  $weekDay = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa']; @endphp
                        <ul class="list week-list">
                            @for ($i = 0; $i <count($weekDay); $i++)
                                <li data-id="{{$i}}"> {{ $weekDay[$i]}}</li>
                            @endfor
                        </ul>
                    </div>
                </div>

                  <div class="col-md-7">
                      <div class="form-group" id="daily_timeInput">
                          {!! Form::text('daily_date_time','', ['class' => 'form-control downside datetime-datepicker','id' => 'daily-datepicker','readonly'=>'true','placeholder'=>'Select day']) !!}
                      </div>
                  </div>

                  <div class="col-md-7" id="months">
                    <div class="form-group" >
                        <select name="months" id="monthValue" class="form-control">
                          <option value="">Select Month</option>
                            @for ($i = 1; $i <=12; $i++)
                                <option value="{{$i}}">{{$i}}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                  <div class="col-md-5">
                      <div class="booking-time-section w-100">
                          <input class="time booking-time form-control" type="time" name ="daily_booking_time" placeholder="Select time" id="daily_booking_time"  />
                      </div>
                  </div>
              </div>
          </div>
      </div>
      <input type="hidden" id="is_recurring_booking" value="1">
  </div>
</div>
</div>

@section('script-bottom-js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/js/bootstrap-datepicker.js" ></script>
<script type="text/javascript" src="{{asset('assets/libs/flatpickr/flatpickr.min.js')}}"></script>
<script type="text/javascript">
 

  var start_date=end_date=daily_booking_time=type = monthNumber = '';
  actual_price            = '{{@$product->variant[0]->actual_price}}';
  default_currency        = "{{Session::get('currencySymbol')}}";
  var currentDate         = moment().format("M/DD/YY");
  $checkinInput           = $('#blocktime');
  $checkoutInput          = $('#blocktime2');
  var selectedweek = weeks = selected_custom_dates=[];
  var recurringformPost = {};
  var type = 1;
  //var weeks = [];
  //var formData = {};
  

  $(function(e) {
        $(document).on("click", ".check_recurring input", async function () {
        $("#daily-datepicker").val('');
        type = $(this).val();
        var picker = 1;

        $("#daily_booking").removeClass('d-none'); 
        $("#daily_timeInput").removeClass('d-none'); 
        $(".week-list li").removeClass('active');
        $("#weeks").addClass('d-none');
        $("#months").addClass('d-none');
        if(type == 5){
            $("#daily_booking").addClass('d-none');
        }else if(type == 2){
            $("#weeks").removeClass('d-none');
        }else if(type == 3){
            $("#months").removeClass('d-none');
            $("#daily_timeInput").addClass('d-none');
        }else if(type == 4){
            picker = 0;
            $("#daily_booking").removeClass('d-none');
            $("#daily_timeInput").removeClass('d-none');
        }
        rangePicker(picker);     

        });
        $('#daily_booking_time').on('change',function(){
            recurringformPost.schedule_time  = this.value
            daily_booking_time  = this.value
        });

        $('#monthValue').on('change',function(){
            recurringformPost.month_number  = this.value
            monthNumber  = this.value;
            var booking_type = $("input[type='radio'][name='booking_type']:checked").val();
            
            recurringformPost =  {
            selectedCustomdates:selected_custom_dates,
            startDate:  start_date,
            endDate  :  end_date,
            action   :  booking_type ,
            schedule_time : daily_booking_time,
            month_number : monthNumber,
            weekDay:weeks
        };
        
        });
    });
    
    $(document).delegate( ".week-list li", "click", function() {
            $(this).toggleClass('active');
            var weekDay = $(this).attr('data-id');
            pushsliceWeeks(weeks,weekDay);
        });

    function rangePicker(action=1){
        if($('#daily-datepicker').data('daterangepicker')){
          $('#daily-datepicker').data('daterangepicker').remove();
        }
        if($("#daily-datepicker").data("datepicker") != null){
          $("#daily-datepicker").data("datepicker").remove();
        }
        

        if(action ==1){
            $('#daily-datepicker').daterangepicker({
                locale: {
                      format: 'YYYY-MM-DD'
                },
                opens: 'left',
                startDate: moment(),
                endDate: moment(),
                minDate:new Date(),
                multidate: true,
            }, function(start, end, label) {
                //console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
                var date = start.format('YYYY-MM-DD')+','+ end.format('YYYY-MM-DD');
                var start_date  = start.format('YYYY-MM-DD');
                var end_date    = end.format('YYYY-MM-DD');
                formData(1,start_date,end_date);
            });
        } else {
          $('#daily-datepicker').datepicker({
              multidate: true,
              format: 'dd-mm-yyyy',
              startDate:new Date()
          }).on('changeDate', function(e) {   
              formData(0,e.format());
          });  
        }


    }

    function diff_minutes(dt2, dt1) {
      var start_date    = new Date(dt2);
      var end_date      = new Date(dt2);
      return Math.abs(new Date(dt2) - new Date(dt1))/60000;
    }
    function checkIfInCart(v_p) {
      localStorage.setItem('in_cart','false');
      if(v_p.variant_id == $('#prod_variant_id').val()){
          localStorage.setItem('in_cart','true');
      }
    }

  

    function formData(action='',start_date='',end_date=''){
        var booking_type = $("input[type='radio'][name='booking_type']:checked").val();
        
        if(action!=1){
          pushsliceSelected_custom_dates(selected_custom_dates,start_date)
        } 
        recurringformPost =  {
            selectedCustomdates:selected_custom_dates,
            startDate:  start_date,
            endDate  :  end_date,
            action   :  booking_type ?? '1',
            schedule_time : daily_booking_time,
            month_number : monthNumber,
            weekDay:weeks
        };
    }

    function pushsliceWeeks(array = '',item) {  
          selected_custom_dates = [];  
        // Removing the specified element by value from the array
        if(array.includes(item)){
          
          const index = array.indexOf(item);
          if (index > -1) { // only splice array when item is found
            array.splice(index, 1); // 2nd parameter means remove one item only
          }
        } else {
          if(item!=''){
            array.push(item);
          }
        }
    }

    function pushsliceSelected_custom_dates(array,item) {  
          weeks = [];   
        // Removing the specified element by value from the array
        if(array.includes(item)){
          
          const index = array.indexOf(item);
          if (index > -1) { // only splice array when item is found
            array.splice(index, 1); // 2nd parameter means remove one item only
          }
        } else {
          if(item!=''){
            array.push(item);
          }
        }
    }
    
</script>
@endsection
