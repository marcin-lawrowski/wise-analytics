function bindOffsiteLinks() {
	var regExp = new RegExp("//" + window.location.host + "($|/)");
	var detectedLinks = document.getElementsByTagName("a");

	if (detectedLinks.length === 0) {
		return;
	}

	for (var link_i = 0; link_i <= detectedLinks.length; link_i++) {
		var detectedLink = detectedLinks[link_i];

		if (!detectedLink || !detectedLink.attributes.href) {
			continue;
		}
		var thisHref = detectedLink.attributes.href.value;
		var isNonExternal = (thisHref.substring(0, 4) === "http") ? regExp.test(thisHref) : true;

		if (isNonExternal) {
			continue;
		}

		const clickListener = function(event, onclickFunction) {
			event.preventDefault();
			var linkHref = findEventTargetLink(event);
			var linkElem = findEventTarget(event);
			var trackCallback = function() {
				if (typeof onclickFunction === "function") {
					var ret = true;
					try {
						ret = onclickFunction.apply(linkElem);
					} catch (err) {
						if (console) {
							console.error(err);
						}
					}
					if (ret !== false) {
						if (linkElem.target !== "_blank") {
							window.location = linkHref;
						}
					}
				} else {
					if (linkElem.target !== "_blank") {
						window.location = linkHref;
					}
				}
			};

			wa.track('external-page-view', { url: linkHref }, trackCallback);

			// prevents blocking:
			if (linkElem.target === "_blank" && window.isOpening !== linkHref) {
				window.isOpening = linkHref;
				setTimeout(function() { window.isOpening = ''; }, 1500);
				var newWindow = window.open();
				if (newWindow != null) {
					newWindow.location = linkHref;
				}
			}
		};

		var onclickFunction = null;
		if (typeof detectedLink.onclick === "function") {
			onclickFunction = detectedLink.onclick;
			detectedLink.onclick = '';
		}

		detectedLink.addEventListener("click", (function(callback) {
			return function(event) { clickListener(event, callback); }
		})(onclickFunction));
	}
}

function findEventTargetLink(evt) {
    let elem;
	if (evt.srcElement) {
		elem = evt.srcElement;
	} else if (evt.target) {
		elem = evt.target;
	}
    if (!elem) {
        console.log('wa: could not find the target link')
        return null;
    }

	if (elem.nodeName !== 'A') {
		var max = 5;
		var tries = 0;
		var parent = elem.parentNode;

		while (parent.nodeName !== 'A' && tries < max) {
			tries++;
			parent = parent.parentNode;
		}

		return parent.href;
	} else {
		return elem.href;
	}
}

function findEventTarget(evt) {
    let elem;
	if (evt.srcElement) {
		elem = evt.srcElement;
	} else if (evt.target) {
		elem = evt.target;
	}
    if (!elem) {
        console.log('wa: could not find the target')
        return null;
    }

	if (elem.nodeName !== 'A') {
		var max = 5;
		var tries = 0;
		var parent = elem.parentNode;
		while (parent.nodeName !== 'A' && tries < max) {
			tries++;
			parent = parent.parentNode;
		}

		return parent;
	} else {
		return elem;
	}
}

exports.bindOffsiteLinks = bindOffsiteLinks;