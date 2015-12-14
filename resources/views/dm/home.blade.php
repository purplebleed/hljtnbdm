@extends('master')

@section('title')
    {{ $doctor ? "建案" : "工作" }}清单
@stop

@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            @include('errors.list')
            <h3 align="center">{{ $doctor ? "建案" : "工作" }}清单 <span class="badge">{{ $count }}</span></h3>
            <a class="btn btn-success" href="{{ route('dm_create') }}" {!! $doctor ? '' : 'style="display:none"' !!}>增</a>
            <form method="GET" action="/dm/home" accept-charset="UTF-8" class="form navbar-form navbar-right searchform">
                <select class="form-control" name="category" required>
                    <option value="" {{ Text::selected($category, '') }}>请选择</option>
                    <option value="1" {{ Text::selected($category, 1) }}>身份证</option>
                    <option value="2" {{ Text::selected($category, 2) }}>卡号</option>
                    <option value="3" {{ Text::selected($category, 3) }}>建案人</option>
                    <option value="4" {{ Text::selected($category, 4) }}>责任卫教</option>
                    <option value="5" {{ Text::selected($category, 5) }}>护理卫教</option>
                    <option value="6" {{ Text::selected($category, 6) }}>营养卫教</option>
                </select>
                <input class="form-control" placeholder="按栏位搜索..." name="search" type="text" value="{{ $search }}" required>
                <input class="btn btn-default" type="submit" value="搜寻">
            </form>
            <table class="table table-hover table-condensed table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>身份证</th>
                    <th>卡号</th>
                    <th>建案日</th>
                    <th>建案人</th>
                    <th>责任卫教</th>
                    <th>进度</th>
                    <th>护理卫教</th>
                    <th>进度</th>
                    <th>营养卫教</th>
                    <th>进度</th>
                    <th class="text-center" {!! $doctor ? '' : 'style="display:none"' !!}>功能</th>
                </tr>
                </thead>

                <tbody>
                @if(count($buildcases))
                    @foreach($buildcases as $buildcase)
                        <tr>
                            <td>{{ $buildcase->id }}</td>
                            @if($buildcase->doctor == $users->id || $buildcase->duty == $users->id || $buildcase->nurse == $users->id || $buildcase->dietitian == $users->id)
                                <td>
<a data-html="true" href="#" data-toggle="popover" title="新增资料选项" data-content=
"&lt;a href='/patient/ccreate/{{ $buildcase->personid }}' class='btn btn-info' role='button'&gt;患者&lt;/a&gt;
&lt;a href='/dm/gobd/{{ $buildcase->personid }}/{{ $buildcase->id }}' class='btn btn-danger' role='button'&gt;血糖&lt;/a&gt;
&lt;a href='/dm/gosoap/{{ $buildcase->personid }}/{{ $buildcase->id }}' class='btn btn-success' role='button'&gt;SOAP&lt;/a&gt;
&lt;a href='/case/create/{{ $buildcase->personid }}' class='btn btn-warning' role='button'&gt;方案&lt;/a&gt;">{{ $buildcase->personid }}</a>
                                </td>
                            @else
                                <td>{{ $buildcase->personid }}</td>
                            @endif
                            <td>{{ $buildcase->cardid }}</td>
                            <td>{{ $buildcase->build_at }}</td>
                            <td>{{ $buildcase->doctor_name ? $buildcase->doctor_name : "" }}</td>
                            <td>{{ $buildcase->duty ? \App\User::find($buildcase->duty)->name : "" }}</td>
                            <td>
                                @if($buildcase->duty)
                                    {{ $buildcase->duty_status == 0 ? '未处理' : ($buildcase->duty_status == 1 ? '处理中' : '已完成') }}
                                @else
                                    &nbsp;
                                @endif
                            </td>
                            <td>{{ $buildcase->nurse ? \App\User::find($buildcase->nurse)->name : "" }}</td>
                            <td>
                                @if($buildcase->nurse)
                                    {{ $buildcase->nurse_status == 0 ? '未处理' : ($buildcase->duty_status == 1 ? '处理中' : '已完成') }}
                                @else
                                    &nbsp;
                                @endif
                            </td>
                            <td>{{ $buildcase->dietitian ? \App\User::find($buildcase->dietitian)->name : "" }}</td>
                            <td>
                                @if($buildcase->dietitian)
                                    {{ $buildcase->dietitian_status == 0 ? '未处理' : ($buildcase->duty_status == 1 ? '处理中' : '已完成') }}
                                @else
                                    &nbsp;
                                @endif
                            </td>
                            <td {!! $doctor ? '' : 'style="display:none"' !!}>
                                <!-- a class="btn btn-primary" href="{{-- route('dm_show', $buildcase->id) --}}">查</a -->
                                <a class="btn btn-warning" href="{{ route('dm_eedit', $buildcase->id) }}">改</a>
                                <form action="{{ route('dm_destroy', $buildcase->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('确定删除?')">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <button class="btn btn-danger" type="submit">删</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <h1>没有资料...</h1>
                @endif
                </tbody>
            </table>
            <?php echo $buildcases->render(); ?>
        </div>
        {{-- @include('dm.right_bar') --}}
    </div>
</div>

@stop

@section('loadScripts')
<script>
$(document).ready(function(){
    $('[data-toggle="popover"]').popover({
        html: true,
    });
});
</script>
@stop
