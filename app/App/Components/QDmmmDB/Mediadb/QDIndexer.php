<?php
namespace App\Components\QDmmmDB\Mediadb;
/**
 * CREATE SCHEMA `qdmmmdb` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
 * CREATE TABLE `FIL_FILES` (
 *  `FIL_FOLDER` varchar(1024) DEFAULT NULL,
 *  `FIL_FILE` varchar(1024) DEFAULT NULL,
 *  FULLTEXT KEY `folder` (`FIL_FOLDER`),
 *  FULLTEXT KEY `file` (`FIL_FILE`)
 *) ENGINE=MyISAM DEFAULT CHARSET=utf8'

 */
class QDIndexer extends QDMediaDBProxy{
	static $id=0;

	function svc_showDisks(){
		$t = new Imagick();
		print sprintf("Series : \n");
		foreach($this->folderSeriesList as $k=>$disk){
			print sprintf("%7s [%7s] %s\n",$k,$disk['name'],$disk['path']);
		}
		print sprintf("Movies : \n");
		foreach($this->folderMoviesList as $k=>$disk){
			print sprintf("%7s [%7s] %s\n",$k,$disk['name'],$disk['path']);
		}
	}

	function svc_index(){
		return file_get_contents(dirname(__FILE__).'/../templates/'.str_replace('::svc_','_',__METHOD__).'.tpl');
	}

	function svc_indexAll(){
		$this->match = "!(".join('|',$this->allowedExt).')$!i';
		$allFolders = array();
		$this->QDDb = new QDDB();
		mysql_connect("localhost", $GLOBALS['conf']['qddb']['username'],$GLOBALS['conf']['qddb']['password'] ) or die("Impossible de se connecter : " . mysql_error());
		mysql_set_charset ('utf8');
		mysql_select_db("qdmmmdb");
		$sql = 'truncate table qdmmmdb.FIL_FILES;';
		mysql_query($sql);
		foreach($this->folderSeriesList+$this->folderMoviesList as $v){
			set_time_limit(120);
			$this->getStruct($v['path']);
		}
	}

	function svc_getFiles(){
		$this->QDDb = new QDDB();
		$arr = explode(' ',array_key_exists_assign_default('search',$_REQUEST,''));
		$this->mode = array_key_exists_assign_default('mode',$_REQUEST,'flat');
		if(array_key_exists_assign_default('search',$_REQUEST,'')==''){
			return array();
		}
		$where = '';
		foreach($arr as $v){
			$where .= ' and concat(FIL_FOLDER,FIL_FILE) like "%'.addslashes($v).'%" and FIL_FILE REGEXP "('.join('|',$this->movieExt).')$"';
		}
		$arr = $this->QDDb->query2Array('select * from qdmmmdb.FIL_FILES where true  '.$where);
		switch($this->mode){
			case 'flat':
				foreach($arr as $k=>$v){
					$arr[$k]['FIL_FOLDER'	]=utf8_decode($v['FIL_FOLDER'	]);
					$arr[$k]['FIL_FILE'		]=utf8_decode($v['FIL_FILE'		]);
				}
				return $arr;
			break;
			case 'tree':
				$tree = array();
				foreach($arr as $v){
					$aPath = split('/',$v['FIL_FOLDER']);
					array_shift($aPath);
					$tFolder = &$tree;
					foreach($aPath as $folder){
						if(!array_key_exists($folder,$tFolder)){
							$tFolder[$folder]=array();
						}
						$tFolder=&$tFolder[$folder];
					}
					$tFolder[]=$v['FIL_FILE'];
				}
				self::$id=100;
				$return=array(
					'text'		=> 'root',
					'data'		=> 'root',
					'state'		=> 'open',
					'expanded'	=> true,
					'icon'		=> 'leaf',
					'id'		=> self::$id,
					'children'	=> array()
				);
				self::makeTreeStruct($tree,'',$return);
				return array('root'=>$return);
			break;
		}
	}

	static function makeTreeStruct($tree,$arbo,&$return){
		$n=0;
		foreach($tree as $k=>$v){
			if(is_array($v)){
				$return['children'][$n]=array(
					'text'		=> $k,
					'data'		=> $k,
					'state'		=>'open',
					'expanded'	=> true,
					'id'		=> self::$id++,
					'leaf'		=> false,
					'children'	=> array()
				);
				self::makeTreeStruct($v,$arbo.'/'.$k,$return['children'][$n]);
			}else{
				if($v){
					$return['children'][$k]=array(
						'text'			=> utf8_decode($v),
						'data'			=> utf8_decode($v),
						'state'			=> 'closed',
						'fullfilename'	=> utf8_decode($arbo.'/'.$v),
						'attr'			=> array(
							'fullfilename'	=> utf8_decode($arbo.'/'.$v)
						),
						'leaf'			=> true
					);
				}
			}
			$n++;
		}
	}

	function getStruct($from,$level=0){
		$aFrom = glob($from.'/*');
		foreach($aFrom as $f){
			$f2 = str_replace($from.'/','',$f);
			if(is_dir($f)){
				$this->getStruct($from.'/'.$f2,$level+1);
			}else{
				$d = ToolsFiles::pathinfo_utf($f);
				if (preg_match($this->match, $f)){
					//print $from.'/'.$f2."\n";
					$sql = 'insert into qdmmmdb.FIL_FILES values("'.addslashes($from).'","'.addslashes($f2).'");';
					mysql_query($sql);
					print mysql_error();
				}
			}
		}
	}
}