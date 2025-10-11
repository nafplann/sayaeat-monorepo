/*------------------------------------------------
    Page Loader
-------------------------------------------------*/
$(window).on('load', function () {
    setTimeout(function () {
        $('.page-loader').fadeOut();
    }, 500);
});


$(document).ready(function () {
    /*------------------------------------------------
        Theme Switch
    -------------------------------------------------*/
    $('body').on('change', '.theme-switch input:radio', function () {
        var theme = $(this).val();

        $('body').attr('data-ma-theme', theme);
    });


    /*------------------------------------------------
        Search
    -------------------------------------------------*/
    // Active Stat
    $('body').on('focus', '.search__text', function () {
        $(this).closest('.search').addClass('search--focus');
    });

    // Clear
    $('body').on('blur', '.search__text', function () {
        $(this).val('');
        $(this).closest('.search').removeClass('search--focus');
    });


    /*------------------------------------------------
        Sidebar toggle menu
    -------------------------------------------------*/
    $('body').on('click', '.navigation__sub > a', function (e) {
        e.preventDefault();

        $(this).parent().toggleClass('navigation__sub--toggled');
        $(this).next('ul').slideToggle(250);
    });


    /*------------------------------------------------
        Form group blue line
    -------------------------------------------------*/
    if ($('.form-group--float')[0]) {
        $('.form-group--float').each(function () {
            var p = $(this).find('.form-control').val()

            if (!p.length == 0) {
                $(this).find('.form-control').addClass('form-control--active');
            }
        });

        $('body').on('blur', '.form-group--float .form-control', function () {
            var i = $(this).val();

            if (i.length == 0) {
                $(this).removeClass('form-control--active');
            } else {
                $(this).addClass('form-control--active');
            }
        });

        $(this).find('.form-control').change(function () {
            var x = $(this).val();

            if (!x.length == 0) {
                $(this).find('.form-control').addClass('form-control--active');
            }
        });
    }


    /*------------------------------------------------
        Clock
    -------------------------------------------------*/
    if ($('.time')[0]) {
        var newDate = new Date();
        newDate.setDate(newDate.getDate());

        setInterval(function () {
            var seconds = new Date().getSeconds();
            $('.time__sec').html((seconds < 10 ? '0' : '') + seconds);
        }, 1000);

        setInterval(function () {
            var minutes = new Date().getMinutes();
            $('.time__min').html((minutes < 10 ? '0' : '') + minutes);
        }, 1000);

        setInterval(function () {
            var hours = new Date().getHours();
            $('.time__hours').html((hours < 10 ? '0' : '') + hours);
        }, 1000);
    }

    var $body = $('body');

    //Fullscreen Launch function
    function launchIntoFullscreen(element) {

        if (element.requestFullscreen) {
            element.requestFullscreen();
        } else if (element.mozRequestFullScreen) {
            element.mozRequestFullScreen();
        } else if (element.webkitRequestFullscreen) {
            element.webkitRequestFullscreen();
        } else if (element.msRequestFullscreen) {
            element.msRequestFullscreen();
        }
    }

    //Fullscreen exit function
    function exitFullscreen() {

        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        }
    }

    $body.on('click', '[data-ma-action]', function (e) {
        e.preventDefault();

        var $this = $(this);
        var action = $this.data('ma-action');
        var target = '';

        switch (action) {
            /*-------------------------------------------
                Search
            ---------------------------------------------*/
            // Open
            case 'search-open':
                $('.search').addClass('search--toggled');
                break;

            // Close
            case 'search-close':
                $('.search').removeClass('search--toggled');
                break;


            /*-------------------------------------------
                Aside
            ---------------------------------------------*/
            // Open
            case 'aside-open':
                target = $this.data('ma-target');
                $this.addClass('toggled')
                $(target).addClass('toggled');
                $('.content, .header').append('<div class="ma-backdrop" data-ma-action="aside-close" data-ma-target=' + target + ' />');
                break;


            case 'aside-close':
                target = $this.data('ma-target');
                $('[data-ma-action="aside-open"], ' + target).removeClass('toggled');
                $('.content, .header').find('.ma-backdrop').remove();
                break;


            /*-------------------------------------------
                Full screen browse
            ---------------------------------------------*/
            case 'fullscreen':
                launchIntoFullscreen(document.documentElement);
                break;


            /*-------------------------------------------
                Print
            ---------------------------------------------*/
            case 'print':
                window.print();
                break;


            /*-------------------------------------------------
                Clear local storage (SweetAlert 2 required)
            --------------------------------------------------*/
            case 'clear-localstorage':
                swal({
                    title: 'Are you sure?',
                    text: 'This can not be undone!',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, clear it',
                    cancelButtonText: 'No, cancel'
                }).then(function () {
                    localStorage.clear();
                    swal(
                        'Cleared!',
                        'Local storage has been successfully cleared',
                        'success'
                    );
                });
                break;


            /*-------------------------------------------
                Login
            --------------------------------------------*/
            case 'login-switch':
                target = $this.data('ma-target');
                $('.login__block').removeClass('active');
                $(target).addClass('active');
                break;


            /*-------------------------------------------
                Notifications clear
            --------------------------------------------*/
            case 'notifications-clear':
                e.stopPropagation();

                var items = $('.top-nav__notifications .listview__item');
                var itemsCount = items.length;
                var index = 0;
                var delay = 150;

                $this.fadeOut();

                items.each(function () {
                    var currentItem = $(this);
                    setTimeout(function () {
                        currentItem.addClass('animated fadeOutRight');
                    }, index += delay);
                });

                setTimeout(function () {
                    items.remove();
                    $('.top-nav__notifications').addClass('top-nav__notifications--cleared');
                }, itemsCount * 180);
                break;


            /*------------------------------------------------
                Toolbar search toggle
            -------------------------------------------------*/

            // Open
            case 'toolbar-search-open':
                $(this).closest('.toolbar').find('.toolbar__search').fadeIn(200);
                $(this).closest('.toolbar').find('.toolbar__search input').focus();
                break;

            // Close
            case 'toolbar-search-close':
                $(this).closest('.toolbar').find('.toolbar__search input').val('');
                $(this).closest('.toolbar').find('.toolbar__search').fadeOut(200);
                break;
        }
    });

    $('.number-mask').mask('#.##0', {
        reverse: true,
        onKeyPress: function (cep, event, currentField, options) {
            const cleaned = $(currentField).cleanVal();
            $(currentField).attr('data-raw-value', cleaned);
        },
    });

    if ($('.app-image_picker')[0]) {
        $('.app-image_picker').each(function () {
            const fieldName = $(this).data('name');
            const isMultiple = $(this).data('multiple');
            const placeholderImage = $(this).data('placeholder-image');

            $(this).spartanMultiImagePicker({
                fieldName,
                maxCount: isMultiple ? 2 : 1,
                allowedExt: 'png|jpg|jpeg',
                placeholderImage: {
                    image: placeholderImage,
                    width: '100%'
                },
                groupClassName: 'col-md-4 col-sm-4 col-xs-6 p-0'
            });
        });
    }

    if ($(".time-picker")[0]) {
        $(".time-picker").flatpickr({
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true
        });
    }
});
