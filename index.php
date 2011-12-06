<?
	require 'lib/classes/QDGlobal.php';
	header('Content-type: text/html; charset=UTF-8');
	//db($GLOBALS['conf']);die();
?><html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title><?php print str_replace(' ','&nbsp;',$GLOBALS['conf']['app']['title']);?></title>
		<link rel="stylesheet" type="text/css" href="skins/css/loader.css" />
		<style type="text/css">
			.x-grid3-row td{
				-moz-user-select	:text;
			}
			.ext-strict .ext-ie .x-tree .x-panel-bwrap{
				position:relative;
				overflow:hidden;
			}
		</style>
	</head>
	<body >
		<div id="loading-mask" style=""></div>
		<div id="loading">
			<div class="loading-indicator">
	            <h1><?php print str_replace(' ','&nbsp;',$GLOBALS['conf']['app']['title']);?></h1>
				<img src="<?php print str_replace(' ','&nbsp;',$GLOBALS['conf']['app']['loaderImg']);?>" />
				<span id="loading-msg">Loading styles and images...</span>
		    </div>
		</div>
		<link rel="stylesheet" type="text/css" href="skins/resources/css/ext-all.css" />
		<link rel="stylesheet" type="text/css" href="skins/css/icons.css" />
		<link rel="stylesheet" type="text/css" href="skins/css/body.css" />
		<script type="text/javascript">
			document.getElementById('loading-msg').innerHTML = 'Loading Core API...';
		</script>
		<script type="text/javascript" src="lib/3rd_js/extjs4/bootstrap.js"></script>
		<script type="text/javascript" src="lib/3rd_js/startup.js"></script>
		<script type="text/javascript">
			document.getElementById('loading-msg').innerHTML = 'Loading Application';
			var QD_GBL_CONF =<?php print json_encode($GLOBALS['conf']);?>;
		</script>
		</script>
		<script type="text/javascript" src="lib/3rd_js/commonfunctions.js"></script>
		<script type="text/javascript" src="modules/qd/mediadb/serieEditor.js"></script>
		<script type="text/javascript" src="modules/qd/mediadb/seriePanel.js"></script>
		<script type="text/javascript" src="lib/3rd_js/ux/SimpleIFrame.js"></script>
		<script type="text/javascript" src="modules/qd/sabnzbd/sabnzbdPanel.js"></script>
		<script type="text/javascript" src="lib/3rd_js/ux/thumbnailSelector.js"></script>
		<script type="text/javascript" src="lib/3rd_js/ux/QTPreview.js"></script>
		<script type="text/javascript" src="modules/qd/mediadb/movieQTPreview.js"></script>
		<script type="text/javascript" src="modules/qd/mediadb/movieEditor.js"></script>
		<script type="text/javascript" src="modules/qd/mediadb/moviePanel.js"></script>
		<script type="text/javascript" src="modules/qd/nzb/nzbview.js"></script>
		<script type="text/javascript" src="modules/qd/nzb/feeditemdesc.js"></script>
		<script type="text/javascript" src="modules/qd/nzb/feedtab.js"></script>
		<script type="text/javascript" src="modules/qd/nzb/NZBPanel.js"></script>
		<script type="text/javascript" src="lib/3rd_js/ux/base64.js"></script>
		<script type="text/javascript" src="lib/3rd_js/ux/ImageSelector.js"></script>
		<script type="text/javascript" src="lib/3rd_js/ux/DataView/LabelEditor.js"></script>
		<script type="text/javascript" src="lib/3rd_js/ux/DataView/DragSelector.js"></script>
		<script type="text/javascript" src="modules/qd/mediadb/app.js"></script>
		<link rel="stylesheet" type="text/css" href="lib/3rd_js/desktop/css/desktop.css" />

		<script>
		if(true){
			QD_GBL_CONF.app.mainClass='MyDesktop.App';
		}
		Ext.Loader.setConfig({
			enabled			: true,
			//disableCaching	: true,
			paths			: {
				'Ext'			: 'lib/3rd_js/extjs4',
				'qd'			: 'modules/qd',
				'Ext.ux'		: 'lib/3rd_js/ux',
				'Ext.ux.desktop': 'lib/3rd_js/desktop/js',
				MyDesktop		: 'lib/3rd_js/desktop'
			}
		});
		Ext.require([
			'Ext.grid.*',
			'Ext.data.*',
			'Ext.util.*',
			'Ext.Action',
			'Ext.tab.*',
			'Ext.button.*',
			'Ext.form.*',
			'Ext.layout.*'
		]);
		</script>
	</body>
</html>
