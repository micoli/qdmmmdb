Ext.onReady(function(){
	Ext.require(QD_GBL_CONF.app.mainClass);

	Ext.state.Manager.clear();
    Ext.state.Manager.setProvider(Ext.create('Ext.state.LocalStorageProvider'));

	(Ext.defer(function() {
		var hideMask = function () {
			Ext.get('loading').remove();
			Ext.fly('loading-mask').animate({
				opacity:0,
				remove:true
			});
		};

		document.getElementById('loading-msg').innerHTML = 'Initialization';
		console.log('mainApp',QD_GBL_CONF.app.mainClass);
		//if (QD_GBL_CONF.app.mainClass=='MyDesktop.App'){
		//	var app = new MyDesktop.App();
		//	Ext.defer(hideMask, 250);
		//}else{
			var app = Ext.create(QD_GBL_CONF.app.mainClass);
			document.getElementById('loading-msg').innerHTML = 'Startup';
			var doResize = function() {
				var windowHeight = Ext.getDoc().getViewSize(false).height;

				var footerHeight  = 0;//footerEl.getHeight() + footerEl.getMargin().top,
					titleElHeight = 0;//titleEl.getHeight() + titleEl.getMargin().top,
					headerHeight  = 0;//headerEl.getHeight() + titleElHeight;

				var warnEl = Ext.get('fb');
				var warnHeight = warnEl ? warnEl.getHeight() : 0;

				var availHeight = windowHeight - ( footerHeight + headerHeight + 14) - warnHeight;
				var sideBoxHeight = 0;//sideBoxEl.getHeight();

				app.setHeight((availHeight > sideBoxHeight) ? availHeight : sideBoxHeight);
			};
			//Ext.QuickTips.init();
			Ext.defer(hideMask, 250);
			if (app.setHeight){
				doResize();
			}
		//}
	},500));
	//Ext.qd.nzb.chooseMovie({data : {title : 'Dead Like Me Life After Death'}})
	//VLCOpenVideo("http://streams.videolan.org/streams-videolan/mp4/Mr_MrsSmith-h264_aac.mp4");
	//VLCOpenVideo('file:////zutzut/m/####toextract/U-571.DVDRip.FR/U-571.cd1.DivX.DVDRip.FR.AVI');
});