<div id="myModal{{ $user->id }}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h5 class="modal-title" id="myModalLabel">{{-- $user->name --}}更新</h5>
            </div>
            {!! Form::model($user, ['url' => '/admin/upload_user', 'class' => 'form-horizontal']) !!}

            <div class="modal-body">
                  {!! Form::hidden('user_id', $user->id) !!}
                <h6>
                  {!! Form::label('id', '#: ', ['class' => 'control-label']) !!}
                  {!! Form::text('id', null, ['class' => 'form-control', 'readonly']) !!}
                  {!! Form::label('account', '帐号: ', ['class' => 'control-label']) !!}
                  {!! Form::text('account', null, ['class' => 'form-control', 'readonly']) !!}
                  {!! Form::label('password', '密码: ', ['class' => 'control-label']) !!}
                  {!! Form::text('password', '', ['class' => 'form-control', 'required', 'placeholder' => '请输入密码']) !!}
                  {!! Form::label('pid', '身份证号: ', ['class' => 'control-label']) !!}
                  {!! Form::text('pid', null, ['class' => 'form-control', 'readonly']) !!}
                  {!! Form::label('name', '姓名: ', ['class' => 'control-label']) !!}
                  {!! Form::text('name', null, ['class' => 'form-control', 'required']) !!}
                  {!! Form::label('department', '部门名称: ', ['class' => 'control-label']) !!}
                  {!! Form::text('department', null, ['class' => 'form-control']) !!}
                  {!! Form::label('position', '职务: ', ['class' => 'control-label']) !!}
                  {!! Form::select('position', $positions , $user->position, ['class' => 'form-control']) !!}
                  {!! Form::label('phone', '手机号: ', ['class' => 'control-label']) !!}
                  {!! Form::text('phone', null, ['class' => 'form-control']) !!}
                  {!! Form::label('email', '邮箱: ', ['class' => 'control-label']) !!}
                  {!! Form::email('email', null, ['class' => 'form-control']) !!}
                  {{-- !! Form::label('departmentno', '部门编号: ', ['class' => 'control-label']) !! --}}
                  {{-- !! Form::text('departmentno', null, ['class' => 'form-control']) !! --}}
                  {{-- !! Form::label('positionno', '职务代码: ', ['class' => 'control-label']) !! --}}
                  {{-- !! Form::text('positionno', null, ['class' => 'form-control']) !! --}}
                </h6>
            </div>

            <div class="modal-footer">
                {!! Form::button('关闭', ['class' => 'btn btn-default', 'data-dismiss' => 'modal']) !!}
                {!! Form::submit('提交', ['class' => 'btn btn-success']) !!}
            </div>
           {!! Form::close() !!}
        </div>
    </div>
</div>
