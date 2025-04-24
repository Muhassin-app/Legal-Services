@php
$timezone = @$user->timezone;
@endphp

<tr>
   <td colspan="2" style="text-align: center;">
       <h2 style="color: #000000;font-size: 15px;font-weight: 500;letter-spacing: 0;line-height: 19px;">{{__('ORDER NO')}}. {{@$order->order_number}} (<span style="color:{{!empty(getClientPreferenceDetail()) ? getClientPreferenceDetail()->primary_color : '#000'}};"></span>)</h2>
       <p style="opacity: 0.41;color: #000000;font-size: 12px;letter-spacing: 0;line-height: 15px;">{{ dateTimeInUserTimeZone(@$order->created_at, @$timezone) }}</p>
   </td>
</tr>
@php $i=0; @endphp
@foreach($locations as $location)
   @php
      $status = ($i == 0) ? 'From' : 'To';
   @endphp
<tr>
   <td colspan="2" style="background-color: #d8d8d85e;">
      <table style="width: 100%;">
         <thead>
            <tr>
               <th style="text-align: right;padding-right: 0;padding-left: 0;color: #000000;font-size: 13px;letter-spacing: 0;line-height: 18px;font-weight: 400;">
                    {{$status}} - {{$location->pre_address}}
               </th>
            </tr>
         </thead>
      </table>
   </td>
</tr>
@endforeach

<tr>
   <td colspan="2" style="padding: 5px 15px;">
      <span style="border-bottom: 1px solid rgb(151 151 151 / 23%);display: block;"></span>
   </td>
</tr>

