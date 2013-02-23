/**
 * Color picker provides a simple color palette for choosing colors. The picker can be rendered to any container. The
 * available default to a standard 40-color palette; this can be customized with the {@link #colors} config.
 *
 * Typically you will need to implement a handler function to be notified when the user chooses a color from the picker;
 * you can register the handler using the {@link #select} event, or by implementing the {@link #handler} method.
 *
 *     @example
 *     Ext.create('Ext.picker.Color', {
 *         value: '993300',  // initial selected color
 *         renderTo: Ext.getBody(),
 *         listeners: {
 *             select: function(picker, selColor) {
 *                 alert(selColor);
 *             }
 *         }
 *     });
 */
Ext.define('Ext.picker.Folder', {
	extend		: 'Ext.Component',
	requires	: 'Ext.XTemplate',
	alias		: 'widget.folderpicker',

	/**
	 * @cfg {String} [componentCls='x-color-picker']
	 * The CSS class to apply to the containing element.
	 */
	componentCls : Ext.baseCSSPrefix + 'color-picker',

	/**
	 * @cfg {String} [selectedCls='x-color-picker-selected']
	 * The CSS class to apply to the selected element
	 */
	selectedCls: Ext.baseCSSPrefix + 'color-picker-selected',

	/**
	 * @cfg {String} value
	 * The initial color to highlight (should be a valid 6-digit color hex code without the # symbol). Note that the hex
	 * codes are case-sensitive.
	 */
	value : null,

	/**
	 * @cfg {String} clickEvent
	 * The DOM event that will cause a color to be selected. This can be any valid event name (dblclick, contextmenu).
	 */
	clickEvent :'click',

	/**
	 * @cfg {Boolean} allowReselect
	 * If set to true then reselecting a color that is already selected fires the {@link #select} event
	 */
	allowReselect : false,


	// private
	initComponent : function(){
		var me = this;

		me.callParent(arguments);
		me.addEvents(
			/**
			 * @event select
			 * Fires when a color is selected
			 * @param {Ext.picker.Color} this
			 * @param {String} color The 6-digit color hex code (without the # symbol)
			 */
			'select'
		);

		if (me.handler) {
			me.on('select', me.handler, me.scope, true);
		}
	},


	// private
	onRender : function(container, position){
		var me = this,
			clickEvent = me.clickEvent;

		Ext.apply(me.renderData, {
			itemCls: me.itemCls,
			colors: me.colors
		});
		me.callParent(arguments);

		me.mon(me.el, clickEvent, me.handleClick, me, {delegate: 'a'});
		// always stop following the anchors
		if(clickEvent != 'click'){
			me.mon(me.el, 'click', Ext.emptyFn, me, {delegate: 'a', stopEvent: true});
		}
	},

	// private
	afterRender : function(){
		var me = this,
			value;

		me.callParent(arguments);
		if (me.value) {
			value = me.value;
			me.value = null;
			me.select(value, true);
		}
	},

	// private
	handleClick : function(event, target){
		var me = this,
			color;

		event.stopEvent();
		if (!me.disabled) {
			color = target.className.match(me.colorRe)[1];
			me.select(color.toUpperCase());
		}
	},

	/**
	 * Selects the specified color in the picker (fires the {@link #select} event)
	 * @param {String} color A valid 6-digit color hex code (# will be stripped if included)
	 * @param {Boolean} suppressEvent (optional) True to stop the select event from firing. Defaults to false.
	 */
	select : function(color, suppressEvent){

		var me = this,
			selectedCls = me.selectedCls,
			value = me.value,
			el;

		color = color.replace('#', '');
		if (!me.rendered) {
			me.value = color;
			return;
		}


		if (color != value || me.allowReselect) {
			el = me.el;

			if (me.value) {
				el.down('a.color-' + value).removeCls(selectedCls);
			}
			el.down('a.color-' + color).addCls(selectedCls);
			me.value = color;
			if (suppressEvent !== true) {
				me.fireEvent('select', me, color);
			}
		}
	},

	/**
	 * Get the currently selected color value.
	 * @return {String} value The selected value. Null if nothing is selected.
	 */
	getValue: function(){
		return this.value || null;
	}
});

