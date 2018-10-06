
$(document).ready(function() {
	$('#loleafletform_viewer').submit();

	window.addEventListener('message', function(evt) {
		var data = JSON.parse(evt.data);
		if(data["MessageId"] == "UI_Close") {
			// Pass message on to parent iframe
			window.parent.postMessage(evt.data, '*');
		}

		if(data["MessageId"] == "UI_FileVersions") {
			var config = ProcessWire.config.looleditor;
			var token = config.token;
			var historyUrl = config.historyUrl;

			// Pass message on to parent iframe
			// window.parent.postMessage(evt.data, '*');
			var $content = $('#pw-content-body');
			
			var $frame = $content.find('iframe');
			
			$.ajax({
				url:		historyUrl,
				method:		'GET',
				data:		{
					access_token:	token
				},
				dataType:	'json',
				success:	function(ret) {
					//console.dir(ret);
					
					var $div = $('<div/>');
					$div.attr('id', 'LoolHistory');
					$div.css('position', 'absolute');
					$div.css('width', '20%');
					$div.css('height', 'auto');
					$div.css('z-index', 9999);
					$div.css('float', 'left');
					$div.css('right', 0);
					$div.css('top', 0);
					$div.css('padding', 0);

					var $hdr = $('<div/>');
					$hdr.css('height', '4em');
					$hdr.css('text-align', 'right');
					$hdr.css('border-bottom', '1px solid grey');
					
					var $closeBtn = $('<span/>');
					$closeBtn.addClass('fa fa-close');
					$closeBtn.css('color', 'darkred');
					$closeBtn.attr('title', config.labels.closeHistory);
					$closeBtn.css('cursor', 'pointer');
					$closeBtn.css('padding', '0.5em');
					$closeBtn.css('font-size', '2em');
					
					$closeBtn.on('click', function(evt) {
						var $hist = $('#LoolHistory');
						$hist.remove();
						$frame.css('width', '100%');
					});
					
					$hdr.append($closeBtn);
					
					$div.append($hdr);

					var $headline = $('<h2>' + config.labels.versions + '</h2>');
					$headline.css('margin-left', '0.5em');
					$headline.css('margin-right', '0.5em');

					$div.append($headline);
					
					var $ul = $('<ul/>');
					$ul.css('margin-left', '0.5em');
					$ul.css('margin-right', '0.5em');
					$ul.css('list-style-type', 'none');
					
					$ul.append($('<li><strong>' + config.labels.latest + '</strong></li>'));
					
					for(var i = 0, l = ret.length; i < l; i++) {
						var cur = ret[i];
						var $li = $('<li/>');
						$li.data('url', historyUrl + cur.filename);
						$li.data('token', token);
						$li.data('readonly', true);
						$li.text(cur.modified);
						$li.css('cursor', 'pointer');
						$ul.append($li);
					}
					
					$div.append($ul);
					
					$frame.css("width", "80%");
					
					$content.append($div);
					
					$content.resize();
				},
				error:		function(xhr, msg, stat) {
					console.log("Error loading history from " + historyUrl + ": " + msg + ' [' + stat + ']');
					//console.log(xhr.responseText);
				}
			});
		}

	});
});
