/**
 * Utilizes the VLC plugin to embed to VLC in an Ext Js components.
 *
 *		  
 * Dependencies:
 * Ext.ux.MediaPanel
 *
 * @author Julien Bouquillon <julien@bouquillon.com>
 * inspired from YoutTubePlayer by Thorsten Suckow-Homberg <ts@siteartwork.de>
 *
 */

Ext.namespace('Ext.ux');

Ext.ux.VlcPlayer = Ext.extend(Ext.ux.MediaPanel.Vlc, { 
	 
	/**
	 * @cfg {String} playerId
	 * The id of the flash object. The id attribute of the embedded flash object
	 * will be set to this property.
	 */
	playerId : null,
	
	/**
	 * @cfg {Integer} verbosity
	* The Vlc plugin log verbosity : The numbers have the following meaning: -1 disable, 0 info, 1 error, 2 warning, 3 debug.
	 */
	verbosity : 0,
	  
	/**
	 * @param {HTMLElement} 
	 * The VLC plugin controlled by this component
	 * @private
	 */
	player : null,
	
	/**
	 * the last uri and options asked
	 * 
	 */
	current_video : null,
    current_options : null,
	
	 /**
	 * the player current status
	 * 
	 */
    current_state : null,
	
	/**
	 * Sets default confg operations and attaches new events to this component so
	 * interaction with the flash player works.
	 */
	initComponent : function()
	{
		// [Skipped:
		// instead of overwriting an already existing onVlcPlayerReady-function,
		// we create a sequence in the hope to not break too much existing application
		// behavior]
		var ovtpr = function(playerId) {
			var cmpId = Ext.ux.VlcPlayer.Players[playerId];
			if (cmpId) {
                var panel = Ext.getCmp(cmpId);
				var player = document.getElementById(playerId);
				panel._setPlayer(player);
				panel.fireEvent('ready', panel, player);	
                // start VLC debug
                  this.task = {
				    run: function(){
				       this.check_state();
				    },
				    interval: 500,
                    scope:panel
                    };
                  Ext.TaskMgr.start(this.task);
			}
		};
		window.onVlcPlayerReady = ovtpr;	
		
		this.mediaCfg = {};
		var defMediaCfg = { 
			    mediaType:'VLC', 
			    id		 : this.playerId, 
			    start    : false, 
			    controls : false,
			    params	 : {}
		};
		
		Ext.apply(this.mediaCfg, defMediaCfg); 
		
		this.addEvents(
            /**
             * @event ready
             * Fires after the VLC plugin has been loaded and is accessible via javascript
             * @param {Ext.ux.MediaPanel.Vlc} panel The Ext.Panel derivat holding the Vlc object
             * @param {HTMLElement} player The DOM Node representing the Vlc player
             */		
             'ready',
             
             /**
              * @event stateChange 
              * Fires whenever the player's state changes. 
              * @param {String} state Possible values are 'idle_close',  'opening', 'buffering', 'playing', 'paused', 'stopping', 'forward', 'backward', 'ended', 'error', 'nostate'
              * @param {Ext.ux.MediaPanel.Vlc} panel The ext panel that holds the Vlc player
              * @param {HTMLElement} player The Dom node representing the Vlc player
              */
              'stateChange',

			 /**
              * @event error 
              * Fired when an error in the player occurs. 
              * @param {Number} errorCode Currently there is only one error code, which is 'video_not_found'.
              * This occurs when a video has been removed (for any reason), or it has been marked as private or 
              * non-embeddable by the user. 
              * @param {Ext.ux.MediaPanel.Vlc} panel The ext panel that holds the Vlc player
              * @param {HTMLElement} player The Dom node representing the Vlc player
              */              
              'error'
		);
 
		if (!Ext.ux.VlcPlayer.Players) {
			Ext.ux.VlcPlayer.Players = [];
		}
		Ext.ux.VlcPlayer.Players[this.playerId] = this.id;
		
		Ext.ux.VlcPlayer.superclass.initComponent.call(this);
        
	},
	
	/**
	 * Sets the player controlled by this component once Vlcobject is fully initialized.
	 * This method is API reserved.
	 *
	 * @param {HtmlElement} player
	 * @private
	 */ 
	_setPlayer : function(player) 
	{
		this.player = player;	
	},
	 

	/**
	 * Overwrites parent implemenmtation to keep aspect ratio of the player window if needed. 
	 */
	onResize : function(w, h)
	{ 
		Ext.ux.VlcPlayer.superclass.onResize.call(this, w, h);
		if (this.playerAvailable()) {}
	},
	
	/**
	 * Helper function for checking if the flash movie is still available.
	 */
	playerAvailable : function()
	{
		return (this.player && this.player.input.state) ? true : false;	
	}, 

   
	/**
	 * Plays the video with specified options
	 */
	playVideo : function(uri, title, options)
	{
        if (!uri) {
            uri = this.current_video;
            options = this.current_options;
        }
        if (!title) title = uri;
        var id = this.player.playlist.add(uri, title, options);
        this.player.playlist.playItem(id);
        // save params in case needs to be restarted
		this.current_video = uri;
        this.current_options = options;
	}, 
        
    toggleFullScreen : function() {
            this.player.video.fullscreen = true;
    },
	
	/**
	 * Pauses the currently playing video. 
	 */
	pauseVideo : function()
	{
        this.dolog("togglePause()");
		this.player.playlist.togglePause();	
	}, 
	
	/**
	 * Stops the current video. 
	 */
	stopVideo : function()
	{
        this.player.playlist.stop();
	},	
   
	/**
	 * Mutes/unmutes the player. 
	 *
	 * @param {Boolean} mute <tt>true</tt> to mute the player, <tt>false</tt> to unmute the
	 * player
	 */
	mute : function(mute)
	{
		this.player.audio.mute = mute;
	},	
 
	/**
	 * Sets the volume. Accepts an integer between 0-100.  
	 *
	 * @param {Number} volume 
	 */
	setVolume : function(volume)
	{
		// VLC uses a 0-200 range
        this.player.audio.volume = parseInt(volume * 2);
	},	
	
	/**
	 * Returns the player's current volume, an integer between 0-100. 
	 *
	 * @return {Number} 
	 */
	getVolume : function()
	{
		return parseInt(this.player.audio.volume / 2);
	},	
	
	/**
	 * Seeks to the specified time of the video in seconds. 
	 *
	 * @param {Number} seconds
	 */
	seekTo : function(seconds)
	{
        this.player.input.time = seconds * 1000;
	},

	/**
	 * Returns the state of the player. Possible values are 'unstarted', 'ended', 
	 * 'playing', 'paused', 'buffering', 'video_cued'. Returns 'unknown' if the player's
	 * state is not yet known by this api.
	 *
	 * @return {String}
	 */
	getPlayerState : function()
	{
        if (!this.playerAvailable()) return 'nostate';
		var state = this.player.input.state;

		switch (state) {
			case 0:  state = 'idle_close';  break;	
			case  1:  state = 'opening';	    break;
			case  2:  state = 'buffering';	    break;
			case  3:  state = 'playing';  break;
			case  4:  state = 'paused'; break;
            case  5:  state = 'stopping'; break;
            case  6:  state = 'forward'; break; // deprecated
            case  7:  state = 'backward'; break; // deprecated
            case  8:  state = 'ended'; break;
            case  9:  state = 'error'; break;
			default : state = 'nostate';	break;
		}
		return state;
	},

	/**
	 * Returns the current time in seconds of the current video. 
	 *
	 * @return {Number}
	 */
	getCurrentTime : function()
	{
        // seems buggy in VLC 0.9.8
        // return (this.player.input.time / 1000);
        return this.getDuration() * this.getCurrentPosition();
	},	
        
	getCurrentPosition : function()
	{
		return (this.player.input.position);
	},	
	/**
	 * Returns the duration in seconds of the currently playing video. Note that 
	 * getDuration() will return 0 until the video's metadata is loaded, which 
	 * normally happens just after the video starts playing. 
	 *
	 * @return {Number}
	 */
	getDuration : function()
	{
		return (this.player.input.length / 1000)
	},	
  
    /**
	 * Debug VLC in Firebug Console if exists or in Ext console
	 *
	 */
    
    check_state : function() 
    {
        if (this.player) {    
            this.player.log.verbosity = this.verbosity;
            
            // checking LOG
            var messages = this.player.log.messages;
            var contents = "";
            msg_iter = messages.iterator();
            
            while (msg_iter.hasNext) {
                msg = msg_iter.next();
                var line = msg.name + " " + msg.type + " " + msg.message;  
				this.dolog(line);
            }
            messages.clear();
            
            //checking STATUS
            var status = this.player.input.state;
            
            if(status != this.current_state) {
                this.current_state = status;
                this.fireEvent('stateChange', this.getPlayerState(), this, this.player);
                this.dolog("fire stateChange : " + status)
            }
        }      
    },
	
	dolog : function(contents)
	{
		if(typeof(window['console']) != 'undefined') console.log(contents);
		else if (Ext.log) Ext.log(contents);
	}
    
       
});