Ext.define('qd.nzb.feedtab', {
    extend		: 'Ext.panel.Panel',
	alias		: 'widget.qd.nzb.feedtab',
	requires	:['qd.nzb.feeditemdesc'],
	qtipTpl		: new Ext.XTemplate(
		'<tpl for=".">',
		'<h3>{ITE_TITLE}:</h3>',
		'<div><i>{ITE_MASK}</i></div>',
		'</tpl>'
	),
	initComponent : function() {
		var that = this;
		that.comboid = Ext.id();
		that.tplid = Ext.id();

		that.filterFeeds=function(prm){
			that.dbfeedStore.proxy.extraParams.mode = 'fullsearch';
			that.dbfeedStore.proxy.extraParams.q = prm.q;
			that.dbfeedStore.load();
		}
		that.rendererTitle = function (val, meta, record, rowIndex, colIndex, store){
				var qtip = that.qtipTpl.apply(record.data);
				return '<div qtip="' + qtip +'" style="'+(record.get('ITE_READ')==0?'font-weight:bold':'')+'">'+ val+'</div>';
		};
		Ext.define('dbfeeds', {
			extend: 'Ext.data.Model',
			fields: [
				'ITE_ID'				,
				'ITE_TITLE'				,
				'ITE_DATE'				,
				'ITE_YEAR'				,
				'ITE_SIZE'				,
				'ITE_MASK'				,
				'ITE_LINK'				,
				'ITE_READ'				,
				'ITE_STARRED'			,
				'ITE_QUALITY'			,
				'ITE_TREATED'			,
				'DESC_SUMMARY'			,
				'DESC_POSTER'			,
				'ITE_LINK_CACHE_SERIAL'	,
				'DESC_GENRE'			,
				'DESC_YEAR'				,
				'DESC_TITLE'			,
				'DESC_DIRECTOR'			,
				'DESC_ACTOR'
			]
		});
		that.dbfeedStore = Ext.create('Ext.data.Store',{
			model		: 'dbfeeds',
			groupField	: 'ITE_DATE',
			sortOnLoad	: true,
			sorters		: [{
				property	: 'ITE_DATE',
				direction	: 'DESC'
			},{
				property	: 'ITE_ID',
				direction	: 'DESC'
			}],
			proxy		: {
				type			: 'ajax',
				url				: 'p/QDNzbProxyFeeds.dbfeed/',
				reader			: {
					type			: 'json',
					root			: 'feeds',
					totalProperty	: 'count'
				}
			}
		});
		Ext.define('feedgroup', {
			extend: 'Ext.data.Model',
			fields: ['GRI_IDX','GRI_TEXT']
		});
		that.feedgroupStore = Ext.create('Ext.data.Store',{
			model		: 'feedgroup',
			proxy		: {
				type			: 'ajax',
				url				: 'p/QDNzbProxyFeeds.distinctfeed/',
				reader			: {
					type			: 'json',
					root			: 'res',
					totalProperty	: 'count'
				}
			},
			listeners		: {
				load			: function(){
					var firstValue = this.data.items[0].get('GRI_IDX');
					Ext.getCmp(that.comboid).setValue(firstValue);
					that.dbfeedStore.proxy.extraParams.is = firstValue;
					that.dbfeedStore.proxy.extraParams.mode = 'feed';
					that.dbfeedStore.load({
						params	:{
							start	:  0,
							limit	: 25
						}
					});
				}
			}
		});
		var groupingFeature = Ext.create('Ext.grid.feature.Grouping',{
			groupHeaderTpl: 'Date: {name} ({rows.length} Item{[values.rows.length > 1 ? "s" : ""]})'
		});
		Ext.apply(this, {
			layout		: 'border',
			border		: false,
			tbar		:['&nbsp;Feeds : ',{
				xtype			: 'combo',
				autoWidth		: true,
				id				: that.comboid,
				displayField	: 'GRI_TEXT',
				valueField 		: 'GRI_IDX',
				allowBlank		: false,
				mode			: 'local',
				store			: that.feedgroupStore,
				listeners		: {
					select			: function(field,record,idx){
						that.dbfeedStore.proxy.extraParams.is = record[0].get('GRI_IDX');
						that.dbfeedStore.load({params:{start:0, limit:25}});
					}
				},
				queryMode: 'local'
			},'&nbsp;Search : ',{
				xtype			: 'textfield',
				id				: 'feedTxt2search',
				width			: 200,
				enableKeyEvents :true,
				height			: 20,
				listeners		: {
					keypress		: function(ob,e){
						if (e.getCharCode()==13){
							that.filterFeeds({q : ob.getValue()})
						}
					}
				}
			}],
			items : [{
				height		: 80,
				id			: that.tplid,
				stateId		: 'qd.nzb.feedtab.tplNorth',
				region		: 'north',
				xtype		: 'qd.nzb.feeditemdesc',
				split		: true
			},{
				region		: 'center',
				xtype		: 'gridpanel',
				border		: false,
				afterRender	: function(){
					Ext.getCmp(that.comboid).store.load();
				},
				store		: that.dbfeedStore,
				forceFit	: true,
				dockedItems	: [{
					xtype		: 'pagingtoolbar',
					store		: that.dbfeedStore,   // same store GridPanel is using
					dock		: 'bottom',
					displayInfo	: true
				}],
				loadMask	: true,
				viewConfig	: {
					forceFit	: true
				},
				features	: [groupingFeature],
				columns		: [{
					header		: ""	, width:  30, dataIndex: 'ITE_STARRED'   , sortable: true,fixed:true,
					renderer	: function (val, meta, record, rowIndex, colIndex, store){
						return '<img src="skins/resources/star_'+(val==0?'grey':'yellow')+'.png" />';
					},
					listeners : {
						click : function(grid,HTMLElement,rowIndex,columnIndex){
							var record = grid.getStore().getAt(rowIndex);
							Ext.Ajax.request({
								url		: 'p/QDNzbProxyFeeds.setStarred/',
								params: {
									ITE_ID		:record.get('ITE_ID'),
									ITE_STARRED	: (record.get('ITE_STARRED')=='0'?1:0)
								},
								success	: function(r,a){
									eval('var res='+r.responseText);
									if (res.ok){
										record.set('ITE_STARRED',res.starred);
									}
								}
							});
						}
					}
				},{
					header: "Year"	, width:  45, dataIndex: 'ITE_YEAR'		, sortable: true	,fixed:true
				},{
					header: "Title"	, width: 230, dataIndex: 'ITE_TITLE'	, sortable: true	,
					listeners : {
						click : function(grid,HTMLElement,rowIndex,columnIndex){
							var record = grid.getStore().getAt(rowIndex);
							Ext.getCmp(that.tplid).setItem(record.data);//tpl.overwrite(Ext.getCmp(that.tplid).body,record.data);
							Ext.Ajax.request({
								url		: 'p/QDNzbProxyFeeds.setRead/',
								success	: function(r,a){
									eval('var res='+r.responseText);
									if (res.ok){
										record.set('ITE_READ',1);
									}
								},
								params: {
									ITE_ID		:record.get('ITE_ID'),
									ITE_READ	: 1
								}
							});
						}
					}
					,renderer : function(val,meta,record){
						return '<span style="'+(record.get('ITE_READ')==0?'font-weight:bold;':'')+(record.get('ITE_TREATED')==1?'text-decoration: line-through;':'')+'">'+ val+'</span>';
					}//	that.rendererTitle.createDelegate(this)
				},{
					xtype	: 'actioncolumn',
					width	: 20,
					items	: [{
						icon	: 'skins/resources/magnifier.png',
						handler	: function(grid, rowIndex, colIndex) {
							var record = grid.getStore().getAt(rowIndex);
							Ext.getCmp('mainNZBPanel').addSearchTab({
								id		: record.get('ITE_ID'	),
								mask	: record.get('ITE_MASK'	),
								link	: record.get('ITE_LINK'	),
								title	: record.get('ITE_TITLE'),
								record	: record
							});
						}
					}]
				},/*{
					header: "Date"		, width:  70, dataIndex: 'ITE_DATE'		, flex : 1
				},*/{
					header: "Size"		, width:  80, dataIndex: 'ITE_SIZE'		, sortable: true,	fixed:true
				},{
					header: "Quality"	, width:  80, dataIndex: 'ITE_QUALITY'	, sortable: true,	fixed:true},{
					xtype	: 'actioncolumn',
					width	: 20,
					items	: [{
						icon	: 'skins/resources/play_blue.png',
						handler	: function(grid, rowIndex, colIndex) {
							var record = grid.getStore().getAt(rowIndex);
							window.open(record.data.ITE_LINK);
						}
					}]
				},{
					header: ""			, width:  30, dataIndex: 'ITE_READ'		, sortable: true,	fixed:true,
					renderer : function (val, meta, record, rowIndex, colIndex, store){
						return '<img src="skins/resources/'+(val==0?'unread':'read')+'.gif" />';
					},
					listeners : {
						click : function(grid,HTMLElement,rowIndex,columnIndex){
							var record = grid.getStore().getAt(rowIndex);
							Ext.Ajax.request({
								url		: 'p/QDNzbProxyFeeds.setRead/',
								success	: function(r,a){
									eval('var res='+r.responseText);
									if (res.ok){
										record.set('ITE_READ',res.read);
										el.removeClass('x-grid3-dirty-cell');
									}
								},
								params: {
									ITE_ID		:record.get('ITE_ID'),
									ITE_READ	: (record.get('ITE_READ')==0?1:0)
								}
							});
						}
					}
				}]
			}]
		});
		this.callParent(arguments);
	}
});