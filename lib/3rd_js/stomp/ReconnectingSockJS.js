/* * Latest version: https://github.com/joewalnes/reconnecting-websocket/
* - Joe Walnes
* just changed websocket to SockJS and working.... :)))
*/
function ReconnectingSockJS(url) {

	// These can be altered by calling code.
	this.debug = false;
	this.reconnectInterval = 1000;
	this.timeoutInterval = 2000;

	var self = this;
	var ws;
	var forcedClose = false;
	var timedOut = false;

	this.url = url;
	this.readyState = SockJS.CONNECTING;
	this.URL = url; // Public API

	this.onopen = function(event) {
	};

	this.onclose = function(event) {
	};

	this.onconnecting = function(event) {
	};

	this.onmessage = function(event) {
	};

	this.onerror = function(event) {
	};

	function connect(reconnectAttempt) {
		ws = new SockJS(url);

		self.onconnecting();
		if (self.debug || ReconnectingSockJS.debugAll) {
			console.debug('ReconnectingSockJS', 'attempt-connect', url);
		}

		var localWs = ws;
		var timeout = setTimeout(function() {
			if (self.debug || ReconnectingSockJS.debugAll) {
				console.debug('ReconnectingSockJS', 'connection-timeout', url);
			}
			timedOut = true;
			localWs.close();
			timedOut = false;
		}, self.timeoutInterval);

		ws.onopen = function(event) {
			clearTimeout(timeout);
			if (self.debug || ReconnectingSockJS.debugAll) {
				console.debug('ReconnectingSockJS', 'onopen', url);
			}
			self.readyState = SockJS.OPEN;
			reconnectAttempt = false;
			self.onopen(event);
		};

		ws.onclose = function(event) {
			clearTimeout(timeout);
			ws = null;
			if (forcedClose) {
				self.readyState = SockJS.CLOSED;
				self.onclose(event);
			} else {
				self.readyState = SockJS.CONNECTING;
				self.onconnecting();
				if (!reconnectAttempt && !timedOut) {
					if (self.debug || ReconnectingSockJS.debugAll) {
						console.debug('ReconnectingSockJS', 'onclose', url);
					}
					self.onclose(event);
				}
				setTimeout(function() {
					connect(true);
				}, self.reconnectInterval);
			}
		};
		ws.onmessage = function(event) {
			if (self.debug || ReconnectingSockJS.debugAll) {
				console.debug('ReconnectingSockJS', 'onmessage', url, event.data);
			}
		self.onmessage(event);
		};
		ws.onerror = function(event) {
			if (self.debug || ReconnectingSockJS.debugAll) {
				console.debug('ReconnectingSockJS', 'onerror', url, event);
			}
			self.onerror(event);
		};
	}
	connect(url);

	this.send = function(data) {
		if (ws) {
			if (self.debug || ReconnectingSockJS.debugAll) {
				console.debug('ReconnectingSockJS', 'send', url, data);
			}
			return ws.send(data);
		} else {
			throw 'INVALID_STATE_ERR : Pausing to reconnect websocket';
		}
	};

	this.close = function() {
		if (ws) {
			forcedClose = true;
			ws.close();
		}
	};

	/**
* Additional public API method to refresh the connection if still open (close, re-open).
* For example, if the app suspects bad data / missed heart beats, it can try to refresh.
*/
	this.refresh = function() {
		if (ws) {
			ws.close();
		}
	};
}

/**
* Setting this to true is the equivalent of setting all instances of ReconnectingSockJS.debug to true.
*/
ReconnectingSockJS.debugAll = false;