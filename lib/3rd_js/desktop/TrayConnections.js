Ext.define('MyDesktop.TrayConnections', {
	extend		: 'Ext.toolbar.TextItem',
	alias		: 'widget.trayconnections',
	cls			: 'ux-desktop-trayconnections',
	html		: '&#160;',
	tpl			: '{img}/{xhr}',

	initComponent: function () {
		var me = this;
		me.callParent();
		if (typeof(me.tpl) == 'string') {
			me.tpl = new Ext.XTemplate(me.tpl);
		}
	},

	afterRender: function () {
		var me = this;
		Ext.Function.defer(me.updateTime, 100, me);
		me.callParent();
	},

	onDestroy: function () {
		var me = this;
		if (me.timer) {
			window.clearTimeout(me.timer);
			me.timer = null;
		}
		me.callParent();
	},

	updateTime: function () {
		var me = this, text = me.tpl.apply(Ext.globalHandlerCounter);
		if (me.lastText != text) {
			me.setText(text);
			me.lastText = text;
		}
		me.timer = Ext.Function.defer(me.updateTime, 1000, me);
	}
});