/**
 * An example implementation of a control for the Ext.ux.YoutubePlayer.
 * It provides functionality for loading videos, muting/unmuting a video,
 * setting the volume and paging between items in a playlist (though a playlist 
 * is neither part of the Ext.ux.YoutubePlayer nor the Ext.ux.YoutubePlayer.Control).
 *
* @author Julien Bouquillon <julien@bouquillon.com>
 * inspired from YoutTubePlayer by Thorsten Suckow-Homberg <ts@siteartwork.de>
 */
 
Ext.namespace('Ext.ux.VlcPlayer'); 
Ext.ux.VlcPlayer.Control = Ext.extend(Ext.Toolbar, {
	
	/**
	 * The youtube player this control should take care of.
	 * @cfg {Ext.ux.vlcplayer} player
	 */
	
	/**
	 * The task that is responsible for reading out different states from the video
	 * such as bytesLoaded
	 */
	task : null, 
	
	/**
	 * An element to show/hide the volume panel using fxs.
	 */
	fxel : null,
	
	/**
	 * The table cell in the toolbar that holds information about the runtime
	 * of the video
	 */
	elRuntime : null,
	
	/**
	 * An Ext.Panel holding the volume control.
	 */
	volumePanel : null,
	
 
	/**
	 * An ext button for fullscreen
	 */
	fsButton : new Ext.Toolbar.Button({
		iconCls : 'ext-ux-vlcplayer-fullscreen',
		disabled : true		
	}),
	
	/**
	 * An ext button for starting the video.
	 */
	playButton : new Ext.Toolbar.Button({
		iconCls : 'ext-ux-vlcplayer-play',
		disabled : true		
	}),
	
	/**
	 * An ext button for stopping the currently playing video
	 */
	stopButton : new Ext.Toolbar.Button({
		iconCls : 'ext-ux-vlcplayer-stop',
		disabled : true		
	}),
 
	/**
	 * The button to mute/unmute the sound of the video.
	 */
	muteButton : null,
	
	/**
	 * Slider to control the volume of the video
	 */
	volumeSlider : null,
	
	/**
	 * Controls the playback of the video.
	 */
	sliderField : null,	
	
	/**
	 * tells if the user is currently adjusting the play position in the stream
	 */
	isAdjusting : false,
    
    
	
	/**
	 * initializes needed listeners.
	 */
	_initListeners : function()
	{
		this.fsButton.on('click', this._onFullscreen, this);
        this.playButton.on('click', this._onPlay, this);
		this.stopButton.on('click', this._onStop, this);
		this.muteButton.on('toggle', this._onMuteToggle, this);
		this.on('resize', this._onResize, this);
		this.on('hide', this._onHide, this);
		this.on('destroy', this._onDestroy, this);
		var c = this;
		this.player.on('stateChange', function(state, panel, player){c._processPlayerEvents.defer(1, c, [state, panel, player]);}, this);
		this.sliderField.on('dragstart', function(){this.isAdjusting = true;}, this);
		this.sliderField.on('drag', this._onSeekPosition, this);
        //this.sliderField.on('click', function() {alert(this.sliderField.getValue());}, this);
		this.sliderField.on('dragend', function(){this.isAdjusting = false;this.player.seekTo(this.sliderField.getValue());}, this);
		this.volumeSlider.on('drag', this._onSetVolume, this);
		this.player.on('error', this._onError, this);
	},
	
	 
	
	/**
	 * Listener or the error-event of the player. If any error occurs, the player
	 * will be stopped.
	 */
	_onError : function(errorCode, playerPanel, player)
	{
		playerPanel.stopVideo();
		Ext.Msg.alert('Error', 'The video you requested could not be played. Error code '+errorCode);
	}, 
	
	/**
	 * Listener for the progress slider, i.e. when the slider gets dragged and the user
	 * wants to skip to a new position in the video stream.
	 */
	_onSeekPosition : function()
	{
		if (!this.isAdjusting) this.player.seekTo(this.sliderField.getValue());
	},
	
	/**
	 * Listener for the volume slider.
	 */
	_onSetVolume : function()
	{
		this.player.setVolume(this.volumeSlider.getValue());
	},	
	
	/**
	 * Listener for the mute button toggle event
	 */
	_onMuteToggle : function(button, pressed)
	{
		if (pressed) {
			button.setIconClass('ext-ux-vlcplayer-mute');
			this.player.mute(true);
		} else {
			button.setIconClass('ext-ux-vlcplayer-volume');
			this.player.mute(false);	
		}
		
	},
	

	/**
	 * Listener for the play button
	 */
	_onPlay : function(button)
	{
        var state = this.player.getPlayerState();
        if (state == 'nostate')  this.player.playVideo();
        else this.player.pauseVideo();
   },
         
     _onFullscreen : function(button)
	{
      
        this.player.toggleFullScreen();
	}, 

	/**
	 * Listener for the stop button
	 */
	_onStop : function(button)
	{
		this.player.seekTo(0);
		this.player.stopVideo();
		this.stopButton.setDisabled(true);
	}, 

	/**
	 * Inits this component.
	 */
	initComponent : function()
	{
		this.volumeSlider = new Ext.ux.VlcPlayer.Control.SliderField({
	   		minValue:0,
			maxValue:100,
			height : 25,
			width: 110,
			disabled : true
		});
		
		this.sliderField = new Ext.ux.VlcPlayer.Control.SliderField({
			minValue:0,
			maxValue:0,
			fieldLabel:'Slider',
			disabled  : true,
			listeners : {
				render : function() {
					this.el.dom.parentNode.style.width = '100%';	
				}
			}
		});			
		
		this.volumePanel = new Ext.Panel({bodyStyle:'background-color:#D3E1F1;',width:120,height:25, items : [this.volumeSlider]});
		
		this.muteButton = new Ext.Toolbar.SplitButton({
				iconCls : 'ext-ux-vlcplayer-volume',
				enableToggle : true,
				disabled : true,
				menu: new Ext.menu.Menu({
					items : [new Ext.menu.Adapter(this.volumePanel)]	
				})
		});
		
		this.muteButton.menu.on('beforeshow', function(){
			var state = this.player.getState();
			if (state != 'ended' && state != 'unstarted') {
				this.volumeSlider.setDisabled(false);
				this.volumeSlider.setValue(this.player.getVolume(), true);
			}
		}, this);
		
       
		Ext.apply(this, {
			items: [
				this.playButton,
				this.stopButton,
                this.fsButton,
				' ', 
				this.sliderField, 
				' ' 
			]
		});
			
		this._initListeners();
		Ext.ux.VlcPlayer.Control.superclass.initComponent.call(this);
	},
	
	/**
	 * Will add a child containing the remaining playtime into the toolbar.
	 * This has to be beautified in future releases.
	 */
	afterRender : function(ct, position)
	{
		Ext.ux.VlcPlayer.Control.superclass.afterRender.call(this, ct, position);
		
		this.elRuntime = Ext.fly(this.el.dom.getElementsByTagName('tr')[0]).createChild({tag : 'td'});
		this.elRuntime.update('00:00');
		this.add(new Ext.Toolbar.Spacer());
		this.add(this.muteButton);
	},
	
	/**
	 * Re-aligns the volume panel and notifies the sliderField to fire
	 * it's resize event.
	 */
	_onResize : function(adjWidth, adjHeight, rawWidth, rawHeight)
	{
		if (this.fxel) {
			this.fxel.alignTo(this.el.dom, 'br-tr');
		}
		
		this.sliderField.fireEvent('resize');
    },
    
   
    /**
     * Stops the task manager, removes the fx element and destroys the volume 
     * panel.
     */
    _onDestroy : function()
	{
		if (this.task) {
		//	Ext.TaskMgr.stop(this.task);
		}
		
		if (this.fxel) {
			this.fxel.dom.parentNode.removeChild(this.fxel.dom);
			this.volumePanel.destroy();
		}
    },
    
    /**
     * Callback for the task querying the player's state every 500 ms.
     * Note that in mozilla, a maximize or collapse of a window the player sits 
     * in will reload the whole movie, thus resultig in the current task runnning
     * to be invalid. The task will check for a valid player-insance and end itself
     * if none found.
     */
    _updateVideoInfo : function()
    {
      
    	if (!this.player.playerAvailable()) {
    		this._processPlayerEvents('ended', this.player, null);
    		return;	
    	}
    	var player = this.player;
    	var slider = this.sliderField;
		var total  = -1;
		var loaded = -1;
		
		var currentTime = this.player.getCurrentTime();
		var totalTime   = this.player.getDuration();
		
		var percLoaded = Math.floor(((loaded/total)*100));
		
		var width = slider.sbar.getWidth();
		
		if (loaded != -1) {
			if (isNaN(percLoaded)) percLoaded=0;
			var pixels = Math.floor((width/100)*percLoaded);
			
	
			slider.updateSliderBg(pixels);
		}
		
		if (totalTime != 0) {
			var rem = Math.floor(totalTime - currentTime);
			
			var minutes = Math.floor(rem / 60);	
			var seconds = (rem%60);
			this.elRuntime.update((minutes < 10 ? '0'+minutes : minutes)+':'+(seconds < 10 ? '0'+seconds : seconds));	
			this.sliderField.maxValue = totalTime;
			if (!this.isAdjusting) {
				this.sliderField.setValue(currentTime, true);
			}
		}
        else {
            this.sliderField.resetPositions();
			this.sliderField.setDisabled(true);
        }
    },
            
            
	/**
	 * Gateway for the player events.
	 */    
    _processPlayerEvents : function(state, panel, player)
    {
            if (!this.task) {
                   this.task = {
				    run: function(){
				       this._updateVideoInfo();
				    },
				    interval: 500,
                    scope:this
                    };
                  Ext.TaskMgr.start(this.task);
			}
            
            
    	switch (state) {
          
			case 'ended' || 'nostate' || 'stopping' || 'error':	 
				
				if(this.playButton.iconCls!='ext-ux-vlcplayer-play') this.playButton.setIconClass('ext-ux-vlcplayer-play');
				this.sliderField.resetPositions();
				this.sliderField.setDisabled(true);
				if (this.volumeField) {
					this.volumeField.setDisabled(true);
				}
				this.playButton.set<(true);
				this.stopButton.setDisabled(true);
				this.muteButton.setDisabled(true);
                this.fsButton.setDisabled(true);
                break;
			
			case 'playing':	
				this.sliderField.setDisabled(false);
				if (this.volumeField) {
					this.volumeField.setDisabled(false);
				}
				if(this.playButton.iconCls!='ext-ux-vlcplayer-pause') this.playButton.setIconClass('ext-ux-vlcplayer-pause');
				this.playButton.setDisabled(false);
				this.stopButton.setDisabled(false);
				this.muteButton.setDisabled(false);
                this.fsButton.setDisabled(false);

                break;
			
			case 'paused':	    
				if(this.playButton.iconCls!='ext-ux-vlcplayer-play') this.playButton.setIconClass('ext-ux-vlcplayer-play');
                break;

		}
    }
    
    
	
});


