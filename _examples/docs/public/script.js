/* @see http://bootsnipp.com/snippets/featured/bootstrap-30-treeview */
$(document).ready(function () {
    /**
     * Move the scrolling to the anchor's position with jQuery
     */
    var anchor = window.location.hash;
    if (anchor.indexOf("#") == 0)
    {
        $('html').animate({
            scrollTop: $('a' + anchor).offset().top
        });
    }

    /* tree search */
    $('.tree-search').css('display', 'block');
    $.each($('.tree'), function (i, obj) {
        $(obj).removeClass('corner');
        $(obj).children('li').not(':hidden').last().addClass("tree-li-last-child");
    });

    $('.tree-search input').on('keyup', function () {
        var tree = $('.tree'),
            items = $('.tree li'),
            value = $(this).val().toUpperCase();

        $.each(items, function (i, obj) {
            var text = $(obj).text().toUpperCase();

            if (text.indexOf(value) > -1) {
                $(obj).css("display", "block");
            } else {
                $(obj).css("display", "none");
            }

            $(obj).removeClass('tree-li-last-child');
        });

        $.each($('.tree'), function (i, obj) {
            $(obj).children('li').not(':hidden').last().addClass("tree-li-last-child");
        });
    });

    /* hide code body */
    $('.method .body .highlight').css('display', 'none');
    $('.method .body h3 a').click(function () {
        var target = $(this).parent().parent().find('.highlight');

        if (target.css('display') == 'none') {
            target.css('display', 'block');
        } else {
            target.css('display', 'none');
        }
		
		return false;
    });
});