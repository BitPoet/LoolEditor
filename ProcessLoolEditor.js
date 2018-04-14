
$(document).ready(function() {
	$('#loleafletform_viewer').submit();

	window.addEventListener('message', function(evt) {
		var data = JSON.parse(evt.data);
		if(data["MessageId"] == "UI_Close") {
			// Pass message on to parent iframe
			window.parent.postMessage(evt.data, '*');
		}
	});
});
