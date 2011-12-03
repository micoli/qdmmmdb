Ext.define('qd.nzb.feeditemdesc', {
    extend		: 'Ext.panel.Panel',
	alias		: 'widget.qd.nzb.feeditemdesc',
	record		: null,
	setItem		: function (data){
		Ext.getCmp(this.imgid ).tpl.overwrite(Ext.getCmp(this.imgid ).body,data);
		Ext.getCmp(this.headid).tpl.overwrite(Ext.getCmp(this.headid).body,data);
		Ext.getCmp(this.descid).tpl.overwrite(Ext.getCmp(this.descid).body,data);
	},
	initComponent : function() {
		var that = this;
		that.imgid  = Ext.id();
		that.headid = Ext.id();
		that.descid = Ext.id();
		Ext.apply(this, {
			layout		: 'border',
			border		: false,
			items		: [ {
				region		: 'west',
				id			: that.imgid,
				width		: 90,
				border		: false,
				baseCls		: 'qdBackFrame',
				tpl			: new Ext.XTemplate(
					'<div style="padding:5px;background-color:#D3E1F1;">',
					'	<tpl if="DESC_POSTER">',
					'		<img height="120" src="p/QDMediaDBProxy.proxyImg/?u={DESC_POSTER}"/>',
					'	</tpl>',
					'</div>'
				)
			},{
				region		: 'center',
				layout		: 'border',
				border		: false,
				items		: [{
					region		: 'north',
					xtype		: 'panel',
					height		: 50,
					minHeight	: 50,
					border		: false,
					baseCls		: 'qdBackFrame',
					id			: that.headid,
					tpl			: new Ext.XTemplate(
						'<div style="padding:5px;">',
						'	<u><b>{DESC_TITLE}</b></u><br />',
						'	{ITE_MASK}</b><br />',
						'	<i>',
						'		<tpl if="DESC_GENRE">{DESC_GENRE}<br/></tpl>',
						'		<tpl if="DESC_DIRECTOR">{DESC_DIRECTOR} </tpl>',
						'		<tpl if="ITE_YEAR">({ITE_YEAR}) </tpl>',
						'		<tpl if="DESC_ACTOR">{DESC_ACTOR}</tpl>',
						'	</i>',
						'</div>'
					)
				},{
					region		: 'center',
					id			: that.descid,
					border		: false,
					baseCls		: 'qdBackFrame',
					autoScroll	: true,
					tpl			: new Ext.XTemplate(
						'<div style="padding:5px;">',
						'	{DESC_SUMMARY}',
						'</div>'
					)
				}]
			}]
			/*tpl			: new Ext.XTemplate(
				'<table>',
				'	<tr style="vertical-align:top;">',
				'		<td style="padding:5px;">',
				'			<tpl if="DESC_POSTER">',
				'				<img height="120" src="p/QDMediaDBProxy.proxyImg/?u={DESC_POSTER}"/>',
				'			</tpl>',
				'		</td>',
				'		<td style="padding:5px;">',
				'			<u><b>{DESC_TITLE}</b></u><br />',
				'			{ITE_MASK}</b><br />',
				'			<i>',
				'				<tpl if="DESC_GENRE">{DESC_GENRE}<br/></tpl>',
				'				<tpl if="DESC_DIRECTOR">{DESC_DIRECTOR} </tpl>',
				'				<tpl if="ITE_YEAR">({ITE_YEAR}) </tpl>',
				'				<tpl if="DESC_ACTOR">{DESC_ACTOR}</tpl>',
				'			</i>',
				'			<br /><br />',
				'			{DESC_SUMMARY}',
				'		</td>',
				'	</tr>',
				'</table>'
			)
*/		});
		this.on({
			afterrender : function(){
				if (that.record && that.record.data && that.record.data.DESC_POSTER){
					that.setItem(that.record.data);
				}
			}
		})
		this.callParent(arguments);
	}
});