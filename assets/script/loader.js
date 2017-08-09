$(document).ready(function() {


    $('#khassida').click(function(){
       loadPage('khassida');
    });
    $('#home').click(function(){
        loadPage('homeSync');
    });
    $('#profile').click(function(){
        loadPage('profile');
    });
    function loadPage(page){
        fillThings('?action='+page,$('.content'), function(){
            $('.content')[0].className = 'content '+page;
        });
    }



    function fillThings(url, container, additionalInstructions, removeContent) {
        $(function() {
            $.ajax({ type: 'GET',
                url: url,
                dataType: 'text',
                success : function(butWhy)
                {
                    console.log(url, butWhy);
                    if (removeContent != false){
                        $(container).html(butWhy);
                    }else{
                        $(container).html($(container).html()+butWhy);
                    }

                    if (additionalInstructions !== undefined){
                        additionalInstructions();
                    }
                }
            });
        });
    }

});