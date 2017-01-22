Ext.define('qd.mediadb.TrayLogger', {
	extend		: 'Ext.Button',
	alias		: 'widget.traylogger',
	cls			: 'ux-desktop-traylogger',
	html		: '&#160;',

	initComponent: function () {
		var that = this;
		that.logStore = Ext.create('Ext.data.ArrayStore', {fields:['log']});
		that.winLogger= Ext.create('Ext.window.Window', {
			title		: 'Logger',
			height		: 200,
			width		: 400,
			layout		: 'fit',
			closeAction : 'hide',
			maximizable : true,
			tbar		: [{
				xtype		: 'button',
				text		: 'clear',
				listeners	: {
					click		: function(){
						that.logStore.removeAll();
					}
				}
			}],
			items		: {
				xtype		: 'grid',
				border		: false,
				columns		: [{
					header		: 'log',
					dataIndex	: 'log',
					flex		: 1
				}],
				store	: that.logStore
			}
		})

		/*var ws = new ReconnectingSockJS('http://' + window.location.hostname + ':15674/stomp');
		ws.reconnectInterval = 3000;
		var client = Stomp.over(ws);

		client.heartbeat.outgoing = 0;
		client.heartbeat.incoming = 0;
		client.debug = false;

		var on_connect = function(x) {
			id = client.subscribe("/topic/qdmmmdb", function(d) {
				//console.log(d.body);
				that.logStore.add({log:d.body})
			});
		};
		var on_error =  function(msg) {
			console.log('error ' + msg);
		};
		client.connect('guest', 'guest', on_connect, on_error, '/');
		that.setText('connected');
		that.listeners={
			click :function(){
				that.winLogger[that.winLogger.isVisible()?'hide':'show']();
			}
		};
		*/
		that.callParent();
	},

	afterRender: function () {
		var me = this;
		me.callParent();
	},

	onDestroy: function () {
		var me = this;
		me.callParent();
	},
});
