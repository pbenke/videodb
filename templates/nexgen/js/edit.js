/**
 * Editing logic
 *
 * @package JavaScript
 * @author  Andreas Goetz	<cpuidle@gmx.de>
 */

function lookupData(title) {
	var win	= open('lookup.php?find=' + encodeURIComponent(title), 'lookup',
		           'width=700,height=600,menubar=no,resizable=yes,scrollbars=yes,status=yes,toolbar=no');
	win.focus();
}

function lookupImage(title) {
	var win	= open('lookup.php?find=' + encodeURIComponent(title) + '&searchtype=image&engine=google', 'lookup',
		       'width=450,height=500,menubar=no,resizable=yes,scrollbars=yes,status=yes,toolbar=no');
	win.focus();
}

function changedId() {
	if ($("#imdbID").val()) {
		$('#lookup1').click();
	}
}
