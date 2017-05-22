<?php
namespace App\Components\QDmmmDB\Mediadb;
class QDFilesProxy{
	static $arrPaths=array();
	function init(){
		self::$arrPaths=array(
			'#'		=>'/',
			'I'		=>'/Users/o.michaud/Documents/tmpStruct.qdmmmdb/I',
			'J'		=>'/Users/o.michaud/Documents/tmpStruct.qdmmmdb/J',
		);
	}

	function getPath(){
		$subpath	= array_key_exists_assign_default('node', $_REQUEST, '/');
		if(preg_match('!ext\-gen!',$subpath)){
			$subpath='/';
		}
		$subpath = preg_replace('!:!m','/',$subpath);
		$path	= self::$arrPaths[$_REQUEST['root']].$subpath;
		return $path;
	}

	function svc_getTree() {
		self::init();
		$res = array();
		$path = self::getPath();
		foreach (glob($path.'*',GLOB_ONLYDIR) as $v) {
			$res[] = array(
				'text'		=> basename($v),
				'fullpath'	=> str_replace(self::$arrPaths[$_REQUEST['root']],'',$v).'/',
				'id'		=> preg_replace('!/!m',':',str_replace(self::$arrPaths[$_REQUEST['root']],'',$v).'/')
			);
		}
		return ($res);
	}

	function filePerms($filename){
		$perms = fileperms($filename);
		$aReturn=array();

		if (($perms & 0xC000) == 0xC000) {
			// Socket
			$info = 's';
		} elseif (($perms & 0xA000) == 0xA000) {
			// Lien symbolique
			$info = 'l';
		} elseif (($perms & 0x8000) == 0x8000) {
			// R�gulier
			$info = '-';
		} elseif (($perms & 0x6000) == 0x6000) {
			// Block special
			$info = 'b';
		} elseif (($perms & 0x4000) == 0x4000) {
			// Dossier
			$info = 'd';
		} elseif (($perms & 0x2000) == 0x2000) {
			// Caract�re sp�cial
			$info = 'c';
		} elseif (($perms & 0x1000) == 0x1000) {
			// pipe FIFO
			$info = 'p';
		} else {
			// Inconnu
			$info = 'u';
		}
		$aReturn['type']=$info;

		$aReturn['owner']='';
		// Autres
		$aReturn['owner'] .=	(($perms & 0x0100) ? 'r' : '-');
		$aReturn['owner'] .=	(($perms & 0x0080) ? 'w' : '-');
		$aReturn['owner'] .=	(($perms & 0x0040) ?
								(($perms & 0x0800) ? 's' : 'x' ) :
								(($perms & 0x0800) ? 'S' : '-'));

		$aReturn['group']='';
		// Groupe
		$aReturn['group'] .= 	(($perms & 0x0020) ? 'r' : '-');
		$aReturn['group'] .= 	(($perms & 0x0010) ? 'w' : '-');
		$aReturn['group'] .= 	(($perms & 0x0008) ?
								(($perms & 0x0400) ? 's' : 'x' ) :
								(($perms & 0x0400) ? 'S' : '-'));

		$aReturn['all'] ='';
		// Tout le monde
		$aReturn['all'] .= 		(($perms & 0x0004) ? 'r' : '-');
		$aReturn['all'] .= 		(($perms & 0x0002) ? 'w' : '-');
		$aReturn['all'] .= 		(($perms & 0x0001) ?
								(($perms & 0x0200) ? 't' : 'x' ) :
								(($perms & 0x0200) ? 'T' : '-'));

		$aReturn['str']=$aReturn['type'].$aReturn['owner'].$aReturn['group'].$aReturn['all'];
		return $aReturn;
	}

