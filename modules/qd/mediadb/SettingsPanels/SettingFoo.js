Ext.define('qd.mediadb.SettingsPanels.SettingFoo', {
	extend		: 'MyDesktop.SettingsPanels',
	settingId	: 'settingfoo',
	title		: 'Setting Foo',

	onBarSubItemsSelect:  function(tree, record) {
		var that = this;
		Ext.getCmp(that.mainid).update(record.get('params'));
	},

	constructor	:	function(cfg){
		var that	= this;
		that.mainid	= Ext.id();

		that.barSubItems = {
			text		: '',
			expanded	: true,
			children	: [{
				text		: "text1",
				iconCls		: '',
				leaf		: true,
				params		: 1
			},{
				text		: "text2",
				iconCls		: '',
				leaf		: true,
				params		: 2
			}]
		}
		that.main = {
			id		: that.mainid,
			html	: 'aa2',
			bbar : ['->',{
				xtype	: 'button',text: 'OK', handler: that.onOK, scope: that
			},{
				xtype	: 'button',text: 'Cancel', handler: that.setBarSubItemsInitialSelection, scope: that
			}]
		}
		that.callParent(that);
	}
},function(){
	this.superclass.self.registerPanel(this.$className);
});