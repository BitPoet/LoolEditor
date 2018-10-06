
(function($) {
	
	var showEditor = function(evt) {
		evt.stopPropagation();
		evt.preventDefault();

		var lnk = evt.currentTarget;
		var url = $(lnk).attr('href');
		
		var $popup = $('<iframe></iframe>');
		$popup.addClass('pwLoolEditorPopup');
		$popup.attr('src', url);
		
		/* ToDo: Listen to close event from loleaflet */
		
		var $overlay = $('<div/>');
		$overlay.addClass('pwLoolEditorOverlay');
		$(document.body).append($overlay);
		
		$(document.body).append($popup);
		
		$("html, body").animate({ scrollTop: 0 }, "slow");
		
		return false;
	}
	
	$.fn.loolEditor = function(cmd) {
		if(cmd == 'close') {
			$('iframe.pwLoolEditorPopup').remove();
			$('div.pwLoolEditorOverlay').remove();
			return;
		}
		this.each(function(idx, thisEl) {
			$(this).on('click', showEditor);
		});
	}
})(jQuery);

$(document).ready(function() {
	$('a.pwLoolEditorLink').loolEditor();

	window.addEventListener('message', function(evt) {
		var data = JSON.parse(evt.data);
		
		//console.log("message event in LoolEditor.js");
		//console.dir(evt);

		if(data.MessageId == "UI_Close") {
			$(this).loolEditor('close');
		}
	});
});

