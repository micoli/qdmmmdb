Ext.define('Ext.ux.FileBrowser', {
	extend			: 'Ext.Panel',
	layout			: 'border',
	alias			: 'widget.fileBrowser',
	displayFolder	: true,
	displayFiles	: true,
	root			: '',
	viewFolders		: true,
	viewFiles		: true,
	viewPreview		: true,

	initComponent	: function(){
		var that = this;
		that.gridid		= Ext.id();
		that.treeid		= Ext.id();
		that.treerootid	= Ext.id();
		that.previewid	= Ext.id();
		that.previewhid	= Ext.id();
		that.currentPathId = '';

		this.addEvents(
			'folderclick'	,
			'folderdblclick',
			'fileclick'		,
			'filedblclick'
		);

		Ext.define('fileBrowserPath', {
			extend: 'Ext.data.Model',
			fields: ['text', 'owner', 'cdate','mtime']
		});

		Ext.define('fileBrowserFiles', {
			extend: 'Ext.data.Model',
			fields: ['folder', 'type', 'filename', 'ext', 'cdate', 'mtime', 'size', 'sizef', 'sizefs', 'root', 'owner', 'group', 'perms', 'preview', 'folderfilename','permt','permo','permg','perma']
		});

		that.getIcon = function(iconSize,record){
			var icon='skins/fileBrowser/'+record.type+'_'+iconSize+'.png';
			if(record.type=='img'){
				icon = 'p/QDFilesProxy.getPreview/?type=img&c='+iconSize+'x'+iconSize+'&root='+record.root+'&id='+record.id+'&ext='+record.ext;
			}
			return icon;
		}

		that.treeStore = Ext.create('Ext.data.TreeStore',  {
			model		: 'fileBrowserPath',
			folderSort	: true,
			root		: {
				text		: 'Paths',
				id			: that.treerootid,
				expanded	: true
			},
			proxy		: {
				type		: 'ajaxEx',
				url			: 'p/QDFilesProxy.getTree/',
				timeout		: 240*1000,
				extraParams	:{
					root		: that.root
				}
			},
			listeners	: {
				load		: function( store, records, successful, eOpts ){
				}
			}
		});

		that.gridstore = Ext.create('Ext.data.Store',{
			model	: 'fileBrowserFiles',
			proxy		: {
				type		: 'ajaxEx',
				url			: 'p/QDFilesProxy.getFiles/',
				timeout		: 240*1000,
				extraParams	:{
					root		: that.root
				}
			},
		});

		that.selectTreeNode = function (id){
			if(that.viewFiles){
				that.gridstore.load({
					params	: {
						node	: id
					},
					scope	: this,
					callback: function(records, operation, success) {
						that.currentPathId = id;
						var parentNode = that.treeStore.getNodeById(id);
						parentNode.expand();
					}
				});
			}
		}

		that.setPreview = function(record){
			var preview = Ext.getCmp(that.previewid);
			if(record){
				record.set('folderfilename',record.get('folder')+record.get('filename'));
				preview.loadRecord(record);
				Ext.getCmp(that.previewhid).update(record.get('preview'));
			}else{
				preview.getForm().reset();
				Ext.getCmp(that.previewhid).update('');
			}
		}

		Ext.apply(this,{
			items	:[{
				xtype			: 'treepanel',
				region			: (that.viewFiles)?'west':'center',
				hidden			: (that.viewFolders)?false:true,
				id				: that.treeid,
				store			: that.treeStore,
				width			: 300,
				lines			: true,
				loadMask		: true,
				split			: true,
				rootVisible		: false,
				multiSelect		: false,
				useArrows		: true,
				stateful		: false,
				listeners		: {
					itemclick		: function (treepanel, record, item, index, e, eOpts ){
						that.selectTreeNode(record.get('id'));
						that.fireEvent('folderclick',that,that.root,record);
						that.setPreview();
					},
					itemdblclick		: function (treepanel, record, item, index, e, eOpts ){
						that.fireEvent('folderdblclick',that,that.root,record);
					},
					itemcontextmenu	: function (view, rec, node, index, e){
						e.stopEvent();
						that.ctxMenu.showAt(e.getXY());
						return false;
					}
				},
				/*tbar			: [{
					text			: 'refresh',
					handler			: function(){
						that.treeStore.proxy.extraParams.refresh = 1;
						that.treeStore.getRootNode().removeAll(false);
						that.treeStore.load({
							node: Ext.getCmp(that.treeid).getRootNode()
						});
						that.treeStore.sync();
					}
				}],*/
				columns			:[{
					xtype			: 'treecolumn',
					header			: 'folder',
					width			: 200,
					dataIndex		: 'text',
					flex			: 1
				}]
			},{
				region			: that.viewFiles?'center':'east',
				hidden			: that.viewFiles?false:true,
				layout			: 'border',
				split			: true,
				items			: [{
					region			: 'center',
					xtype			: 'grid',
					split			: true,
					id				: that.gridid,
					store			: that.gridstore,
					plugins			: [Ext.create('Ext.ux.grid.plugin.DragSelector')],
					features		: [Ext.create('Ext.ux.grid.feature.Tileview', {
						viewMode			: 'default',
						getAdditionalData	: function(data, index, record, orig){
							var r = {};
							Ext.apply(r,
								record.data
							);
							r.icon = that.getIcon((this.viewMode=="tileIcons")?48:64,r);
							return r;
						},
						viewTpls			:{
							tileIcons			: [
								'<td class="{cls} ux-explorerview-detailed-icon-row">',
									'<table class="x-grid-row-table">',
										'<tbody>',
											'<tr>',
												'<td class="x-grid-col x-grid-cell ux-explorerview-icon" style="background: url(&quot;{icon}&quot;) no-repeat scroll 50% 50% transparent;">',
												'</td>',
												'<td class="x-grid-col x-grid-cell">',
													'<div class="x-grid-cell-inner" unselectable="on">{filename}</div>',
													'<div class="x-grid-cell-inner" unselectable="on" style="color:grey;">{sizef}</div>',
													'<div class="x-grid-cell-inner" unselectable="on" style="color:grey;">{mtime}</div>',
												'</td>',
											'</tr>',
										'</tbody>',
									'</table>',
								'</td>'
								].join(''),
							mediumIcons			: [
								'<td class="{cls} ux-explorerview-medium-icon-row">',
									'<table class="x-grid-row-table">',
										'<tbody>',
											'<tr>',
												'<td class="x-grid-col x-grid-cell ux-explorerview-icon" style="background: url(&quot;{icon}&quot;) no-repeat scroll 50% 100% transparent;">',
												'</td>',
											'</tr>',
											'<tr>',
												'<td class="x-grid-col x-grid-cell">',
													'<div class="x-grid-cell-inner" unselectable="on">{filename}</div>',
													'<div class="x-grid-cell-inner" unselectable="on" style="color:grey;">{sizef}</div>',
												'</td>',
											'</tr>',
										'</tbody>',
									'</table>',
								'</td>'
							].join('')
						}
					}),{
						ftype			: 'grouping',
						groupHeaderTpl	: 'Group: {name} ({rows.length} Item{[values.rows.length > 1 ? "s" : ""]})',
						disabled		: false
					}],
					listeners		: {
						itemclick		: function(grid, record, item, index, e, eOpts ){
							that.setPreview(record);
							that.fireEvent('fileclick',that,that.root,record);
						},
						itemdblclick	: function(grid, record, item, index, e, eOpts ){
							that.fireEvent('filedblclick',that,that.root,record);
							if(record.get('type')=='folder'){
								var nodeId = record.get('id');
								var selected = that.treeStore.getNodeById(nodeId);
								if(selected){
									selected.expand();
									Ext.getCmp(that.treeid).selectPath(selected.getPath());
									that.selectTreeNode(nodeId)
								}
							}
						}
					},
					tbar: [{
						xtype	: 'button',
						text	: '..',
						handler	: function(){
							var parentNode = that.treeStore.getById(that.currentPathId).parentNode;
							if(parentNode){
								that.selectTreeNode(parentNode.getId());
							}
						}
					},'-','->', {
						xtype		: 'switchbuttonsegment',
						activeItem	: 0,
						scope		: this,
						items		: [{
							tooltip		: 'Details',
							viewMode	: 'default',
							iconCls		: 'icon-default'
						}, {
							tooltip		: 'Tiles',
							viewMode	: 'tileIcons',
							iconCls		: 'icon-tile'
						}, {
							tooltip		: 'Icons',
							viewMode	: 'mediumIcons',
							iconCls		: 'icon-medium'
						}],
						listeners: {
							change: function(btn, item){
								Ext.getCmp(that.gridid).features[0].setView(btn.viewMode);
							},
							scope: this
						}
					}],
					columns			: [{
						header: "&nbsp;"	,width:  30,	dataIndex: 'type'		, fixed :true	,sortable: true,renderer : function(v,meta,record){
							var tclass='';
							if(record.get('type')=='folder'){
								tclass='x-tree-icon-parent';
								return '<span class="'+tclass+'" style="width:16px;height:16px;display:block;">&nbsp;</span>';
							}else{
								var icon = that.getIcon(16,record.data);
								return '<span style="width:16px;height:16px;display:block;background: url(&quot;'+icon+'&quot;) no-repeat scroll 100% 100% transparent;">&nbsp;</span>';
							}
						}
					},{
						header: "Name"		,width: 200,	dataIndex: 'filename'	, flex	:1	,sortable: true
					},{
						header: "Ext"		,width:  50,	dataIndex: 'ext'		, flex	:0	,sortable: true,renderer : function(v,meta,record){
							meta.style='text-align:right;';
							return v;
						}
					},{
						header: "Size"		,width:  70,	dataIndex: 'sizef'		, flex	:0	,sortable: true,renderer : function(v,meta,record){
							meta.style='text-align:right;';
							return record.get('type')=='folder'?'':v;
						}
					},{
						header: "Modified"	,width: 140,	dataIndex: 'mtime'		, flex	:0	,sortable: true,renderer : function(v,meta,record){
							meta.style='text-align:right;';
							return v;
						}
					}]
				},{
					region		:'south',
					id			: that.previewid,
					hidden		: that.viewPreview?false:true,
					height		: 140,
					xtype		: 'form',
					collapsible	: true,
					layout		: 'border',
					padding		: '5 5 5 5',
					border		: false,
					bodyStyle	: {
						'background-color': '#DFE9F6'
					},
					items		: [{
						region		: 'north',
						name		: 'folderfilename',
						xtype		: 'textfield',
						readOnly	: true,
						fieldLabel	: 'File',
						border		: false,
						labelWidth	: 40,
						height		: 30
					},{
						region		: 'west',
						width		: 200,
						layout		: 'absolute',
						border		: false,
						bodyStyle	: {
							'background-color': '#DFE9F6'
						},
						defaults	: {
							xtype		: 'textfield',
							labelWidth	: 60,
						},
						items		: [{
							y			: 0,
							readOnly	: true,
							fieldLabel	: 'Size',
							name		: 'sizef',
							anchor		: '100%'
						},{
							y			: 25,
							width		: 125,
							readOnly	: true,
							fieldLabel	: 'Owner',
							name		: 'owner'
						},{
							y			: 25,
							x			: 130,
							hidelabel	: true,
							readOnly	: true,
							name		: 'group',
							anchor		: '100%'
						},{
							y			: 50,
							readOnly	: true,
							fieldLabel	: 'Perms',
							name		: 'permt',
							width		: 80
						},{
							y			: 50,
							x			: 85,
							readOnly	: true,
							hidelabel	: true,
							name		: 'permo',
							width		: 30
						},{
							y			: 50,
							x			: 120,
							readOnly	: true,
							hidelabel	: true,
							name		: 'permg',
							width		: 30
						},{
							y			: 50,
							x			: 155,
							readOnly	: true,
							hidelabel	: true,
							name		: 'perma',
							anchor		: '100%'
						},{
							y			: 75,
							readOnly	: true,
							fieldLabel	: 'Time',
							name		: 'mtime',
							anchor		: '100%'
						}]
					},{
						region		: 'center',
						border		: false,
						border		: false,
						bodyStyle	: {
							'background-color': '#DFE9F6'
						},
						id			: that.previewhid,
						name		: 'preview'
					}]
				}]
			}]
		});
		this.callParent(arguments);
		that.selectTreeNode(that.treerootid);
	},
});
