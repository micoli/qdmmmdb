Ext.define('MyDesktop.SettingsPanels.Wallpaper', {
	extend		: 'MyDesktop.SettingsPanels',
	settingId	: 'wallpaper',
	title		: 'WallPaper',
	uses: [
		'Ext.ux.desktop.Wallpaper',
		'Ext.form.field.Checkbox'
	],

	getTextOfWallpaper: function (path) {
		var text = path, slash = path.lastIndexOf('/');
		if (slash >= 0) {
			text = text.substring(slash+1);
		}
		var dot = text.lastIndexOf('.');
		text = Ext.String.capitalize(text.substring(0, dot));
		text = text.replace(/[-]/g, ' ');
		return text;
	},

	onOK: function () {
		var that = this;
		if (that.selected) {
			that.desktop.setWallpaper(that.selected, that.stretch);
		}
	},

	onBarSubItemsSelect:  function(tree, record) {
		var that = this;
		that.selected = (record.data.params)?('lib/3rd_js/desktop/wallpapers/' + record.data.params):Ext.BLANK_IMAGE_URL;
		Ext.getCmp(that.previewid).setWallpaper(that.selected);
	},

	setBarSubItemsInitialSelection: function () {
		var that = this;
		var s = that.desktop.getWallpaper();
		if (s) {
			var path = '/Wallpaper/' + this.getTextOfWallpaper(s);
			Ext.getCmp(that.treeid).selectPath(path, 'text');
		}
	},

	constructor : function(cfg) {
		var that		= this;

		that.treeid		= Ext.id();
		that.previewid	= Ext.id();

		Ext.apply(that,cfg);

		that.selected	= that.desktop.getWallpaper();
		that.stretch	= that.desktop.wallpaper.stretch;

		function child (img) {
			return {
				params	: img,
				text	: that.getTextOfWallpaper(img),
				iconCls	: '',
				leaf	: true
			};
		}

		that.barSubItems = {
			text		: 'Wallpaper',
			expanded	: true,
			children	: [{
				text		: "None",
				iconCls		: '',
				leaf		: true
			},
			child('Blue-Sencha.jpg'	),
			child('Dark-Sencha.jpg'	),
			child('Wood-Sencha.jpg'	),
			child('blue.jpg'		),
			child('desk.jpg'		),
			child('desktop.jpg'		),
			child('desktop2.jpg'	),
			child('sky.jpg'			)
			]
		}

		that.main = {
			layout		: 'border',
			items		: [{
				xtype		: 'wallpaper',
				region		: 'center',
				id			: that.previewid,
				listeners	: {
					afterrender : function(){
						Ext.getCmp(that.previewid).setWallpaper(that.selected);
					}
				}
			},{
				xtype		: 'checkbox',
				region		: 'south',
				height		: 40,
				frame		: true,
				boxLabel	: 'Stretch to fit',
				checked		: that.stretch,
				listeners	: {
					change		: function (comp) {
						that.stretch = comp.checked;
					}
				}
			}],
			bbar : ['->',{
				xtype	: 'button',text: 'OK', handler: that.onOK, scope: that
			},{
				xtype	: 'button',text: 'Cancel', handler: that.setBarSubItemsInitialSelection, scope: that
			}]
		}
		that.callParent(that);
	}
},function(){
	this.superclass.self.registerPanel(this.$className);
});