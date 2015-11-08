<h3>血糖統計</h3>
<span> {{$blood_records['start']}} ~ {{$blood_records['end']}} </span>
<br/>
<br/>
<table class="table table-hover statics">
    <tr>
        <th style="vertical-align: middle; text-align: center;" rowspan="2">統計資料</th>
        <th style="vertical-align: middle; text-align: center;" rowspan="2">凌晨</th>
        <th style="vertical-align: middle; text-align: center;" rowspan="2">晨起</th>
        <th style="vertical-align: middle; text-align: center;" colspan="2">早餐</th>
        <th style="vertical-align: middle; text-align: center;" colspan="2">午餐</th>
        <th style="vertical-align: middle; text-align: center;" colspan="2">晚餐</th>
        <th style="vertical-align: middle; text-align: center;" rowspan="2">睡前</td>
    </tr>
    <tr>
        <th style="vertical-align: middle; text-align: center;">飯前</th>
        <th style="vertical-align: middle; text-align: center;">飯後</th>
        <th style="vertical-align: middle; text-align: center;">飯前</th>
        <th style="vertical-align: middle; text-align: center;">飯後</th>
        <th style="vertical-align: middle; text-align: center;">飯前</th>
        <th style="vertical-align: middle; text-align: center;">飯後</th>
    </tr>
    <tr>
        <td>資料筆數</td>
        <td>@if(isset($blood_records['early_morning']['count'])){{$blood_records['early_morning']['count']}}@endif</td>
        <td>@if(isset($blood_records['morning']['count'])){{$blood_records['morning']['count']}}@endif</td>
        <td>@if(isset($blood_records['breakfast_before']['count'])){{$blood_records['breakfast_before']['count']}}@endif</td>
        <td>@if(isset($blood_records['breakfast_after']['count'])){{$blood_records['breakfast_after']['count']}}@endif</td>
        <td>@if(isset($blood_records['lunch_before']['count'])){{$blood_records['lunch_before']['count']}}@endif</td>
        <td>@if(isset($blood_records['lunch_after']['count'])){{$blood_records['lunch_after']['count']}}@endif</td>
        <td>@if(isset($blood_records['dinner_before']['count'])){{$blood_records['dinner_before']['count']}}@endif</td>
        <td>@if(isset($blood_records['dinner_after']['count'])){{$blood_records['dinner_after']['count']}}@endif</td>
        <td>@if(isset($blood_records['sleep_before']['count'])){{$blood_records['sleep_before']['count']}}@endif</td>
    </tr>
    <tr>
        <td>平均</td>
        <td>@if(isset($blood_records['early_morning']['average'])){{$blood_records['early_morning']['average']}}@endif</td>
        <td>@if(isset($blood_records['morning']['average'])){{$blood_records['morning']['average']}}@endif</td>
        <td>@if(isset($blood_records['breakfast_before']['average'])){{$blood_records['breakfast_before']['average']}}@endif</td>
        <td>@if(isset($blood_records['breakfast_after']['average'])){{$blood_records['breakfast_after']['average']}}@endif</td>
        <td>@if(isset($blood_records['lunch_before']['average'])){{$blood_records['lunch_before']['average']}}@endif</td>
        <td>@if(isset($blood_records['lunch_after']['average'])){{$blood_records['lunch_after']['average']}}@endif</td>
        <td>@if(isset($blood_records['dinner_before']['average'])){{$blood_records['dinner_before']['average']}}@endif</td>
        <td>@if(isset($blood_records['dinner_after']['average'])){{$blood_records['dinner_after']['average']}}@endif</td>
        <td>@if(isset($blood_records['sleep_before']['average'])){{$blood_records['sleep_before']['average']}}@endif</td>
    </tr>
    <tr>
        <td>最大值</td>
        <td>@if(isset($blood_records['early_morning']['max'])){{$blood_records['early_morning']['max']}}@endif</td>
        <td>@if(isset($blood_records['morning']['max'])){{$blood_records['morning']['max']}}@endif</td>
        <td>@if(isset($blood_records['breakfast_before']['max'])){{$blood_records['breakfast_before']['max']}}@endif</td>
        <td>@if(isset($blood_records['breakfast_after']['max'])){{$blood_records['breakfast_after']['max']}}@endif</td>
        <td>@if(isset($blood_records['lunch_before']['max'])){{$blood_records['lunch_before']['max']}}@endif</td>
        <td>@if(isset($blood_records['lunch_after']['max'])){{$blood_records['lunch_after']['max']}}@endif</td>
        <td>@if(isset($blood_records['dinner_before']['max'])){{$blood_records['dinner_before']['max']}}@endif</td>
        <td>@if(isset($blood_records['dinner_after']['max'])){{$blood_records['dinner_after']['max']}}@endif</td>
        <td>@if(isset($blood_records['sleep_before']['max'])){{$blood_records['sleep_before']['max']}}@endif</td>
    </tr>
    <tr>
        <td>最小值</td>
        <td>@if(isset($blood_records['early_morning']['min'])){{$blood_records['early_morning']['min']}}@endif</td>
        <td>@if(isset($blood_records['morning']['min'])){{$blood_records['morning']['min']}}@endif</td>
        <td>@if(isset($blood_records['breakfast_before']['min'])){{$blood_records['breakfast_before']['min']}}@endif</td>
        <td>@if(isset($blood_records['breakfast_after']['min'])){{$blood_records['breakfast_after']['min']}}@endif</td>
        <td>@if(isset($blood_records['lunch_before']['min'])){{$blood_records['lunch_before']['min']}}@endif</td>
        <td>@if(isset($blood_records['lunch_after']['min'])){{$blood_records['lunch_after']['min']}}@endif</td>
        <td>@if(isset($blood_records['dinner_before']['min'])){{$blood_records['dinner_before']['min']}}@endif</td>
        <td>@if(isset($blood_records['dinner_after']['min'])){{$blood_records['dinner_after']['min']}}@endif</td>
        <td>@if(isset($blood_records['sleep_before']['min'])){{$blood_records['sleep_before']['min']}}@endif</td>
    </tr>
    <tr>
        <td>高於目標值</td>
        <td>@if(isset($blood_records['early_morning']['above'])){{$blood_records['early_morning']['above']}}@endif</td>
        <td>@if(isset($blood_records['morning']['above'])){{$blood_records['morning']['above']}}@endif</td>
        <td>@if(isset($blood_records['breakfast_before']['above'])){{$blood_records['breakfast_before']['above']}}@endif</td>
        <td>@if(isset($blood_records['breakfast_after']['above'])){{$blood_records['breakfast_after']['above']}}@endif</td>
        <td>@if(isset($blood_records['lunch_before']['above'])){{$blood_records['lunch_before']['above']}}@endif</td>
        <td>@if(isset($blood_records['lunch_after']['above'])){{$blood_records['lunch_after']['above']}}@endif</td>
        <td>@if(isset($blood_records['dinner_before']['above'])){{$blood_records['dinner_before']['above']}}@endif</td>
        <td>@if(isset($blood_records['dinner_after']['above'])){{$blood_records['dinner_after']['above']}}@endif</td>
        <td>@if(isset($blood_records['sleep_before']['above'])){{$blood_records['sleep_before']['above']}}@endif</td>
    </tr>
    <tr>
        <td>正常值</td>
        <td>@if(isset($blood_records['early_morning']['normal'])){{$blood_records['early_morning']['normal']}}@endif</td>
        <td>@if(isset($blood_records['morning']['normal'])){{$blood_records['morning']['normal']}}@endif</td>
        <td>@if(isset($blood_records['breakfast_before']['normal'])){{$blood_records['breakfast_before']['normal']}}@endif</td>
        <td>@if(isset($blood_records['breakfast_after']['normal'])){{$blood_records['breakfast_after']['normal']}}@endif</td>
        <td>@if(isset($blood_records['lunch_before']['normal'])){{$blood_records['lunch_before']['normal']}}@endif</td>
        <td>@if(isset($blood_records['lunch_after']['normal'])){{$blood_records['lunch_after']['normal']}}@endif</td>
        <td>@if(isset($blood_records['dinner_before']['normal'])){{$blood_records['dinner_before']['normal']}}@endif</td>
        <td>@if(isset($blood_records['dinner_after']['normal'])){{$blood_records['dinner_after']['normal']}}@endif</td>
        <td>@if(isset($blood_records['sleep_before']['normal'])){{$blood_records['sleep_before']['normal']}}@endif</td>
    </tr>
    <tr>
        <td>低於目標值</td>
        <td>@if(isset($blood_records['early_morning']['below'])){{$blood_records['early_morning']['below']}}@endif</td>
        <td>@if(isset($blood_records['morning']['below'])){{$blood_records['morning']['below']}}@endif</td>
        <td>@if(isset($blood_records['breakfast_before']['below'])){{$blood_records['breakfast_before']['below']}}@endif</td>
        <td>@if(isset($blood_records['breakfast_after']['below'])){{$blood_records['breakfast_after']['below']}}@endif</td>
        <td>@if(isset($blood_records['lunch_before']['below'])){{$blood_records['lunch_before']['below']}}@endif</td>
        <td>@if(isset($blood_records['lunch_after']['below'])){{$blood_records['lunch_after']['below']}}@endif</td>
        <td>@if(isset($blood_records['dinner_before']['below'])){{$blood_records['dinner_before']['below']}}@endif</td>
        <td>@if(isset($blood_records['dinner_after']['below'])){{$blood_records['dinner_after']['below']}}@endif</td>
        <td>@if(isset($blood_records['sleep_before']['below'])){{$blood_records['sleep_before']['below']}}@endif</td>
    </tr>
    <tr>
        <td>PC-AC<30</td>
        <td>@if(isset($blood_records['early_morning']['below'])){{$blood_records['early_morning']['below']}}@endif</td>
        <td>@if(isset($blood_records['morning']['below'])){{$blood_records['morning']['below']}}@endif</td>
        <td>@if(isset($blood_records['breakfast_before']['below'])){{$blood_records['breakfast_before']['below']}}@endif</td>
        <td>@if(isset($blood_records['breakfast_after']['below'])){{$blood_records['breakfast_after']['below']}}@endif</td>
        <td>@if(isset($blood_records['lunch_before']['below'])){{$blood_records['lunch_before']['below']}}@endif</td>
        <td>@if(isset($blood_records['lunch_after']['below'])){{$blood_records['lunch_after']['below']}}@endif</td>
        <td>@if(isset($blood_records['dinner_before']['below'])){{$blood_records['dinner_before']['below']}}@endif</td>
        <td>@if(isset($blood_records['dinner_after']['below'])){{$blood_records['dinner_after']['below']}}@endif</td>
        <td>@if(isset($blood_records['sleep_before']['below'])){{$blood_records['sleep_before']['below']}}@endif</td>
    </tr>
</table>

<br/>
<br/>
@include('bdata.food_statics')