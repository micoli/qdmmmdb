<?php
/**
select group_concat(concat("'",c.COLUMN_NAME,"'") order by c.ORDINAL_POSITION separator "\t\t\t\t\t,\n")
from information_schema.COLUMNS c
where c.TABLE_NAME='client' and c.TABLE_SCHEMA='test'

function pub_testOrm($o){
	header('Content-Type: text/html, charset=utf-8');

	$oIPR = new IPR_IMPORTED_PROSPECT();
	$r = $oIPR->set(array(
		'IPR_COMPANY_NAME'		=> "eezeze",
		'IPR_COMPANY_NUMBER'	=> "eezeze"
	));
	db($r);
	$r = $oIPR->set(array(
		'IPR_ID'				=> 3,
		'IPR_COMPANY_NAME'		=> "aaa",
		'IPR_COMPANY_NUMBER'	=> "bbb"
	));
	db($r);
	$r = $oIPR->get(array(
		'cols'	=> array('*'),
		'where'	=> array(
			'IPR_COMPANY_NAME'		=> array('IN',array("aaa")),
			'OPE1'					=> array("SQL","IPR_COMPANY_NAME like '%a%'"),
		),
		'start' => 0,
		'limit' => 2,
	));
	db($r);
}

 */
class QDOrm{
	static $dbCnxs=array();

	public $table	= null;
	public $pk		= null;
	public $fields	=  array();

	public static function addConnection($name,$cnx){
		self::$dbCnxs[$name]=$cnx;
	}

	public function getConnectionName(){}

	/**
	 *
	 * @param array $arr
	 * @return boolean
	 */
	private function isIndexedArray($arr){
		return array_keys($arr) == range(0, count($arr) - 1);
	}
	/**
	 * @return QDPDO
	 */
	private function getConnection(){
		return self::$dbCnxs[$this->getConnectionName()];
	}

	/**
	 *
	 */
	function __construct(){
		$this->map=array();
		foreach($this->fields as $col){
			$this->map[strtolower($col)]=$col;
		}
	}

	/**
	 *
	 * @param unknown $col
	 * @return unknown
	 */
	function _map($col){
		if(array_key_exists(strtolower($col),$this->map)){
			return $this->map[strtolower($col)];
		}
		return $col;
	}

	/**
	 *
	 * @param unknown $o
	 * @throws Exception
	 */
	public function get($o){
		$debugsql=false;
		if(akead('debugsql',$o,false)){
			$debugsql=true;
			unset($o['debugsql']);
		}
		$o = array_merge(array(
				'cols'	=> array('*'),
				'where'	=> array()
		),$o);

		$cols='';
		$sepa='';
		foreach($o['cols'] as $k=>$v){
			$cols.= $sepa.$this->_map($v);
			$sepa =' , ';
		}

		$query= sprintf("SELECT %s \nFROM %s TFROM \nWHERE ",$cols,$this->table);

		$k=0;
		$sepa='';
		$aVal=array();
		foreach($o['where'] as $col=>$val){
			$operator=false;
			if(!is_array($val)){
				$operator="=";
			}else{
				$operator=$val[0];
				if($operator=='SQL'){
					$operator=false;
				}
				if($operator== 'IN' ){
					$t='';
					$inSepa='';
					foreach($val[1] as $v){
						$t.=$sepa."'".addslashes($v)."'";
						$inSepa=',';
					}
					$val[1]=sprintf('%s IN (%s) ',$this->_map($col),$t);
					$operator=false;
				}
			}
			if($operator){
				$query .= sprintf("%s \n%s %s :prm_col_%s ",$sepa,$this->_map($col),$operator,$k);
				$aVal[':prm_col_'.$k]=$val;
			}else{
				$query .= sprintf("%s \n %s ",$sepa,$val[1]);
			}
			$sepa	= " AND ";
			$k++;
		}

		if($k==0){
			$query.=' TRUE ';
		}
		if(array_key_exists('limit',$o)){
			$query .= sprintf("\nLIMIT %s,%s",akead('start',$o,0),$o['limit']);
		}
		$stmt = $this->getConnection()->prepare($query);

		foreach($aVal as $key=>$val){
			if(!is_null($val)){
				$stmt->bindValue($key, $val);
			}
		}
		if($debugsql){
			db($query);
		}
		//db($aVal);

		$this->getConnection()->setAttribute(PDO::ATTR_CASE,PDO::CASE_LOWER);

		if ($stmt->execute()){
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}else{
			throw new Exception(json_encode($stmt->errorInfo()),1);
		}
	}

	/**
	 *
	 * @param unknown $o
	 * @throws Exception
	 * @return unknown
	 */
	public function set($o){
		if($this->isIndexedArray($o)){
			$aIds = array();
			foreach($o as $newO){
				$aIds[]=$this->set($o);
			}
			return $aIds;
		}
		$debugsql=false;
		if(akead('debugsql',$o,false)){
			$debugsql=true;
			unset($o['debugsql']);
		}
		$this->getConnection()->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		$o=array_change_key_case($o,CASE_LOWER);
		$pk=strtolower($this->pk);
		if(array_key_exists($pk,$o) && !is_null($o[$pk])){
			$mode	='update';
			$query	= sprintf("UPDATE %s  \nSET ",$this->table);
			$pkVal	= $o[$pk];
		}else{
			$mode='insert';
			$query	= sprintf("INSERT INTO  %s \nSET ",$this->table);
		}

		$sepa	= '';
		foreach($o as $col=>$val){
			if ($mode=='insert'|| ($mode=='update' && $col!=$pk)){
				$query .= sprintf("%s \n%s=:%s ",$sepa,$this->_map($col),strtolower($col));
				$sepa	= ',';
			}
		}

		if ($mode=='update'){
			$query .= sprintf(" \nwhere \n%s=:%s ",$this->_map($pk),$pk);
		}

		$stmt = $this->getConnection()->prepare($query);
		foreach($o as $col=>$val){
			if (($val == 'NULL') or is_null($val)){
				$Value = NULL;
				$stmt->bindValue(':'.$col, $val, PDO::PARAM_NULL);
			}else{
				$stmt->bindValue(':'.$col, $val);
			}
		}

		if($debugsql){
			print $query."\n";
		}
		if ($stmt->execute()){
			if($mode=='update'){
				return $pkVal;
			}else{
				return $this->getConnection()->lastInsertId();
			}
		}else{
			throw new Exception(json_encode($stmt->errorInfo()),1);
		}
	}
}