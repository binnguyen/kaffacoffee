jwerty.key('F6', function () {

   window.location.href='http://'+window.location.host+'/frontend/order/add';

});
jwerty.key('F7', function () {

    //addRowDetail();
    $('#save-order').trigger('click');

});
jwerty.key('F8', function () {


    $('#payment-order').trigger('click');

});

jwerty.key('F9', function () {

    $('#print').trigger('click');

}); // action  print in page detail


jwerty.key('F10', function () {

    $('#printFunc').trigger('click');

});  // action print in form print