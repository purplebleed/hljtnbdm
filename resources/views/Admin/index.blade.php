@extends('master')

@section('title')
    管理员
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-11">
                @include('errors.list')
                <h3 align="center">人员信息表<span class="badge">{{ $count }}</span></h3>
                <a href="/admin/create"><button class="btn btn-primary">添加人员</button></a>
                {!! Form::open(array('route'=>'admin.index', 'method'=>'get', 'class'=>'form navbar-form navbar-right searchform')) !!}
                    {!! Form::select('category', $categories, $category, ['required', 'class' => 'form-control']) !!}
                    {!! Form::text('search', $search, array('required', 'class'=>'form-control', 'placeholder'=>'按栏位搜索...')) !!}
                    {!! Form::submit('搜寻', array('class'=>'btn btn-default')) !!}
                    <!-- a class="btn btn-info" href="{{-- route('admin.forget', 1) --}}">清除</a -->
                {!! Form::close() !!}
                <table class="table table-striped table-hover" id="sortadTable">
                    <thead>
                    <tr style="background: silver;">
                        <th>#<a href="javascript:void(0)"><span class="glyphicon glyphicon-sort" aria-hidden="true"></span></a></th>
                        <th>帐号<a href="javascript:void(0)"><span class="glyphicon glyphicon-sort" aria-hidden="true"></span></a></th>
                        <th>姓名<a href="javascript:void(0)"><span class="glyphicon glyphicon-sort" aria-hidden="true"></span></a></th>
                        <th>医院</th>
                        <th>部门<a href="javascript:void(0)"><span class="glyphicon glyphicon-sort" aria-hidden="true"></span></a></th>
                        <th>手机<a href="javascript:void(0)"><span class="glyphicon glyphicon-sort" aria-hidden="true"></span></a></th>
                        <th>邮箱<a href="javascript:void(0)"><span class="glyphicon glyphicon-sort" aria-hidden="true"></span></a></th>
                        <th class="text-center">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $hospitals = ['hljtnb' => '黑龙江', 'tytnb' => '太原', 'tnbrj' => '北京', 'cdtnb' => '成都', 'xbtnb' => '兰州']; ?>
                    @if (count($users))
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->account }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ in_array($user->hospital, array_keys($hospitals)) ? $hospitals[$user->hospital] : "" }}</td>
                                <td>{{ $user->department }}</td>
                                <td>{{ $user->phone }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#myModal{{$user->id}}">更新</button>
                                    <form action="{{ url('admin/'.$user->id) }}" style='display: inline' method="post">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('确定删除?')">删除</button>
                                    </form>
                                </td>
                            </tr>
                            @include('Admin.upload_user')
                        @endforeach
                    @else
                        <h1>没有人员名单,请管理员添加...</h1>
                    @endif
                    </tbody>
                </table>
                <?php echo $users->render(); ?>
            </div>
            @include('Admin.right_bar')
        </div>
    </div>
@stop

@section('loadScripts')
    <script>
        $(document).ready(function(){
            $("#sortadTable").tablesorter();
        });
    </script>
@stop
