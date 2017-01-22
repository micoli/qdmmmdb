Ext.define('qd.mediadb.xbmcDBSeriesPanel', {
	extend			: 'Ext.Panel',
	alias			: 'widget.qd.mediadb.xbmcDBSeriesPanel',

	initComponent	: function() {
		var that = this;
		that.seasonshowgridid	= Ext.id();
		that.episodeListgridid	= Ext.id();
		that.tvdbid=0;
		that.season=0;

		var seasonString = function(n) {
			z = '0';
			n = n + '';
			return n.length >= 2 ? n : new Array(2 - n.length + 1).join(z) + n;
		}

		var episodeSeasonBasicFields=['tvdbid','episodeNumber'];
		var columnModelModel = [{header: "Episode"	, width:  50,	dataIndex: 'episodeNumber', sortable: true,locked:true}];

		Ext.define('episodeSeason', {
			extend: 'Ext.data.Model',
			fields: episodeSeasonBasicFields
		});

		Ext.define('episodeList', {
			extend: 'Ext.data.Model',
			fields: [
				'episode_title'				,
				'episode_date'				,
				'episode_season'			,
				'episode_episode'			,
				'episode_fullfilename'		,
				'episode_filename'			,
				'episode_path'				,
				'key'						,
				'lang'
			]
		});

		that.episodeSeasonStore = Ext.create('Ext.data.Store',{
			pruneModifiedRecords: true,
			model				: 'episodeSeason',
			autoLoad			: false,
			proxy				: {
				type				: 'ajaxEx',
				url					: 'p/QDXbmcSeries.getShowsSeasons/',
				reader				: {
					type				: 'json',
					root				: 'data'
				}
			}
		});

		that.episodeListStore = Ext.create('Ext.data.Store',{
			pruneModifiedRecords: true,
			model				: 'episodeList',
			autoLoad			: false,
			proxy				: {
				type				: 'ajaxEx',
				url					: 'p/QDXbmcSeries.getEpisodeList/',
				reader				: {
					type				: 'json',
					root				: 'data'
				}
			}
		});

		var episodeRenderer = function (v,metaData, record, row, col, store, gridView){
			var val = '';
			var sepa = '';
			var first=true;
			if(v.exists){
				val=val+'<div>'+v.episode_formatted_number+':&nbsp;'+v.title+'</div>';
				Ext.each(v.files,function(episode){
					val = val+sepa+'<b>'+episode.key+'</b>:<div class="genericLanguageIcon16 icon-16-language-'+episode.lang+'"></div>';
					sepa=', ';
				});
			}else{
				metaData.style='background:#C8C8C8;';
			}
			return val;
		}

		that.getTvShow = function(tvdbid){
			if(tvdbid){
				that.tvdbid=tvdbid;
				that.episodeSeasonStore.proxy.extraParams.tvdbid=that.tvdbid;
				that.episodeListStore.proxy.extraParams.tvdbid=that.tvdbid;
			}
			that.mask('loading conf');
			Ext.Ajax.request({
				url		: 'p/QDXbmcSeries.preGetShowSeasons/?tvdbid='+that.tvdbid,
				success	: function(response, opts) {
					var serieData			= JSON.parse(response.responseText)
					var model				= Ext.apply([],episodeSeasonBasicFields);
					that.currentColumnModel	= Ext.apply([],columnModelModel);
					for(var i=1;i<=parseInt(serieData.nbSeasons);i++){
						model.push('season_'+seasonString(i));
						that.currentColumnModel.push({
							header		: i,
							width		: 170,
							dataIndex	: 'season_'+seasonString(i),
							renderer	: episodeRenderer,
							sortable	: true
						});
					}
					episodeSeason.setFields(model);
					Ext.getCmp(that.seasonshowgridid).reconfigure(that.episodeSeasonStore,that.currentColumnModel);
					that.unmask();
					that.episodeSeasonStore.removeAll();
					that.episodeSeasonStore.load();
				},
				failure	: function(response, opts) {
					that.unmask();
					console.log('server-side failure with status code ' + response.status);
				}
			});
		}

		that.getEpisodes = function(season){
			if (season){
				that.season=season;
			}
			that.episodeListStore.proxy.extraParams.season=that.season;
			that.episodeListStore.removeAll();
			that.episodeListStore.load();
		}

		Ext.apply(this,{
			layout		: 'border',
			border		: false,
			stateful	: false,
			items		: [{
				xtype			: 'grid',
				region			: 'center',
				id				: that.seasonshowgridid,
				store			: that.episodeSeasonStore,
				tbar			:[{
					xtype			: 'button',
					text			: 'reload',
					handler			: function(){
						that.getTvShow();
					}
				}],
				gridView		: {
					stripeRows		: true
				},
				viewConfig: {
					getRowClass: function( record, index, rowParams, store ){
						return 'seasonHeight';
					},
					listeners: {
						beforecellmousedown: function(view, cell, cellIdx, record, row, rowIdx, eOpts){
							var column=(''+that.currentColumnModel[cellIdx+1].dataIndex);
							if(/season_/.test(column)){
								that.getEpisodes(column.replace(/season_/,''));
							}
						}
					}
				},
				columns			: columnModelModel,
			},{
				xtype			: 'grid',
				region			: 'east',
				split			: true,
				width			: 600,
				id				: that.episodeListgridid,
				store			: that.episodeListStore,
				tbar			:[{
					xtype			: 'button',
					text			: 'reload',
					handler			: function(){
						that.episodeListStore.removeAll();
						that.episodeListStore.load();
					}
				}],
				gridView		: {
					stripeRows		: true
				},
				columns			: [{
					header		: 'lang'	,dataIndex	: 'lang'			,width	: 30,renderer:function(v){
						return '<span class="genericLanguageIcon16 icon-16-language-'+v+'"></span>';
					}
				},{
					header		: 'key'		,dataIndex	: 'key'				,width	: 30,renderer:function(v){
						return '<b>'+v+'</b>';
					}
				},{
					header		: 'S'		,dataIndex	: 'episode_season'	,width	: 30
				},{
					header		: 'E'		,dataIndex	: 'episode_episode'	,width	: 30
				},{
					header		: 'title'	,dataIndex	: 'episode_title'	,width	:250, flex:0
				},{
					header		: 'path'	,dataIndex	: 'episode_path'	,width	:100, flex:1
				}]
			}]
		});
		this.callParent(this);
		/*Ext.getCmp(that.seasonshowgridid).on('cellclick',function(){
			console.log(arguments)
		});*/
	}
});