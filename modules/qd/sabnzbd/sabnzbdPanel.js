Ext.define('qd.sabnzbd.sabnzbdPanel', {
	extend		: 'Ext.grid.Panel',
	alias		: 'widget.qd.sabnzbd.sabnzbdPanel',
	autoTitle	: false,

	sendCommand : function(prm,cb){
		var ob ={
			url		: 'api/Sabnzbd/action',
			method	: 'POST',
			params	: {}
		};
		Ext.apply(ob.params,prm);
		if (cb){
			ob.success = cb;
			ob.failure = cb;
		}
		Ext.AjaxEx.request(ob);
	},

	initComponent : function() {
		var that = this;
		that.combospeedid = Ext.id();
		that.statusid = Ext.id();

		Ext.define('queueitem', {
			extend: 'Ext.data.Model',
			fields: [
				'timeleft'	,
				'mb'		,
				'priority'	,
				'precent'	,
				'msgid'		,
				'filename'	,
				'mbleft'	,
				'id'		,
				'nzo_id'	,
				'icondwn'	,
				{name : 'percentage', type: 'int'},
			]
		});
		that.timerTick = null;
		that.queueitemStore = Ext.create('Ext.data.Store',{
			model		: 'queueitem',
			autoLoad	: true,
			proxy		: {
				type			: 'ajaxEx',
				url				: 'api/Sabnzbd/action',
				extraParams		: {
					sab_mode		: 'queue',
					obj_return		: 'queue'
				},
				reader			: {
					type			: 'json',
					root			: 'slots',
					totalProperty	: 'count'
				}
			},
			listeners	: {
				update : function( store, record, operation ){
					if (operation == 'edit' && record.dirty && record.modified.priority){
						var prio = {
							'Force'		:  2,
							'High'		:  1,
							'Normal'	:  0,
							'Low'		: -1
						};
						console.log(prio[record.data.priority]);
						that.sendCommand({
							sab_mode	: 'queue',
							sab_name	: 'priority',
							sab_value	: record.data.nzo_id,
							sab_value2	: prio[record.data.priority]
						},function(a,b,c){
							that.queueitemStore.load();
						});
					}
					return true;
				},
				load		: function ( store, records ){
					if(Ext.getCmp(that.statusid)){
						var txt = sprintf('%02d KB/s %s',store.proxy.reader.jsonData.kbpersec,store.proxy.reader.jsonData.timeleft);
						Ext.getCmp(that.statusid).setText(store.proxy.reader.jsonData.status+' '+txt);
						if (that.autoTitle){
							if(that.ownerCt && that.ownerCt.isWindow && that.ownerCt.setTitle){
								that.ownerCt.setTitle('Sabnzbd&nbsp;'+txt);
							}else{
								that.setTitle('Sabnzbd&nbsp;'+txt);
							}
							qd.sabnzbd.sabnzbdPanel.statusText = txt;
						}
					}
					clearTimeout(that.timerTick);
					that.timerTick = setTimeout(function(){
						that.queueitemStore.load();
					}, 5*1000);
				}
			}
		});

		that.speedStore = Ext.create('Ext.data.Store',{
			model	: Ext.define('priority', {
				extend	: 'Ext.data.Model',
				fields	: ['s']
			}),
			autoLoad	: true,
			proxy		: {
				type			: 'ajaxEx',
				url				: 'api/Sabnzbd/getSpeeds',
				extraParams		: {
					sab_mode		: 'queue',
					obj_return		: 'queue'
				},
				reader			: {
					type			: 'json',
					root			: 'speed',
					totalProperty	: 'count'
				}
			},
			sortInfo		: {
				field			: 's',
				direction		: "ASC"
			},
			listeners		: {
				load			: function(){
					var firstValue = this.data.items[0].data.s;
					if(Ext.getCmp(that.statusid)){
						Ext.getCmp(that.combospeedid).setValue(firstValue);
					}
				}
			}
		});

		var cellEditing = Ext.create('Ext.grid.plugin.CellEditing', {
			clicksToEdit: 1
		});

		Ext.apply(this, {
			store		: that.queueitemStore,
			loadMask	: true,
			tbar		: [{
				xtype		: 'button',
				text		: 'Refresh',
				listeners	: {
					click	: function(){
						that.queueitemStore.load();
					}
				}
			},{
				xtype		: 'button',
				text		: 'Pause',
				listeners	: {
					click	: function(){
						that.sendCommand({
							sab_mode : 'pause'
						});
					}
				}
			},{
				xtype		: 'button',
				text		: 'Resume',
				listeners	: {
					click	: function(){
						that.sendCommand({
							sab_mode : 'resume'
						});
					}
				}
			},{
				xtype		: 'button',
				text		: 'stop',
				listeners	: {
					click	: function(){
						that.sendCommand({
							sab_mode : 'stop'
						});
					}
				}
			},'->',{
				id			: that.statusid,
				text		: '',
				disabled	: true
			},{
				xtype			: 'combo',
				width			: 50,
				id				: that.combospeedid,
				displayField	: 's',
				valueField 		: 's',
				allowBlank		: false,
				queryMode		: 'local',
				store			: that.speedStore,
				listeners			: {
					select				: function(field,record,idx){
						that.sendCommand({
							sab_mode	: 'config',
							sab_name	: 'speedlimit',
							sab_value	: record[0].data.s
						});
					}
				},
				forceSelection		: true,
				triggerAction		: 'all'
			}],
			viewConfig	: {
				forceFit	: true
			},
			plugins		: [cellEditing],
			selModel	: {
				selType		: 'cellmodel'
			},
			columns		: [
				{header: "Left"			, width: 122, dataIndex: 'percentage'	, sortable: true	,fixed:true,resizable : false,align	:	'right',
					renderer : function (val, meta, record, rowIndex, colIndex, store){
						var percent = record.data.percentage;
						var width = this.columns[0].getWidth();
						var bar = 4-parseInt(percent/25);
						meta.style = meta.style+";background-position: "+(percent==0?-120:(width*percent/100)-120)+"px 50%; background-image: url('skins/resources/progressbar/percentImage_back"+bar+".png'); background-repeat:no-repeat;";

						return sprintf('%s - %02d%%',record.data.timeleft,record.data.percentage);
					}
				},
				{header: "Filename"		, width: 400, dataIndex: 'filename'		, sortable: true	},
				{header: "Priority"		, width: 100, dataIndex: 'priority'		, sortable: true	/*,renderer: Ext.ux.renderer.Combo(priorityCombo), editor: priorityCombo,cellActions:[{
					iconIndex:'icondwn'
				}]*/
					,editor: {
						xtype			: 'combobox',
						typeAhead		: true,
						triggerAction	: 'all',
						selectOnTab		: true,
						lazyRender		: true,
						listClass		: 'x-combo-list-small',
						store			: [
							['Force'	,'Force'	],
							['High'		,'High'		],
							['Normal'	,'Normal'	],
							['Low'		,'Low'		]
						]
					}
				},{
					xtype	: 'actioncolumn',
					width	: 20,
					items	: [{
						icon	: 'skins/resources/pause_blue.png',
						getClass: function(value,metadata,record){
							var closed = record.get('icondwn');
							if (closed == 'play' ) {
								return 'x-hide-display';
								} else {
								return 'x-grid-center-icon';
								}
						},
						handler	: function(grid, rowIndex, colIndex) {
							var record = grid.getStore().getAt(rowIndex);
							that.sendCommand({
								sab_mode	: 'queue',
								sab_name	: 'resume',
								sab_value	: record.get('nzo_id')
							},function(a,b,c){
								that.queueitemStore.load();
							});
						}
					}]
				},{
					xtype	: 'actioncolumn',
					width	: 20,
					items	: [{
						icon	: 'skins/resources/play_blue.png',
						getClass: function(value,metadata,record){
							var closed = record.get('icondwn');
							if (closed == 'pause' ) {
								return 'x-hide-display';
								} else {
								return 'x-grid-center-icon';
								}
						},
						handler	: function(grid, rowIndex, colIndex) {
							var record = grid.getStore().getAt(rowIndex);
							that.sendCommand({
								sab_mode	: 'queue',
								sab_name	: 'pause',
								sab_value	: record.get('nzo_id')
							},function(a,b,c){
								that.queueitemStore.load();
							});
						}
					}]
				},
				{header: "MbLeft"		, width: 100, dataIndex: 'mbleft'		, sortable: true	,align	:	'right',fixed:true,resizable : false,
					renderer : function (val, meta, record, rowIndex, colIndex, store){
						return sprintf("%2.02f/%2.02f",record.get('mb')-record.get('mbleft'),record.get('mb'));
					}
				}
			]
		});
		this.callParent(this);
	}
});