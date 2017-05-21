Ext.define('qd.nzb.nzbview', {
	extend		: 'Ext.Panel',
	alias		: 'widget.qd.nzb.nzbview',
	requires	: ['Ext.ux.SimpleIFrame'],
	initComponent : function() {
		var that = this;
		that.gridid			= Ext.id();
		that.iframeid		= Ext.id();
		that.txtSearchid	= Ext.id();
		that.textSearchId	= Ext.id();
		Ext.define('searchnzb', {
			extend	: 'Ext.data.Model',
			fields	: [
				'id'		,
				'title'		,
				'group'		,
				'percent'	,
				'size'		,
				'age'
			]
		});

		that.nzbstore = Ext.create('Ext.data.Store',{
			model	: 'searchnzb',
			proxy	: {
				type			: 'ajaxEx',
				url				: 'api/NzbProxyFeeds/search',
				reader			: {
					type			: 'json',
					root			: 'posts',
					totalProperty	: 'count'
				}
			},
			sortInfo	:{
				field		: 'age',
				direction	: "ASC"
			},
			listeners	:{
				load		: function(){
					Ext.getCmp(that.gridid).getSelectionModel().selectAll();
				}
			}
		});

		that.searchNZB = function (mask){
			that.nzbstore.removeAll();
			that.nzbstore.proxy.extraParams.q 	= unescapeHTML(mask);
			that.nzbstore.proxy.extraParams.dc	= Math.random();
			that.nzbstore.proxy.extraParams.s	= Ext.getCmp('comboSearcher').getValue();
			that.nzbstore.load();
		}
		var objectHead ={
			region	: 'north',
			id		: that.iframeid,
			split	: true
		};
		if (that.record && that.record.data && that.record.data.DESC_POSTER){
			Ext.apply(objectHead,{
				height		: 100,
				xtype		: 'qd.nzb.feeditemdesc',
				stateId		: 'qd.nzb.nzbview.tplNorth',
				record		: that.record
			});
		}else if (that.link){
			Ext.apply(objectHead,{
				height		: 200,
				xtype		: 'simpleiframe',
				stateId		: 'qd.nzb.nzbview.tplNorth',
				src			: that.link
			});
		}else{
			Ext.apply(objectHead,{
				height		: 0,
				xtype		: 'panel',
				split		: false
			});
		}

		Ext.apply(this, {
			layout		: 'border',
			stateful	: false,
			items		: [objectHead,{
				region		: 'center',
				xtype		: 'grid',
				id			: that.gridid,
				store		: that.nzbstore,
				loadMask	: true,
				tbar 		: [{
					xtype		: 'button',
					text		: 'all',
					width		: 100,
					listeners	: {
						click		: function(){
							Ext.getCmp(that.gridid).getSelectionModel().selectAll();
						}
					}
				},{
					xtype		: 'button',
					text		: 'none',
					width		: 100,
					listener	: {
						click		: function(){
							Ext.getCmp(that.gridid).getSelectionModel().deselectAll();
						}
					}
				},{
					xtype		: 'button',
					text		: 'download',
					width		: 100,
					listeners	: {
						click		: function(){
							var idsString = '';
							var sepa = '';
							var grid = Ext.getCmp(that.gridid);
							var l = grid.getSelectionModel().getSelection();
							Ext.each(l,function(record){
								idsString = idsString + sepa + record.data.id;
								sepa='!';
							});
							that.ldMask = new Ext.LoadMask(that.getEl(), {msg:"Please wait..."});
							that.ldMask.show();
							Ext.AjaxEx.request({
								url		: 'api/NzbProxyFeeds/download',
								params	: {
									ids		: idsString,
									q		: that.q,
									s		: Ext.getCmp('comboSearcher').getValue(),
									rid		: that.record?that.record.get('ITE_ID'):0
								},
								success	: function(r,a){
									that.ldMask.hide();
									eval('var res='+r.responseText);
									that.setTitle('<s>'+that.oTitle+'</s>');
									that.record.set('ITE_TREATED',1);
								}
							});
						}
					}
				},'->',{
					xtype			: 'textfield',
					id				: that.txtSearchid,
					value			: unescapeHTML(that.q),
					width			: 400,
					enableKeyEvents : true,
					listeners		: {
						keypress: function(ob,e){
							if (e.getCharCode()==13){
								that.searchNZB(ob.getValue());
							}
						}
					}
				},{
					text		: 'search',
					handler		: function(){
						that.searchNZB(Ext.getCmp(that.txtSearchid).getValue());
					}
				}],
				forceFit: true,
				selModel	: Ext.create('Ext.selection.CheckboxModel'),
				columns: [
					{header: "size"		, width:  75, dataIndex: 'size'		, sortable: true},
					{header: "Nom"		, width: 400, dataIndex: 'title'	, sortable: true},
					{header: "percent"	, width:  60, dataIndex: 'percent'	, sortable: true},
					{header: "age"		, width:  60, dataIndex: 'age'		, sortable: true},
					{header: "group"	, width:  80, dataIndex: 'group'	, sortable: true}
				]
			}]
		});
		this.on ({
			rowdblclick  : function ( grid, rowIndex, e ){
				var record = grid.getStore().getAt(rowIndex);
			},
			contextclick : function(link){
			}
		});
		this.callParent(arguments);
	}
});