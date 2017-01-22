<?
	chdir(dirname(__FILE__));
	if(!defined('QD_BASE'))			define ('QDBASE'			,realpath(dirname(__FILE__).'/lib').'/');
	if(!defined('QD_PATH_MODULES'))	define ('QD_PATH_MODULES'	,realpath(dirname(__FILE__)).'/');
	if(!defined('CONF_ROOT'))		define ('CONF_ROOT'			,dirname(__FILE__).'/conf/');

	require 'lib/classes/QDGlobal.php';
	header('Content-type: text/html; charset=UTF-8');
	//db($GLOBALS['conf']);die();
?><html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title><?php print str_replace(' ','&nbsp;',$GLOBALS['conf']['app']['title']);?></title>
		<link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
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
		<link rel="stylesheet" type="text/css" href="lib/3rd_js/desktop/css/desktop.css" />
		<link rel="stylesheet" type="text/css" href="skins/css/CheckHeader.css" />
		<link rel="stylesheet" type="text/css" href="skins/flags/language-flags/icons.css" />
		<script type="text/javascript">
			document.getElementById('loading-msg').innerHTML = 'Loading Core API...';
		</script>
		<script type="text/javascript" src="lib/3rd_js/extjs4/bootstrap.js"></script>
		<script type="text/javascript" src="lib/3rd_js/startup.js"></script>
		<script type="text/javascript">
			document.getElementById('loading-msg').innerHTML = 'Loading Extensions';
			var QD_GBL_CONF				=<?php print json_encode($GLOBALS['conf']);?>;
			var QD_GBL_SETTING_PANELS	=<?php print json_encode(QDSettingPanels::init());?>
		</script>
		<script type="text/javascript" src="lib/3rd_js/commonfunctions.js"></script>
		<script type="text/javascript" src="lib/3rd_js/css.js"></script>
		<script type="text/javascript" src="lib/3rd_js/ux/SelectGrouping.js"></script>
		<script type="text/javascript" src="lib/3rd_js/ux/Ext.data.ConnectionEx.js"></script>
		<script type="text/javascript" src="lib/3rd_js/ux/Ext.AjaxEx.js"></script>
		<script type="text/javascript" src="lib/3rd_js/ux/AjaxEx.js"></script>
		<script type="text/javascript" src="lib/3rd_js/ux/SimpleIFrame.js"></script>
		<script type="text/javascript" src="lib/3rd_js/ux/thumbnailSelector.js"></script>
		<script type="text/javascript" src="lib/3rd_js/ux/CheckColumn.js"></script>
		<script type="text/javascript" src="lib/3rd_js/ux/QTPreview.js"></script>
		<script type="text/javascript" src="lib/3rd_js/ux/base64.js"></script>
		<script type="text/javascript" src="lib/3rd_js/ux/array.js"></script>
		<script type="text/javascript" src="lib/3rd_js/ux/ImageSelector.js"></script>
		<script type="text/javascript" src="lib/3rd_js/ux/DataView/LabelEditor.js"></script>
		<script type="text/javascript" src="lib/3rd_js/ux/DataView/DragSelector.js"></script>
		<script type="text/javascript" src="lib/3rd_js/ux/Notification.js"></script>
		<script type="text/javascript" src="lib/3rd_js/ux/FileBrowser.js"></script>
		<script type="text/javascript" src="lib/3rd_js/ux/grid/feature/Tileview.js"></script>
		<script type="text/javascript" src="lib/3rd_js/ux/grid/plugin/DragSelector.js"></script>
		<script type="text/javascript" src="lib/3rd_js/ux/container/ButtonSegment.js"></script>
		<script type="text/javascript" src="lib/3rd_js/ux/container/SwitchButtonSegment.js"></script>
		<script type="text/javascript" src="lib/3rd_js/extjs4.old/src/layout/container/AbstractFit.js"></script>
		<script type="text/javascript" src="lib/3rd_js/ux/folderPicker.js"></script>

		<script type="text/javascript" src="lib/3rd_js/stomp/ReconnectingSockJS.js"></script>
		<script type="text/javascript" src="lib/3rd_js/stomp/sockjs-0.3.min.js"></script>
		<script type="text/javascript" src="lib/3rd_js/stomp/stomp.js"></script>

		<link rel="stylesheet" type="text/css" href="lib/3rd_js/ux/grid/feature/Tileview.css"></link>
		<link rel="stylesheet" type="text/css" href="lib/3rd_js/ux/container/ButtonSegment.css"></link>
		<script type="text/javascript">
			document.getElementById('loading-msg').innerHTML = 'Loading Application';
		</script>
		<script type="text/javascript" src="modules/qd/mediadb/serieEditor.js"></script>
		<script type="text/javascript" src="modules/qd/mediadb/seriePanel.js"></script>
		<script type="text/javascript" src="modules/qd/mediadb/serieFileSorter.js"></script>
		<script type="text/javascript" src="modules/qd/mediadb/serieFileSorterConfirmation.js"></script>
		<script type="text/javascript" src="modules/qd/sabnzbd/sabnzbdPanel.js"></script>
		<script type="text/javascript" src="modules/qd/mediadb/movieQTPreview.js"></script>
		<script type="text/javascript" src="modules/qd/mediadb/movieEditor.js"></script>
		<script type="text/javascript" src="modules/qd/mediadb/moviePanel.js"></script>
		<script type="text/javascript" src="modules/qd/nzb/nzbview.js"></script>
		<script type="text/javascript" src="modules/qd/nzb/feeditemdesc.js"></script>
		<script type="text/javascript" src="modules/qd/nzb/feedtab.js"></script>
		<script type="text/javascript" src="modules/qd/nzb/NZBPanel.js"></script>
		<script type="text/javascript" src="modules/qd/mediadb/app.js"></script>
		<script type="text/javascript" src="modules/qd/system/fileExplorer.js"></script>
		<script type="text/javascript" src="modules/qd/mediadb/indexer.js"></script>
		<script type="text/javascript" src="modules/qd/mediadb/xbmcDB.js"></script>
		<script type="text/javascript" src="modules/qd/mediadb/xbmcDBSeriesPanel.js"></script>
	</body>
</html>
