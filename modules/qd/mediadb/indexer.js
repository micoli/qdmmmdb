Ext.define('qd.mediadb.indexer', {
	extend		: 'Ext.Panel',
	alias		: 'widget.qd.mediadb.indexer',
	border		: false,
	timer		: null,

	initComponent : function() {
		var that			= this;
		that.gridid			= Ext.id();
		that.searchfieldid	= Ext.id();
		that.displayfieldid	= Ext.id();
		that.filetreeid		= Ext.id();

		Ext.define('files', {
			extend	: 'Ext.data.Model',
			fields	: ['FIL_FOLDER','FIL_FILE']
		});

		Ext.define('tfiles', {
			extend	: 'Ext.data.Model',
			fields	: ['text','fullfilename']
		});

		that.fileStore = Ext.create('Ext.data.Store',{
			model				: 'files',
			pruneModifiedRecords: true,
			proxy				: {
				type				: 'ajaxEx',
				url					: 'api/Indexer/getFiles',
				reader				: {
					type				: 'json',
					root				: 'results',
					totalProperty		: 'count'
				}
			}
		});

		that.treeFileStore = Ext.create('Ext.data.TreeStore',  {
			model		: 'tfiles',
			folderSort	: true,
			autoLoad	: false,
			proxy		: {
				type		: 'memory',
				/*type		: 'ajax',
				url			: 'api/Indexer/getFiles?mode=tree',
				reader		: {
					type		: 'json',
				}*/
			},
		});

		that.launchSearch = function(){
			var txt = Ext.getCmp(that.searchfieldid).getValue();
			that.fileStore.removeAll();
			that.fileStore.load({
				params: {
					search : txt
				}
			});
			//*/
			that.treeFileStore.setRootNode({text:''});
			Ext.Ajax.request({
				url		: 'api/Indexer/getFiles?mode=tree&search='+txt,
				success	: function(response, opts) {
					var obj = Ext.decode(response.responseText);
					that.treeFileStore.setRootNode(obj.root);
				},
				failure	: function(response, opts) {
					console.log('server-side failure with status code ' + response.status);
				}
			});
			/*/
			that.treeFileStore.load({
				params: {
					search : txt
				}
			});/*/
		}

		Ext.apply(this, {
			layout		: 'border',
			tbar		:[{
				xtype			: 'label',
				text			: 'Search',
			},{
				xtype			: 'textfield',
				width			: 200,
				value			: '',
				enableKeyEvents	: true,
				id				: that.searchfieldid,
				listeners		: {
					keyup			: function(field, event){
						if(event.getKey()==13){
							that.launchSearch();
						}
					}
				}
			},{
				xtype			: 'button',
				text			: 'search',
				handler			: that.launchSearch
			},'->',{
				xtype			: 'textfield',
				id				: that.displayfieldid,
				width			: '75%',
			}],
			items		: [{
				region			: 'east',
				width			: '50%',
				xtype			: 'treepanel',
				id				: that.filetreeid,
				lines			: true,
				store			: that.treeFileStore,
				loadMask		: true,
				split			: true,
				rootVisible		: false,
				multiSelect		: false,
				useArrows		: true,
				stateful		: false,
				listeners		: {
					itemclick		: function(tree,record) {
						Ext.getCmp(that.displayfieldid).setValue(record.get('fullfilename'));
					}
				}
			},{
				region		: 'center',
				xtype		: 'grid',
				id			: that.gridid,
				layout		: 'fit',
				border		: false,
				store		: that.fileStore,
				loadMask	: true,
				autoFit		: true,
				listeners		: {
					itemclick		: function(grid,record) {
						Ext.getCmp(that.displayfieldid).setValue(record.get('FIL_FOLDER')+'/'+record.get('FIL_FILE'));
					}
				},
				columns		: [{
					header: "path"		, width: 300,flex : 0, dataIndex: 'FIL_FOLDER'		, sortable: true
				},{
					header: "file"		, width: 300,flex : 1, dataIndex: 'FIL_FILE'		, sortable: true
				}]
			}]
		});
		this.callParent(this);
	}
});