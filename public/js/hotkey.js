
$(document).ready(function() {
    // window hot key
    var host = 'http://'+window.location.host;

    jwerty.key('F6', function () {
        window.location.href= host+'/frontend/order/add';
    });

}   );



