$(document).ready(function() {

    reloadPlease();

    function reloadPlease(){
        // If a link has a dropdown, add sub menu toggle.
        $('nav ul li a:not(:only-child)').click(function(e) {
            $(this).siblings('.nav-dropdown').toggle();
            // Close one dropdown when selecting another
            $('.nav-dropdown').not($(this).siblings()).hide();
            e.stopPropagation();
        });
        // Clicking away from dropdown will remove the dropdown class
        $('html').click(function() {
            $('.nav-dropdown').hide();
        });
        // Toggle open and close nav styles on click
        $('#nav-toggle').click(function() {
            $('nav ul').slideToggle();
        });
        // Hamburger to X toggle
        $('#nav-toggle').on('click', function() {
            this.classList.toggle('active');
        });
    }

    var frmBis = $('#formRegister');
    frmBis.submit(function (e) {

        e.preventDefault();

        $.ajax({
            type: frmBis.attr('method'),
            url: frmBis.attr('action'),
            data: frmBis.serialize(),
            cache: false,

            success: function (data) {
                $('.error_register').html('');
                $('.success_register').html("Inscription reussie <span id='loginAfterRegister'>connectez-vous</span>");

                $('#loginAfterRegister').click(function(){
                    $('#registerModal').css('display','none');
                    $('#myModal').css('display','block');
                });
            },
            error: function (data) {
                var res = data.responseText.split('<!DOCTYPE html>')[0];
                var message = res.split(':')[1].split('"')[1];
                $('.error_register').html(message);
            }
        });
    });

    var frmTer = $('#formLogin');
    frmTer.submit(function (e) {
        e.preventDefault();

        $.ajax({
            type: frmTer.attr('method'),
            url: frmTer.attr('action'),
            data: frmTer.serialize(),
            success: function (data) {
                $('#myModal').css('display','none');
                $("header").load(location.href + " header>*", "");
                $('html').click(function() {
                    $('.nav-dropdown').show();
                });
                //return false;
            },
            error: function (data) {
                var res = data.responseText.split('<!DOCTYPE html>')[0];
                var message = res.split(':')[1].split('"')[1];

                $('.error_login').html(message);
                $('.success_login').html('');

            }
        });

    });



    $( ".brand" ).one( "click", function() {
        $( this ).css( "transform" , "scale(0.8)" );
    });


    var Url = window.location.href.split("?action=")[1];
    var page = Url.split("&token")[0];
    var current_page = page.split("&from")[0];
    var token = '';


    $('header section nav ul li '+current_page).addClass('active');
    $('header section nav ul li a[href^="?action=' + current_page + '"]').addClass('active');
    if(current_page == 'reglog'){
        $('header a[href^="?action=user"]').addClass('active');
    }


    $('html').click(function() {
        $('.nav-dropdown').hide();
    });
});
