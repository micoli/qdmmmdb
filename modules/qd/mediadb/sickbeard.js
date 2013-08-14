Ext.define('qd.mediadb.sickbeard', {
	extend			: 'Ext.Panel',
	alias			: 'widget.qd.mediadb.sickbeard',
	initComponent	: function() {
		var that = this;
		that.gridfilesid	= Ext.id();

		Ext.define('sbserie', {
			extend: 'Ext.data.Model',
			fields: ['id', 'text', 'fullname','rootDrive','tvdb','numbertorename','serieName']
		});

		that.sbserieStore = Ext.create('Ext.data.Store',{
			pruneModifiedRecords	: true,
			model					: 'sbserie'
		});

		Ext.apply(this,{
			layout		: 'border',
			border		: false,
			stateful	: false,
			items		: [{
				html		: 'eeeee'
			}]
		});
		this.callParent(this);
	}
});