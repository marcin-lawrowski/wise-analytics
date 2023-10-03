var waConnector = waConnector || {};
waConnector.api = waConnector.api || {};

waConnector.api.Core = function() {
	var API_BASE_URL = waConfig.url + '/wa-api/';
	var baseDeferred = typeof jQuery !== 'undefined' && typeof jQuery.when !== 'undefined' ? jQuery.when({}) : null;
	var isUUIDCookie = typeof getUUIDCookie() !== 'undefined';
	var gaSettingDone = false;
	
	// in case of presence wa cookie all events can be posted in parallel:
	if (isUUIDCookie) {
		baseDeferred = null;
	}
	
	function initOnLoad() {
		
	}
	
	/**
	 * Returns the value of nwemid cookie.
	 *
	 * @returns {String}
	 */
	function getUUIDCookie() {
		if (typeof waConfig.cookie !== 'undefined') {
			return getCookie(waConfig.cookie);
		} else {
			return undefined;
		}
	}
	
	/**
	 * Returns cookie by name
	 *
	 * @param {String} cookieName
	 * @returns {String}
	 */
	function getCookie(cookieName) {
		var name = cookieName + "=";
		var decodedCookie = decodeURIComponent(document.cookie);
		var cookies = decodedCookie.split(';');
		for (var i = 0; i < cookies.length; i++) {
			var cookie = cookies[i];
			while (cookie.charAt(0) == ' ') {
				cookie = cookie.substring(1);
			}
			
			if (cookie.indexOf(name) == 0) {
				return cookie.substring(name.length, cookie.length);
			}
		}
		
		return undefined;
	}
	
	function onUUIDCookieSet() {
		if (gaSettingDone) {
			return;
		}
		gaSettingDone = true;
		
		var uuid = getCookie(waConfig.cookie);
		// UUID - related code goes here
	}
	
	/**
	 * Tracks event with additional data.
	 *
	 * @param {String} eventName
	 * @param {Object|undefined} customData
	 * @param {Function|undefined} doneCallback A function executed after the tracking is completed
	 */
	function track(eventName, customData, doneCallback) {
		var params = {
			ty: eventName,
			ur: window.location.href,
			ti: document.title,
			re: document.referrer,
			cs: getApiHash()
		};
		
		if (typeof customData !== 'undefined') {
			// custom URL parameter:
			if ('url' in customData) {
				if (typeof customData['url'] !== 'undefined') {
					params['ur'] = customData['url'];
				}
				delete customData['url'];
			}
		
			// attach custom data:
			params['da'] = JSON.stringify(customData);
		}

		waConnector.libs.botDetect()
			.then(function(botd) { return botd.detect(); })
			.then(function(result) {
				if (result.bot !== false) {
					return;
				}

				if (baseDeferred !== null) {
					// if jQuery is available send all events sequentially:
					baseDeferred = baseDeferred.then(apiCallDeferred('event', params, doneCallback));
				} else {
					// otherwise send each event without waiting:
					apiCall('event', params, doneCallback);
				}
			})
			.catch(function(error) { console.error(error) });
	}
	
	function apiCallDeferred(category, params, doneCallback) {
		return function() {
			var defer = jQuery.Deferred();
			var alteredDoneCallback = function() {
				if (!isUUIDCookie && typeof getUUIDCookie() !== 'undefined') {
					onUUIDCookieSet();
				}
				
				if (typeof doneCallback !== 'undefined') {
					doneCallback();
				}
				defer.resolve();
			}
			apiCall(category, params, alteredDoneCallback);
			
			return defer.promise();
		};
	}
	
	function apiCall(endpoint, data, doneCallback) {
		var imgSrc = API_BASE_URL + endpoint + objectToUrlParams(data);
		
		var imageNode = typeof IEWIN !== 'undefined' ? new Image() : document.createElement('img');
		imageNode.style.display = "none";
		
		// register done callback:
		imageNode.onload = function() {
			if (!isUUIDCookie && typeof getUUIDCookie() !== 'undefined') {
				onUUIDCookieSet();
			}

			if (typeof doneCallback !== 'undefined') {
				doneCallback();
			}
		};
		
		imageNode.src = imgSrc;
		imageNode.alt = '#';
		document.getElementsByTagName('body')[0].appendChild(imageNode);
	}
	
	function log() {
		console.log.apply(window, arguments);
	}
	
	function getApiHash() {
		function randPart() {
			return Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
		}
		
		var num1 = randPart();
		var num2 = randPart();
		var num3 = randPart();
		var num4 = num1[0] + '' +  num2[1] + '' + num3[2] + '' + num1[1] + '' +  num2[2] + '' + num3[3];
		
		return num1 + '' + num2 + '' + num3 + '' + num4;
	}
	
	function objectToUrlParams(data) {
		var out = [];
		for (var key in data) {
			out.push(key + '=' + encodeURIComponent(data[key]));
		}
		
		return (out.length > 0 ? '?' : '') + out.join('&');
	}
	
	this.initOnLoad = initOnLoad;
	this.track = track;
};

var wa = new waConnector.api.Core();

(function() {
	document.addEventListener("DOMContentLoaded", function(event) {
		wa.initOnLoad();
	});
})();
