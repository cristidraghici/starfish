/* @see http://bootsnipp.com/snippets/featured/bootstrap-30-treeview */
$(document).ready(function () {

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
	$('.method .body h3').click(function () {
		var target = $(this).parent().find('.highlight');

		if (target.css('display') == 'none') {
			target.css('display', 'block');
		} else {
			target.css('display', 'none');
		}
	});
});