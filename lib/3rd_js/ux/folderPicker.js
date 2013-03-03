Ext.define("Ext.form.field.folderpicker", {
	extend: 'Ext.form.field.Picker',
	alias: ['widget.folderpicker'],

	onTriggerClick: function(){
		var that=this;
		that.originalValue = that.getValue();
		if (!that.readOnly && !that.disabled) {
			if (that.isExpanded) {
				that.collapse();
			} else {
				that.expand();
			}
			that.inputEl.focus();
		}
	},
	createPicker : function(){
		var that=this;
		that.textfieldid = Ext.id();
		that.picker =  new Ext.panel.Panel({
			width		: 540,
			height		: 330,
			border		: false,
			floating	: true,
			layout		: 'border',
			items		:[{
				region		: 'center',
				xtype		: 'fileBrowser',
				root		: '/',
				viewPreview	: false,
				viewFiles	: false,
				viewFolders : true,
				listeners : {
					folderclick		: function (filebrowser,root,record){
						that.newValue = record.raw.fullpath;
						that.setValue(that.newValue);
					},
					folderdblclick		: function (filebrowser,root,record){
						that.inputEl.focus();
						that.newValue = record.raw.fullpath;
						that.setValue(that.newValue);
						that.collapse();
						//console.log('folderclick',filebrowser,root,record);
					}
				}
			},{
				region		: 'south',
				xtype		: 'toolbar',
				items		:['->',{
					xtype		: 'button',
					text		: 'Ok',
					handler		: function(){
						that.inputEl.focus();
						that.setValue(that.newValue);
						that.collapse();
					}
				},{
					xtype		: 'button',
					text		: 'Cancel',
					handler		: function(){
						that.inputEl.focus();
						that.setValue(that.originalValue);
						that.collapse();
					}
				}]	
			}]
		});
		return that.picker;
	}
});