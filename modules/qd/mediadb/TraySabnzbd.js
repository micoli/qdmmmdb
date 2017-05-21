Ext.define('qd.mediadb.TraySabnzbd', {
	extend		: 'Ext.toolbar.TextItem',
	alias		: 'widget.traysabnzbd',
	cls			: 'ux-desktop-traysabnzbd',
	html		: '&#160;',

	initComponent: function () {
		var me = this;
		me.callParent();
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
		var me = this;
		var cb = function(response,request){
			jsonData = Ext.JSON.decode(response.responseText);
			var text = sprintf('%02d KB/s %s',jsonData.kbpersec,jsonData.timeleft);

			if (me.lastText != text) {
				me.setText(text);
				me.lastText = text;
			}
			/////////me.timer = Ext.Function.defer(me.updateTime, 5*1000, me);
		}
		var ob ={
			url		: 'api/Sabnzbd/action',
			method	: 'POST',
			success	: cb,
			failure	: cb,
			reader	: 'json',
			params	: {
				sab_mode	: 'queue',
				obj_return	: 'queue'
			}
		};
		Ext.AjaxEx.request(ob);
	}
});