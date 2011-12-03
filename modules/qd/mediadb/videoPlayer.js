VLCOpenVideo = function (url,title){
	var VLCPlayerPanel = new Ext.ux.VlcPlayer({
		playerId     : 'myplayer'+Ext.id(),
		verbosity   : 2
	});

	/*VLCPlayerPanel.on('ready', function(panel, player) {
			panel.dolog("player ready");
	}, VLCPlayerPanel);*/

	var wVLCPlayerPanel = new Ext.Window({
		title        : '['+title+']',
		hideMode     : 'visibility',
		layout       : 'fit',
		maximizable  : false,
		animCollapse : false,
		collapsible  : false,
		closable     : true,
		resizable    : true,
		items        : [VLCPlayerPanel],
		bbar         : new Ext.ux.VlcPlayer.Control({
			player       : VLCPlayerPanel,
			border       : false,
			//id           : 'control',
			style        : 'border:none;'
		}),
		listeners   : {
			resize   : function(){
				this.bottomToolbar.fireEvent('resize')
			},
			show: function(){
					window.onVlcPlayerReady(VLCPlayerPanel.playerId);
					VLCPlayerPanel.playVideo(url, url, ":http-caching=5000");
			},
			beforeclose  : function(){
				VLCPlayerPanel.stopVideo();
					console.log('eeeeeee');
			}
		},
		height      : 400,
		width       : 500
	});
	wVLCPlayerPanel.show();
}