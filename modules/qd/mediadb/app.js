/*
var appItems = [
	{title		: 'Main'		,xtype	: 'panel'					,html	: '<div style="width:100%;height:100%;background:url(skins/resources/xbmc-logo.png);background-position:center -90px;background-repeat:no-repeat;background-color : black;text-color:white;">&nbsp;</div>'},
	{title		: 'NZB'			,xtype	: 'qd.nzb.NZBPanel'			,id		: 'mainNZBPanel'	},
	{title		: 'SabNzbd'		,xtype	: 'qd.sabnzbd.sabnzbdPanel'	,autoTitle	: true			},
	{title		: 'Series'		,xtype	: 'qd.mediadb.seriePanel'								},
	{title		: 'Movies'		,xtype	: 'qd.mediadb.moviePanel'								}
];

Ext.each(QD_GBL_CONF.mediadb.helperSite,function(item){
	appItems.push({
		title		: item.title	,
		xtype		: 'simpleiframe',
		bodyBorder	: false,
		src			: item.url,
		closable	: true
	});
});
*/

var appItems=[];
Ext.globalHandlerCounter = {
	'img'	: 0,
	'xhr'	: 0
}
Ext.globalAjaxHandler = function(event,request){
	if(event == 'imagestart'||event == 'imageend'){
		Ext.globalHandlerCounter['img'] += (event=="imagestart")?1:-1;

	}else{
		Ext.globalHandlerCounter['xhr'] += (event=="start")?1:-1;
	}
	var cmp = Ext.getCmp('qd.mediadb.ajaxCount')
	if(cmp) cmp.setText(''+Ext.globalHandlerCounter['xhr']+'/'+Ext.globalHandlerCounter['img']);
}

Ext.define('qd.mediadb.app', {
	extend		: 'Ext.container.Viewport',
	layout		: 'border',
	id			: 'qd.mediadb.appid',
	border		: false,
	requires	: [
		'Ext.ux.SimpleIFrame'		,
		'qd.nzb.NZBPanel'			,
		'qd.sabnzbd.sabnzbdPanel'	,
		'qd.mediadb.seriePanel'		,
		'qd.mediadb.serieFileSorter',
		'qd.mediadb.moviePanel'		,
	],
	items	:[{
		xtype		: 'tabpanel',
		activeTab	: 4,
		region		: 'center',
		items		: appItems
	},{
		xtype		: 'toolbar',
		region		: 'south',
		frame		: true,
		border		: false,
		items		: ['->',{
			xtype		: 'button',
			id			: 'qd.mediadb.ajaxCount',
			text		: '0',
			value		: 0
		}]
	}],
	listeners	: [{
		activate	:function(){
		}
	}]
});

if(false){
	setTimeout(function(){
		Ext.create('qd.mediadb.movieQTPreview', {
			filename	: '/mnt/Q/__Films/Colombianaa.avi'
		}).show();
	},200);
}


//var VLCPlayerPanel = null;
/*Ext.override(Ext.grid.GridView, {
	templates: {
			cell: new Ext.Template(
									'<td class="x-grid3-col x-grid3-cell x-grid3-td-{id} {css}" style="{style}" tabIndex="0" {cellAttr}>',
									'<div class="x-grid3-cell-inner x-grid3-col-{id}" {attr}>{value}</div>',
									"</td>"
					)
	}
});*/
////wVLCPlayerPanel=null;