$( document ).ready(function() {
    //set up main class
    $("#main_class").find("button").click(function(event){
        event.preventDefault();
        $("#main_class").find(".btn-primary").removeClass('btn-primary');
        $(this).addClass('btn-primary');
        $(this).blur();
        $.ajax({
            type: 'GET',
            url: '/soap/get_sub/' + $(this).attr('data'),
            success: function(result){
                if(result){
                    var html = "";
                    for(var i = 0; i< result.length; i++){
                        html += '<option value="' + result[i].sub_class_pk + '">' + result[i].class_name + '</option>';
                    }

                    $("#sub_class").html(html);
                    if(result.length > 0){
                        setSOAClass(result[0].sub_class_pk);
                    }
                }
            }
        });
    });

    $("#sub_class").change(function(){
        setSOAClass( $(this).val());
    });

    $("#s").change(function(){
        appendText( $(this).find('option:selected').text(), $("#s_text"));
    });

    $("#o").click(function(){
        appendText( $(this).find('option:selected').text(), $("#o_text"));
    });

    $("#a").change(function(){
        setSOADetail( $(this).val() );
        appendText( $(this).find('option:selected').text(), $("#a_text"));
    });

    $("#p").change(function(){
        appendText( $(this).find('option:selected').text(), $("#p_text"));
    });

    $("#e").change(function(){
        appendText( $(this).find('option:selected').text(), $("#e_text"));
    });

    $("#customize_type").change(function(){
        setCustomize($("#customize_type").val());
        $("#customize_text").val("");
    });

    $("#customize_select").change(function(){
        var select_id = "#" + $("#customize_type").val().toLowerCase() + "_text";
        appendText( $(this).find('option:selected').text(), $(select_id));
    });

    $("#customize_btn").click(function(e){
        e.preventDefault();
        var inputdata = {};
        inputdata['types'] = $("#customize_type").val();
        inputdata['text'] = $("#customize_text").val();
        inputdata['_token'] = $('#customize > input[ name=_token]').val();
        $.ajax({
            type: 'POST',
            url: '/soap/get_customize',
            data: inputdata,
            success: function(result){
                if(result == 'success'){
                    setCustomize($("#customize_type").val());
                    $("#customize_text").val("");
                    $("#customize_btn").blur();
                }
            }
        });
    });

    $("#soap_save_btn").click(function(e){
        e.preventDefault();
        var inputdata = {};
        inputdata['s_text'] = $("#s_text").val();
        inputdata['o_text'] = $("#o_text").val();
        inputdata['a_text'] = $("#a_text").val();
        inputdata['p_text'] = $("#p_text").val();
        inputdata['e_text'] = $("#e_text").val();
        inputdata['r_text'] = $("#r_text").val();
        inputdata['_token'] = $('#customize > input[ name=_token]').val();
        $.ajax({
            type: 'POST',
            url: '/soap/post_soap',
            data: inputdata,
            success: function(result){
                if(result == 'success'){
                    alert("儲存成功");
                    $("#soap_save_btn").blur();
                }else{
                    alert("儲存失敗");
                    $("#soap_save_btn").blur();
                }
            }
        });
    });

    setCustomize($("#customize_type").val());
});

function delete_soap(soap_history_pk){

    if (confirm("確定要刪除嗎?") == true) {
        var inputdata = {};
        inputdata['_token'] = $('#soap_save > input[ name=_token]').val();
        inputdata['history'] = soap_history_pk;
        $.ajax({
            type: 'POST',
            url: '/soap/delete_history/',
            data: inputdata,
            success: function(result){
                if(result == "success"){
                    location.reload();
                }
            }
        });
    }
}

function setSOAClass( sub_class_pk){
    $.ajax({
        type: 'GET',
        url: '/soap/get_soa/' + sub_class_pk,
        success: function(result){
            if(result){
                var html = "";
                var soaps = ["S", "O", "A", "E"];
                for(var j = 0; j < soaps.length; j++){
                    var soap = soaps[j];
                    for(var i = 0; i< result[soap].length; i++){
                        html += '<option value="' + result[soap][i].soa_class_pk + '">' + result[soap][i].class_name + '</option>';
                    }
                    $("#"+soap.toLowerCase()).html(html);

                    html = "";
                }
            }
        }
    });
}

function setSOADetail(soa_class_pk){
    $.ajax({
        type: 'GET',
        url: '/soap/get_soa_detail/' + soa_class_pk,
        success: function(result){
            if(result){
                var html = "";
                for(var i = 0; i< result.length; i++){
                    html += '<option value="' + result[i].soa_detail_pk + '">' + result[i].detail_name + '</option>';
                }
                $("#p").html(html);
            }
        }
    });
}

function setCustomize( type){
    $.ajax({
        type: 'GET',
        url: '/soap/get_customize/' + type,
        success: function(result){
            if(result){
                var html = "";
                for(var i = 0; i< result.length; i++){
                    html += '<option value="' + result[i].user_customerize_pk + '">' + result[i].text + '</option>';
                }
                $("#customize_select").html(html);
            }
        }
    });
}

function appendText( text, textarea){
    textarea.val(textarea.val() + text + "\n");
    textarea.scrollTop(textarea[0].scrollHeight);
}