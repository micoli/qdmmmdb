Ext.define('qd.mediadb.movieQTPreview', {
    extend			: 'Ext.Window',
	alias			: 'widget.qd.mediadb.movieQTPreview',
	width			: 600,
	height			: 370,
	modal			: true,
	stateful		: false,
	maximizable		: true,
	//maximized		: true,
	title			: 'Preview',
	initComponent	: function() {
		var that = this;
		var previewid	= Ext.id();

		Ext.apply(this,{
			layout			: 'border',
			items			:	[{
				region			: 'center',
				xtype			: 'QTPreview',
				id				: previewid
			}],
			buttons :[{
				text	: 'close',
				handler	: function(){
					that.close();
				}
			}],
			listeners : {
				show	: function(){
					setTimeout(function(){
						Ext.getCmp(previewid).loadPreview(that.filename)
					},100);
					//setCurrentRecord(that.referenceRecord);
				}
			}
		});
		this.callParent(arguments);
	}
});