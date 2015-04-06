/*
filter supplier
 */

$(document).ready(function () {
    $(".jqueryte").jqte();
    activeMenu();
});
function activeMenu(){
    var url      = window.location.pathname;
    $('.nav  li').each(function(){
        var href = $(this).children('a').attr('href');
        var className = "";
        if(href == url){
            className = "in"
            console.log(href+'--'+url+'--'+className);
            //parent

            //children
            $(this).parent().addClass(className);
            $(this).show();
            $(this).addClass('active');
        }

    });

}
function disableSubmit(){
    $("input[type='submit'] , button[type='submit']").attr('disabled','disabled');
}
function enableSubmit(){
    $("input[type='submit'] , button[type='submit']").removeAttr('disabled');
}

$(document).on('change','#supplyType',function(){
     var supplyTypeId = $(this).find("option:selected").val();
    var url = 'http://'+window.location.host+'/admin/supplier/getsuplier';
    if(supplyTypeId != 0 && supplyTypeId != ''){
        $.ajax({
            type : 'POST',
            url : url,
            async : false,
            data : {suplier_item_id: supplyTypeId},
            beforeSend : function (){
                $(".loading").show();
            },
            success : function (returnData) {
                //stuff
                var data = JSON.parse(returnData);
                var html = '';
                if(data.status){

                    $.each( data.result , function( key, value ) {

                     html += '<option value="'+key+'">'+value+'</option>';

                    } );
                    $("#supplier").html(html);
                    enableSubmit();
                    $(".loading").hide();

                }else{
                    alert('Chưa có nhà cung cấp cho mặt hàng này!') ;
                    disableSubmit();
                    $("#supplier").html('');
                    $(".loading").hide();
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


/*
number format
 */
$(function() {
    $('.number-format').blur(function() {
        $('.number-format').html(null);
        $(this).priceFormat({
            colorize: true,
            negativeFormat: '-%s%n',
            roundToDecimalPlace: 0,
            symbol: '',
            prefix: ' ',
            suffix: ' '  ,
            centsLimit:0 ,
            thousandsSeparator: '.'
        });
    })
        .keyup(function(e) {
            var e = window.event || e;
            var keyUnicode = e.charCode || e.keyCode;
            if (e !== undefined) {
                switch (keyUnicode) {
                    case 16: break; // Shift
                    case 17: break; // Ctrl
                    case 18: break; // Alt
                    case 27: this.value = ''; break; // Esc: clear entry
                    case 35: break; // End
                    case 36: break; // Home
                    case 37: break; // cursor left
                    case 38: break; // cursor up
                    case 39: break; // cursor right
                    case 40: break; // cursor down
                    case 78: break; // N (Opera 9.63+ maps the "." from the number key section to the "N" key too!) (See: http://unixpapa.com/js/key.html search for ". Del")
                    case 110: break; // . number block (Opera 9.63+ maps the "." from the number block to the "N" key (78) !!!)
                    case 190: break; // .
                    default: $(this).priceFormat({ colorize: true, negativeFormat: '-%s%n', roundToDecimalPlace: -1, eventOnDecimalsEntered: true });
                }
            }
        })

});
$(document).on('click','.switch-order-user',function(){
   var order_id = $(this).attr('data-order-id');
    $("#order_id_hidden").val(order_id);
})  ;
$(document).on('click','.merge-order',function(){

    var order_id = $(this).attr('data-order-id');
    var fromTable = $(this).attr('data-table');
    $("#order_id_hidden").val(order_id);
    $("#fromTable").val(order_id);

});
$(document).on('click','.split-order',function(){

    var order_id = $(this).attr('data-order-id');
    var url = 'http://'+window.location.host;
   if(order_id != 0){
       $('#split-tbody').html('');
       $('#oldOrder').val(0);
// and here goes your synchronous ajax call
       $.ajax({
           type : 'POST',
           url : url+'/frontend/order/ajaxDetailOrder',
           async : false,
           data : {order_id:order_id},
           success : function (returnData) {
               //stuff
               var data = JSON.parse(returnData);
               if(data.oldOrderId != ''){
                       $('#split-tbody').append(data.data);
                       $('#oldOrder').val(data.oldOrderId);

               }else{
                   $("#split-modal").modal('hide');
               }
           },
           error : function (xhr, textStatus, errorThrown) {
               //other stuff
           },
           complete : function (){

           }
       });
   }

})      ;
$(document).on('change','#supplierItemId',function(){

    var text = $('select option:selected').text();
    $("#supplierItemName").val(text);

})  ;


