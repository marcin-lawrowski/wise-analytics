var waConnector = waConnector || {};
waConnector.api = waConnector.api || {};

/**
 * @constructor
 */
waConnector.api.Commons = function(nwapi) {
	
	var trackedExtensions = [
		// Word Legacy
		'doc','dot',
		// Word OOXML
		'docx', 'docm', 'dotx', 'dotm', 'docb',
		// Excel Legacy
		'xls', 'xlt', 'xlm',
		// Excel OOXML
		'xlsx', 'xlsm', 'xltx', 'xltm',
		// Excel Other
		'xlsb', 'xla','xlam', 'xll', 'xlw',
		// Powerpoint Legacy
		'ppt', 'pot', 'pps',
		// Powerpoint OOXML
		'pptx', 'pptm', 'potx', 'potm', 'ppam', 'ppsx', 'ppsm', 'sldx', 'sldm',
		// Access
		'accdb', 'accde', 'accdt', 'accdr',
		// Open Document Text,
		'odt',
		// Wordperfect Document
		'wpd',
		// Open Document Spreadsheet
		'ods',
		// PDF
		'pdf',
		// Ebooks
		'epub', 'mobi',
		// Raw Text
		'txt', 'rtf', 'xml', 'otf',
		// Archives
		'zip', 'tar', 'gz', '7z', 'rar', 'jar',
		// Special Images
		'dwg', 'dxf', 'eps', 'psd'
	];
	
	function findEventTargetLink(evt) {
		if (evt.srcElement) {
			elem = evt.srcElement;
		} else if (evt.target) {
			elem = evt.target;
		}

 		if (elem.nodeName != 'A') {
 			var max = 5;
			var tries = 0;
 			var parent = elem.parentNode;
 			
			while (parent.nodeName != 'A' && tries < max) {
 				tries++;
 				parent = parent.parentNode;
 			}
			
 			return parent.href;
 		} else {
			return elem.href;
		}
	}

	function findEventTarget(evt) {
		if (evt.srcElement) {
			elem = evt.srcElement;
		} else if (evt.target) {
			elem = evt.target;
		}

 		if (elem.nodeName != 'A') {
 			var max = 5;
			var tries = 0;
 			var parent = elem.parentNode;
 			while (parent.nodeName != 'A' && tries < max) {
 				tries++;
 				parent = parent.parentNode;
 			}
			
 			return parent;
 		} else {
			return elem;
		}
	}
	
	function bindOffsiteLinks() {
		var regExp = new RegExp("//" + window.location.host + "($|/)");
		var links = document.getElementsByTagName("a");
		
		if (links.length === 0) {
			return;
		}
		
		for (var link_i = 0; link_i <= links.length; link_i++) {
			var thisLink = links[link_i];
			
			if (typeof thisLink === "undefined" || !thisLink.attributes.href) {
				continue;
			}
			
			var thisHref = thisLink.attributes.href.value;
			var isLocal = (thisHref.substring(0, 4) === "http") ? regExp.test(thisHref) : true;
			
			if (isLocal) {
				continue;
			}
			
			var clickListener = function(event, onclickFunction) {
				event.preventDefault();
				var linkHref = findEventTargetLink(event);
				var linkElem = findEventTarget(event);
				var trackCallback = function() {
					if (typeof onclickFunction == "function") {
						var ret = true;
						try {
							ret = onclickFunction.apply(linkElem);
						} catch (err) {
							if (console) {
								console.error('nwapi onclick exception', err);
							}
						}
						if (ret !== false) {
							if (linkElem.target != "_blank") {
								window.location = linkHref;
							}
						}
					} else {
						if (linkElem.target != "_blank") {
							window.location = linkHref;
						}
					}
				};
				
				// track the event:
				nwapi.track('external-site-visited', { url: linkHref }, trackCallback);
				
				// this prevents blocking of a new window in browser:
				if (linkElem.target == "_blank" && window.isOpening != linkHref) {
					window.isOpening = linkHref;
					setTimeout(function() { window.isOpening = ''; }, 1500);
					var newWindow = window.open();
					if (newWindow != null) {
						newWindow.location = linkHref;
					}
				}
			};
			
			var onclickFunction = null;
			if (typeof thisLink.onclick == "function") {
				onclickFunction = thisLink.onclick;
				thisLink.onclick = '';
			}
			
			thisLink.addEventListener("click", (function(callback) {
				return function(event) { clickListener(event, callback); }
			})(onclickFunction));	
		}
	}
	
	function bindDocLinks() {
		var links = document.getElementsByTagName("a");
		if (links.length === 0) {
			return;
		}
		
		for (var link_i = 0; link_i <= links.length; link_i++) {
			var thisLink = links[ link_i ];
			if (typeof thisLink === "undefined" || !thisLink.attributes.href) {
				continue;
			}
			
			var thisHref = thisLink.attributes.href.value;
			var extension = thisHref.split('.').pop() + '';
			if (trackedExtensions.indexOf(extension.toLowerCase()) < 0) {
				continue;
			}
			
			var clickListener = function(event, onclickFunction) {
				event.preventDefault();
				var linkHref = findEventTargetLink(event);
				var linkElem = findEventTarget(event);
				
				var trackCallback = function() {
					if (typeof onclickFunction == "function") {
						var ret = true;
						try {
							ret = onclickFunction.apply(linkElem);
						} catch (err) {
							if (console) {
								console.error('nwapi onclick exception', err);
							}
						}
						if (ret !== false) {
							if (linkElem.target != "_blank") {
								window.location = linkHref;
							}
						}
					} else {
						if (linkElem.target != "_blank") {
							window.location = linkHref;
						}
					}
				}
				
				// track the event:
				nwapi.track('document-viewed', { url: linkHref }, trackCallback);
				
				// this prevents blocking of a new window in browser:
				if (linkElem.target == "_blank" && window.isOpening != linkHref) {
					window.isOpening = linkHref;
					setTimeout(function() { window.isOpening = ''; }, 1500);
					var newWindow = window.open();
					if (newWindow != null) {
						newWindow.location = linkHref;
					}
				}
			}
			
			var onclickFunction = null;
			if (typeof thisLink.onclick == "function") {
				onclickFunction = thisLink.onclick;
				thisLink.onclick = '';
			}
			
			thisLink.addEventListener("click", (function(callback) {
				return function(event) { clickListener(event, callback); }
			})(onclickFunction));
		}
	}
	
	function bindVideoTags() {
		var videos = document.getElementsByTagName("video");
		if (videos.length === 0) {
			return;
		}
		
		for (var video_i = 0; video_i < videos.length; video_i++) {
			(function (video_i_inner) {
				var video = videos[ video_i_inner ];
				var isPlaying = false;
				var videoStorage = {};
				var videoName = null;
				var videoSource = null;
				var videoSources = video.getElementsByTagName("source");
				
				if (!video.attributes['data-tracked']) {
					video.setAttribute('data-tracked', 1);
				} else {
					return;
				}
				
				// determine the name of the video by checking data-name attribute:
				if (video.attributes['data-name']) {
					videoName = video.attributes['data-name'].value;
				}
				
				// determine the source URI of the video by querying the nested <source> tag:
				if (videoSources.length > 0) {
					videoSource = videoSources[0].src;
				}
				
				function roundHalf(num) {
					return Math.round(num * 2) / 2;
				}
				
				function trackVideoEvent(eventCategory) {
					// track the event:
					nwapi.track('video-action', { category: eventCategory, name: videoName, src: videoSource });
				}
				
				function setKeyFrames (duration) {
					var quarter = (duration / 4).toFixed(1);
					var tenth = (duration / 10).toFixed(1);
					
					videoStorage['one'] = roundHalf(tenth);
					videoStorage['two'] = roundHalf(quarter);
					videoStorage['three'] = roundHalf((quarter * 2).toFixed(1));
					videoStorage['four'] = roundHalf((quarter * 3).toFixed(1));
					videoStorage['five'] = roundHalf((tenth * 9).toFixed(1));
				}
				
				function videoTimeUpdate () {
					var curTime = roundHalf(video.currentTime.toFixed(1));
					
					switch (curTime) {
						case videoStorage['one']:
							trackVideoEvent('10% played');
							delete videoStorage['one'];
							break;
						case videoStorage['two']:
							trackVideoEvent('25% played');
							delete videoStorage['two'];
							break;
						case videoStorage['three']:
							trackVideoEvent('50% played');
							delete videoStorage['three'];
							break;
						case videoStorage['four']:
							trackVideoEvent('75% played');
							delete videoStorage['four'];
							break;
						case videoStorage['five']:
							trackVideoEvent('90% played');
							delete videoStorage['five'];
							break;
					}
				}
				
				function videoEnd () {
					isPlaying = false;
					trackVideoEvent('Watched to End');
				}
				
				function videoPlay (e) {
					if (!isPlaying) {
						isPlaying = true;
						trackVideoEvent('Played');
						setKeyFrames(this.duration);
					}
				}
				
				function videoPause () {
					trackVideoEvent('Paused');
				}
				
				video.addEventListener('ended', videoEnd, false);
				video.addEventListener('timeupdate', videoTimeUpdate, false);
				video.addEventListener('play', videoPlay, false);
				video.addEventListener('pause', videoPause, false);
				
			})(video_i);
		}
	}
	
	function initOnLoad() {
		bindOffsiteLinks();
		bindDocLinks();
		bindVideoTags();
	}
	
	this.initOnLoad = initOnLoad;
	
	// add function to nwapi object:
	wa.trackVideoTags = bindVideoTags;
};

(function() {
	document.addEventListener("DOMContentLoaded", function(event) {
		if (typeof wa !== 'undefined') {
			var commons = new waConnector.api.Commons(wa);
			commons.initOnLoad();
		} else if (console) {
			console.error("WA API object was not found");
		}
	});
})();