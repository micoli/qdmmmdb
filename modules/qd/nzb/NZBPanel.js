Ext.define('qd.nzb.NZBPanel', {
	extend		: 'Ext.Panel',
	alias		: 'widget.qd.nzb.NZBPanel',
	requires	: [
		'qd.nzb.feedtab',
		'qd.nzb.nzbview'
	],
	initComponent : function(){
		var that=this;
		that.tabPanelId = Ext.id();
		that.addSearchTab = function (ob){
			var tabs = Ext.getCmp(that.tabPanelId);
			var activeTab = tabs.child('#tab-'+ob.id);
			if (!activeTab){
				activeTab = Ext.create('qd.nzb.nzbview',{
					title		: '-' + (ob.title?ob.title:ob.mask),
					oTitle		: '-' + (ob.title?ob.title:ob.mask),
					id			: 'tab-'+ob.id,
					closable	: true,
					link		: (ob.link?ob.link:false),
					q			: ob.mask,
					record		: ob.record==undefined?null:ob.record
				});
				tabs.add(activeTab);
			}
			tabs.setActiveTab(activeTab);
			activeTab.nzbstore.load({
				params :{
					q  : activeTab.q,
					dc : Math.random(),
					s  : Ext.getCmp('comboSearcher').getValue()
				}
			});
		}
		Ext.apply(this,{
			layout		: 'border',
			border				: false,
			tbar		:['Global search : ',{
				xtype			: 'textfield',
				id				: 'mainNZBTxt2search',
				width			: 300,
				enableKeyEvents :true,
				height			: 20,
				listeners		: {
					keypress		: function(ob,e){
						if (e.getCharCode()==13){
							that.addSearchTab({
								mask	: ob.getValue(),
								id		: Ext.id()
							});
						}
					}
				}
			},{
				xtype		: 'button',
				text		: 'search',
				id			: 'btnSearchNZB',
				width		: 100,
				listeners	: {
					click		: function(){
						that.addSearchTab({
							mask	: Ext.getCmp('mainNZBTxt2search').getValue()
						});
					}
				}
			},'->',{
				xtype			: 'combo',
				id				: 'comboSearcher',
				store			: {
					xtype			: 'store',
					fields			: ['it'],
					data			: [{it : 'binsearch'},{it : 'newzleech'}]
				},
				queryMode		: 'local',
				displayField	: 'it',
				valueField		: 'it',
				value			: 'binsearch'
			}],
			items		: [{
				region				: 'center',
				xtype				: 'tabpanel',
				id					: that.tabPanelId,
				frame				: true,
				border				: false,
				activeTab			: 0,
				items				:	[]
			},{
				type				: 'panel',
				stateId				: 'qd.nzb.NZBPanel.feedtab',
				region				: 'west',
				layout				: 'border',
				width				: 610,
				split				: true,
				items				: [{
					region		: 'center',
					id			: that.feedtabid,
					xtype		: 'qd.nzb.feedtab'
				}]
			}]
		});
		this.callParent(arguments);
	}
});