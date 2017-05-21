Ext.define('qd.mediadb.xbmcDB', {
	extend			: 'Ext.Panel',
	alias			: 'widget.qd.mediadb.xbmcDB',

	initComponent	: function() {
		var that = this;
		that.showgridid	= Ext.id();
		that.episodegridid	= Ext.id();

		Ext.define('xbmcserie', {
			extend: 'Ext.data.Model',
			fields: [
				'tvdbid'	,
				'title'		,
				'date'		,
				'paths'		,
				'idShows'	,
				'idPaths'	,
				'nbEpisode'
			]
		});

		that.xbmcserieStore = Ext.create('Ext.data.Store',{
			pruneModifiedRecords: true,
			model				: 'xbmcserie',
			autoLoad			: true,
			proxy				: {
				type				: 'ajaxEx',
				url					: 'api/XbmcSeries/getShows',
				reader				: {
					type				: 'json',
					root				: 'data'
				}
			}
		});

		Ext.apply(this,{
			layout		: 'border',
			border		: false,
			stateful	: false,
			items		: [{
				xtype			: 'grid',
				region			: 'west',
				id				: that.showgridid,
				width			: 300,
				loadMask		: true,
				autoFit			: true,
				clicksToEdit	: 1,
				store			: that.xbmcserieStore,
				tbar			:[{
					xtype			: 'button',
					text			: 'reload',
					handler			: function(){
						that.xbmcserieStore.removeAll();
						that.xbmcserieStore.reload();
					}
				}],
				listeners		:{
					itemclick		: function (grid, record, item, index, e, eOpts ){
						Ext.getCmp(that.episodegridid).getTvShow(record.get('tvdbid'));
					}
				},
				columns			: [
					{header: "show"		, flex :  1,	dataIndex: 'title'			, sortable: true},
					{header: "date"		, width:  90,	dataIndex: 'date'			, sortable: true},
					{header: "nb"		, width:  50,	dataIndex: 'nbEpisode'		, sortable: true},
				]
			},{
				xtype			: 'qd.mediadb.xbmcDBSeriesPanel',
				region			: 'center',
				id				: that.episodegridid,
				loadMask		: true
			}]
		});
		this.callParent(this);
	}
});