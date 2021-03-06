<div id="insert_food_data" style="background-color: whitesmoke; width: 80%;  left: 10%; top:10%; position: absolute; display: none;">
    <table class="table table-hover" style="width: 80%;  margin: 0px auto; ">
        <tr>
            <td>进食日期：</td><td id="food_calendar_date"></td>
        </tr>
        <tr class="form-inline">
            <td>进食时段：</td><td class="form-line"><span id="food_range"></span> <input type="text" id="food_timepicker" class="timepicker form-control"/></td>
        </tr>
        <tr>
            <td style="vertical-align: middle" id="food_type" rowspan="1">正 餐：</td>
            <td>
                <table id="sample" class="table borderless" style="background-color: transparent; margin-bottom: 0px">
                    <tr >
                        <td class="form-line ">
                            <button id="add_food" class="form-control">新增饮食</button>
                            <button id="delete_food" class="delete form-control" style="display: none;">删除</button>
                        </td>
                        <td class="form-inline">
                            <select id="food_category" class="form-control" style="width: 150px">
                                <option value="0">无</option>
                                @foreach ($data['food_categories'] as $food_category)
                                    <option value="{{$food_category->food_category_pk}}">{{$food_category->food_category_name}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="form-inline">
                            <select id="food_type_option" id="$food" class="form-control" style="width: 150px">
                                <option value="0">无</option>
                            </select>
                        </td>
                        <td class="form-inline">
                            <input id="amount" type="text" class="timepicker form-control"/>
                            <select id="food_unit" class="form-control" style="width: 150px">
                                <option value="gram">公克</option>
                                <option value="set">份</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                醣 类:
            </td>
            <td class="form-inline">
                <input id="sugar_amount" type="text" class=" form-control" value="0"/>   公克
            </td>
        </tr>
        <tr>
            <td style="vertical-align: middle">饮食备注：</td><td class="form-inline"><textarea id="food_note" class="form-control area"></textarea></td>
        </tr>
        <tr>
            <td style="vertical-align: middle">我的备注：</td><td class="form-inline"><textarea id="overall_note" class="form-control area"></textarea></td>
        </tr>
        <tr>
            <td colspan="2">
                {!! Form::open(array('url'=>'upsertfood','method'=>'POST', 'id'=>'myform')) !!}
                {!! Form::button('存 档', array('class'=>'btn btn-default', 'id'=>'food_save')) !!}
                <button class="btn btn-default" id="food_cancel">取 消</button>
                {!! Form::button('删除饮食资料', array('class'=>'btn btn-default', 'id'=>'delete_food_all')) !!}
                {!! Form::close() !!}
            </td>
        </tr>
    </table>
</div>