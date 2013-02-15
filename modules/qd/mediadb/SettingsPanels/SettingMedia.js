Ext.define('qd.mediadb.SettingsPanels.SettingMedia', {
	extend		: 'MyDesktop.SettingsPanels',
	settingId	: 'Settingmedia',
	title		: 'Media',

	onBarSubItemsSelect	: function(tree, record) {
		var that = this;
		var panels = Ext.getCmp(that.mainid);
		panels.items.each(function(v,k){
			if(record.get('params')==v.cardId){
				panels.getLayout().setActiveItem(k);
				if(v.store){
					v.store.removeAll();
					v.store.load();
				}
				return false;
			}
		})
	},

	constructor	:	function(cfg){
		var that					= this;
		that.mainid					= Ext.id();

		Ext.define('config.qdmediadb_movie.folderMoviesList', {
			extend	: 'Ext.data.Model',
			config	: {
				configType	: 'object',
				columns		: [{
					header: "name"		,width:  70,	dataIndex: 'name'		, flex	:1	,sortable: true
				},{
					header: "path"		,width: 200,	dataIndex: 'path'		, flex	:1	,sortable: true
				},{
					header: "xbmcpath"	,width: 200,	dataIndex: 'xbmcpath'	, flex	:1	,sortable: true
				}]
			},
			fields	: [
				'name'		,
				'path'		,
				'xbmcpath'
			]
		});

		Ext.define('config.qdmediadb_serie.folderSeriesList', {
			extend	: 'Ext.data.Model',
			config	: {
				configType	: 'object',
				columns		: [{
					header: "name"		,width:  70,	dataIndex: 'name'		, flex	:1	,sortable: true
				},{
					header: "path"		,width: 200,	dataIndex: 'path'		, flex	:1	,sortable: true
				},{
					header: "xbmcpath"	,width: 200,	dataIndex: 'xbmcpath'	, flex	:1	,sortable: true
				}]
			},
			fields	: [
				'name'		,
				'path'		,
				'xbmcpath'
			]
		});

		Ext.define('config.qdmediadb.allowedExt', {
			extend	: 'Ext.data.Model',
			config	: {
				configType	: 'array',
				columns		: [{
					header: "Value"		,width:  70,	dataIndex: 'v'		, flex	:1	,sortable: true
				}]
			},
			fields	: ['v']
		});

		Ext.define('config.qdmediadb.movieExt', {
			extend	: 'Ext.data.Model',
			config	: {
				configType	: 'array',
				columns		: [{
					header: "Value"		,width:  70,	dataIndex: 'v'		, flex	:1	,sortable: true
				}]
			},
			fields	: ['v']
		});

		Ext.define('config.qdmediadb.arrKeepSpecialTag', {
			extend	: 'Ext.data.Model',
			config	: {
				configType	: 'object',
				columns		: [{
					header: "Regex"		,width:  70,	dataIndex: 'rgx'		, flex	:1	,sortable: true
				},{
					header: "Replace"	,width:  70,	dataIndex: 'rep'		, flex	:1	,sortable: true
				},{
					header: "Multiple"	,width:  40,	dataIndex: 'multiple'	, flex	:0	,sortable: true
				}]
			},
			fields	: [
			'rgx',
			'rep',
			'multiple'
			]
		});

		Ext.define('config.qdmediadb.arrCleanupMoviesRegexStrict', {
			extend	: 'Ext.data.Model',
			config	: {
				configType	: 'object',
				columns		: [{
					header: "Regex"		,width:  70,	dataIndex: 'rgx'		, flex	:1	,sortable: true
				},{
					header: "Replace"	,width:  70,	dataIndex: 'rep'		, flex	:1	,sortable: true
				}]
			},
			fields	: [
			'rgx',
			'rep'
			]
		});

		Ext.define('config.qdmediadb.arrCleanupMoviesRegex', {
			extend	: 'Ext.data.Model',
			config	: {
				configType	: 'object',
				columns		: [{
					header: "Regex"		,width:  70,	dataIndex: 'rgx'		, flex	:1	,sortable: true
				},{
					header: "Replace"	,width:  70,	dataIndex: 'rep'		, flex	:1	,sortable: true
				}]
			},
			fields	: [
			'rgx',
			'rep'
			]
		});

		Ext.define('config.qdmediadb.arrRegex', {
			extend	: 'Ext.data.Model',
			config	: {
				configType	: 'object',
				columns		: [{
					header: "Regex"			,width:  70,	dataIndex: 'rgx'		, flex	:1	,sortable: true
				},{
					header: "Sea.Pos"	,width:  40,	dataIndex: 's'			, flex	:0	,sortable: true
				},{
					header: "Epi.Pos"	,width:  40,	dataIndex: 's'			, flex	:0	,sortable: true
				}]
			},
			fields	: [
			'rgx',
			's',
			'e'
			]
		});

		Ext.define('config.qdmediadb.testFilenames', {
			extend	: 'Ext.data.Model',
			config	: {
				configType	: 'array',
				columns		: [{
					header: "Value"		,width:  70,	dataIndex: 'v'		, flex	:1	,sortable: true
				}]
			},
			fields	: ['v']
		});

		that.configsId		= {};
		that.configCards	= [];


		for(var k in Ext.ModelManager.types){
			var mdl = Ext.ModelManager.types[k];
			var modelName = mdl.getName();
			if(/^config\./.test(modelName)){
				var cardName = modelName.replace(/^config\./,'');
				that.configsId[cardName] = Ext.id();
				that.configCards.push({
					xtype			: 'grid',
					cardId			: cardName,
					region			: 'center',
					id				: that.configsId[cardName],
					store			: that.createConfigStore(cardName),
					columns			: mdl.prototype.config.columns
				});
			}
		}

		that.barSubItems = {
			text		: '',
			expanded	: true,
			children	: [{
				text		: 'Paths',
				expanded	: true,
				children	: [{
					text		: "Movies",
					iconCls		: '',
					leaf		: true,
					params		: 'qdmediadb_movie.folderMoviesList'
				},{
					text		: "Series",
					iconCls		: '',
					leaf		: true,
					params		: 'qdmediadb_serie.folderSeriesList'
				}]
			},{
				text		: 'Extensions',
				expanded	: true,
				children	: [{
					text		: "AllowExt",
					iconCls		: '',
					leaf		: true,
					params		: 'qdmediadb.allowedExt'
				},{
					text		: "MovieExt",
					iconCls		: '',
					leaf		: true,
					params		: 'qdmediadb.movieExt'
				}]
			},{
				text		: 'Regexp &amp; tags',
				expanded	: true,
				children	: [{
					text		: "Keep Special Tag",
					iconCls		: '',
					leaf		: true,
					params		: 'qdmediadb.arrKeepSpecialTag'
				},{
					text		: "Cleanup Movies Regex",
					iconCls		: '',
					leaf		: true,
					params		: 'qdmediadb.arrCleanupMoviesRegex'
				},{
					text		: "Cleanup Movies Regex Strict",
					iconCls		: '',
					leaf		: true,
					params		: 'qdmediadb.arrCleanupMoviesRegexStrict'
				},{
					text		: "Season Regexp",
					iconCls		: '',
					leaf		: true,
					params		: 'qdmediadb.arrRegex'
				},{
					text		: "Season testFilenames",
					iconCls		: '',
					leaf		: true,
					params		: 'qdmediadb.testFilenames'
				}]
			}]
		}

		that.main = {
			layout	: 'card',
			id		: that.mainid,
			items	: that.configCards,
			bbar : ['->',{
				xtype	: 'button',text: 'OK'		, handler: that.onOK							, scope: that
			},{
				xtype	: 'button',text: 'Cancel'	, handler: that.setBarSubItemsInitialSelection	, scope: that
			}]
		}
		that.callParent(that);
	}
},function(){
	this.superclass.self.registerPanel(this.$className);
});