	function svc_getFiles() {
		self::init();
		$res = array();

		$path = self::getPath();

		foreach (glob($path.'*') as $v) {
			$fdetail	= ToolsFiles::pathinfo_utf($v);
			$size		= filesize($v);
			$arrExt=array(
				'mov'	=> array("avi", "mpg", "mpeg", "mkv", "ogm", "mpeg", "divx", "ram", "mp4"),
				'sub'	=> array("srt", "sub", "idx"),
				'audio'	=> array("mp3", "mp2", "flac"),
				'txt'	=> array("xml", 'nfo', 'json'),
				'img'	=> array("tbn", 'jpg', 'gif', 'png')
			);
			$preview='';
			if(is_dir($v)){
				$type='folder';
			}else{
				$type='unknown';
				foreach($arrExt as $t=>$aExt){
					if(in_array(strtolower($fdetail['extension']),$aExt)){
						$type=$t;
						break;
					}
				}
			}
			$owner	= posix_getpwuid(fileowner($v));
			$group	= posix_getgrgid(filegroup($v));
			$perms	= self::filePerms($v);
			$id		= preg_replace('!/!m',':',str_replace(self::$arrPaths[$_REQUEST['root']],'',$v).'/');
			$ext	= strtolower($fdetail['extension']);
			$style	= "border: medium none transparent;height: 100px;padding: 5px;width: 100%;";
			switch ($type){
				case 'img':
					$preview = '<img src="p/QDFilesProxy.getPreview/?type=imgfull&root='.$_REQUEST['root'].'&id='.$id.'&ext='.$ext.'" style="'.$style.'" />';
				break;
				case 'txt':
					$preview = '<iframe src="p/QDFilesProxy.getPreview/?type=txt&root='.$_REQUEST['root'].'&id='.$id.'&ext='.$ext.'" style="'.$style.'" ></iframe>';
				break;
			}
			$res[]		= array(
				'filename'	=> basename($v),
				'id'		=> $id,
				'folder'	=> $path,
				'size'		=> $size,
				'group'		=> $group['name'],
				'owner'		=> $group['name'],
				'perms'		=> $perms['str'],
				'permt'		=> $perms['type'],
				'permo'		=> $perms['owner'],
				'permg'		=> $perms['group'],
				'perma'		=> $perms['all'],
				'mtime'		=> $fdetail['mtime'],
				'ext'		=> $ext,
				'sizef'		=> Tools::getSymbolByQuantity($size),
				'sizefs'	=> number_format($size),
				'type'		=> $type,
				'preview'	=> $preview,
				'root'		=> $_REQUEST['root'],
			);
		}
		return ($res);
	}

	function svc_getPreview(){
		self::init();
		$path	= self::$arrPaths[$_REQUEST['root']].preg_replace('!:!m','/',$_REQUEST['id']);
		$path = preg_replace('!\/$!','',$path);
		switch ($_REQUEST['type']){
			case 'txt':
				header('Content-Type: text/html');
				die("<pre>".htmlentities(substr(file_get_contents($path),0,1024))."</pre>");
			break;
			case 'imgfull':
				$size = getimagesize($path);
				header('Content-Type: '.$size['mime']);
				die(file_get_contents($path));
			break;
			case 'img':
				$sizes=split('x',$_REQUEST['c']);
				$new_w=$sizes[0];
				$new_h=$sizes[1];
				if (preg_match('/jpg|jpeg|tbn/',$_REQUEST['ext'])){
					$src_img=imagecreatefromjpeg($path);
				}
				if (preg_match('/png/',$_REQUEST['ext'])){
					$src_img=imagecreatefrompng($path);
				}
				$old_x=imageSX($src_img);
				$old_y=imageSY($src_img);
				if ($old_x > $old_y) {
					$thumb_w=$new_w;
					$thumb_h=$old_y*($new_h/$old_x);
				}
				if ($old_x < $old_y) {
					$thumb_w=$old_x*($new_w/$old_y);
					$thumb_h=$new_h;
				}
				if ($old_x == $old_y) {
					$thumb_w=$new_w;
					$thumb_h=$new_h;
				}
				header('Content-Type: image/png');
				$dst_img=ImageCreateTrueColor($thumb_w,$thumb_h);
				imagecopyresampled($dst_img,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y);
				imagepng($dst_img);
				imagedestroy($dst_img);
				imagedestroy($src_img);
			break;
		}
	}
}