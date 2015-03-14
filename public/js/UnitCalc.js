$(document).on('change','#number_before',function(){

    var number_before  = $("#number_before").val();
    var url = 'http://'+window.location.host +'/admin/menustore/unitcalc';
    var unit_store = $("#unit_store").find('option:selected').val();
    var unit_input = $("#unit_input").find('option:selected').val();
    if(number_before != '' && number_before != 0) {
        $.ajax({
            type : 'POST',
            url : url,
            async : false,
            data : {unit_store: unit_store,unit_input:unit_input,number_before:number_before},
            beforeSend : function (){
                $(".loading").show();
            },
            success : function (returnData) {
                //stuff
                if(returnData != false){
                    $("#number_after").val(returnData);

                }else{
                    alert('Đơn vị chuyển đỏi không phù hợp')    ;
                    $("#number_after").val(0);
                }
                $(".loading").hide();
            },
            error : function (xhr, textStatus, errorThrown) {
                //other stuff
            },
            complete : function (){

            }
        });
    }




})  ;

$(document).on('change','#unit_store , #unit_input',function(){

    var number_before  = $("#number_before").val();
    var url = 'http://'+window.location.host +'/admin/menustore/unitcalc';
    var unit_store = $("#unit_store").find('option:selected').val();
    var unit_input = $("#unit_input").find('option:selected').val();
    if(number_before != '' && number_before != 0) {
    $.ajax({
        type : 'POST',
        url : url,
        async : false,
        data : {unit_store: unit_store,unit_input:unit_input,number_before:number_before},
        beforeSend : function (){
            $(".loading").show();
        },
        success : function (returnData) {
            //stuff
            if(returnData != false){
                $("#number_after").val(returnData);

            }else{
                alert('Đơn vị chuyển đỏi không phù hợp')    ;
                $("#number_after").val(0);
            }
            $(".loading").hide();

        },
        error : function (xhr, textStatus, errorThrown) {
            //other stuff
        },
        complete : function (){

        }
    });
    }





})  ;