/**
 * Taken from the ext 2.0.2 examples folder and extended for functionality with 
 * vertical/hotizontal sliders. All props to Jack Slocum & his team.
 * Since this class was experimental I decided to change it's namespace and the code 
 * directly instead of extending it. So don't get confused, most of the code and
 * logic was implemented by the Ext Team.
 * 
 */
Ext.ux.VlcPlayer.Control.SliderField = Ext.extend(Ext.form.Field, {
	defaultAutoCreate : {tag:'input', type:'hidden'},

	thumbX : 0,
	thumbY : 0,
	
	// private
	initComponent : function() {
		Ext.ux.VlcPlayer.Control.SliderField.superclass.initComponent.call(this);
		this.minValue = this.minValue || 0;
		this.maxValue = this.maxValue || 1;
		
		this.addEvents(
			'dragstart',
			'drag',
			'dragend' 
		);
		this.on('resize', this.onResize, this);
	},
	
 
	onResize : function()
	{
		this.adjustPixelMax();
	},
	
	adjustPixelMax : function()
	{
		if (!this.sbar) {
			return;	
		}
		
		var bb = this.sbar.getBox();
		var tb = this.sthumb.getBox();
		if (this.vertical === true) {
			this.pixelMax = bb.height - tb.height;	
		} else {
			this.pixelMax = bb.width - tb.width;
		}		
	},

	// private
	onRender: function(ct, position) {
		Ext.ux.VlcPlayer.Control.SliderField.superclass.onRender.call(this, ct, position);
		this.sbar = Ext.DomHelper.append(ct, {tag:'div', cls:'ext-ux-vlcplayer-slider-'+(this.vertical === true ? 'v' : 'h')+'bar'}, true);
		this.sthumb = Ext.DomHelper.append(ct, {tag:'img', src:Ext.BLANK_IMAGE_URL, cls:'ext-ux-vlcplayer-slider-'+(this.vertical === true ? 'v' : '')+'thumb'}, true);
		this.sthumb.addClassOnOver('ext-ux-vlcplayer-slider-'+(this.vertical === true ? 'v' : '')+'thumb-over');
		this.setValue(this.getValue() || this.minValue);
	},

	// private
	afterRender: function() {
		Ext.ux.VlcPlayer.Control.SliderField.superclass.afterRender.call(this);
		
		if (this.vertical === true) {
			this.sbar.setHeight(parseInt(this.el.dom.style.height, 10));
		} else {
			var w = parseInt(this.el.dom.style.width, 10);
			if (!isNaN) {
				this.sbar.setWidth(w);
			}
		}
		
		this.thumbX = parseInt(this.sthumb.getStyle('left'));
		this.thumbY = parseInt(this.sthumb.getStyle('bottom'));
		
		this.dd = new Ext.dd.DD(this.sthumb.id, 'slider-' + this.sthumb.id, {constrainX : !this.vertical, constrainY : this.vertical});
		this.dd.slider = this;
		this.dd.onDrag = this.onDrag;
		this.dd.startDrag = this.startDrag;
		this.dd.endDrag = this.endDrag;
		
		if (this.disabled) {
			this.dd.lock();	
		}
		
	},
	
	updateSliderBg : function(percentage)
	{
		this.sbar.dom.style.backgroundPosition = '-'+(1280-percentage)+'px 0';
	},
	
	setValue : function(value, moveThumb) 
	{
		Ext.ux.VlcPlayer.Control.SliderField.superclass.setValue.call(this, value);
		
		
		if (moveThumb === true) {
			
			if (!this.pixelMax) {
				this.adjustPixelMax();
			}
			
			var min = this.minValue;
			var max = this.maxValue;	
			
			if (this.vertical === true) {
				this.dd.getEl().style.bottom = (Math.floor((value*this.pixelMax)/(min + (max - min)))+this.thumbY)+'px';
			} else {
				this.dd.getEl().style.left = Math.floor((value*this.pixelMax)/(min + (max - min)))+'px';
			}
		}
	},
	
	resetPositions : function()
	{
		this.sthumb.dom.style.left = '0px';
		this.sbar.dom.style.backgroundPosition = '-1280px 0';
	},
	
	onDisable : function()
	{
		Ext.ux.VlcPlayer.Control.SliderField.superclass.onDisable.call(this);
		this.sbar.addClass(this.disabledClass);
		this.sthumb.addClass(this.disabledClass);
		this.dd.lock();
	},
	
	onEnable : function()
	{
		Ext.ux.VlcPlayer.Control.SliderField.superclass.onEnable.call(this);
		this.sbar.removeClass(this.disabledClass);
		this.sthumb.removeClass(this.disabledClass);
		this.dd.unlock();
	},
	
	startDrag: function(x, y) {
		var slider = this.slider;

		var bb = slider.sbar.getBox();
		var tb = slider.sthumb.getBox();

		this.resetConstraints();
		
		if (!slider.pixelMax) {
			slider.adjustPixelMax();
		}
		
		if (this.slider.vertical === true) {
			this.setXConstraint(0, 0);
			this.setYConstraint(tb.y - bb.y + 1, bb.y + bb.height - tb.y - tb.height - 1);
		} else {
			this.setYConstraint(0, 0);
			this.setXConstraint(tb.x - bb.x + 1, bb.x + bb.width - tb.x - tb.width - 1);
		}
		
		this.slider.fireEvent('dragstart');
	},
	
	endDrag : function(e)
	{
        
		this.slider.fireEvent('dragend');
        
	},
	
	onDrag: function(e) {
		var min = this.slider.minValue;
		var max = this.slider.maxValue;
		
		if (this.slider.vertical === true) {
			var pixelPos = Math.abs(parseInt(this.getEl().style.top,10))-this.slider.thumbY;
			this.slider.setValue(min + (max - min) * pixelPos / this.slider.pixelMax);
		} else {
			var pixelPos = parseInt(this.getEl().style.left,10);
			this.slider.setValue(min + (max - min) * pixelPos / this.slider.pixelMax);
		}
		
		this.slider.fireEvent('drag');
	}
});


