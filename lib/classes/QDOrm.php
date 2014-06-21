<?php
/*
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
	$r = $oIPR->get(array(
		'cols'	=> array('*'),
		'where'	=> array(
				'IPR_COMPANY_NAME'		=> array('IN',array("SPIE EST",'SNEF')),
				'AND'					=> array(
					'OPE1'					=> array("SQL","IPR_COMPANY_NAME like '%e%'"),
					'OPE2'					=> array("SQL","IPR_COMPANY_NAME like '%a%'"),
				),
				'AND_2'					=> array(
					'OPE1'					=> array("SQL","IPR_COMPANY_NAME like '%e%'"),
					'OPE2'					=> array("SQL","IPR_COMPANY_NAME like '%a%'"),
				)
		),
		'debugsql'	=> 1,
		'start'		=> 0,
		'limit'		=> 2,
	));
}
 */
class QDOrm {
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
		if(!is_array($this->pk)){
			$this->pk=array($this->pk);
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

	function where($o,&$query,&$aVal,$joinOperator,$level){
		$indent="\n".str_pad("\t",$level*2);
		foreach($o as $col=>$val){
			if(preg_match('!^(OR|AND)(_[0-9]*){0,1}$!',$col,$m)){
				$query.=$indent.$joinOperator.'(';
				$this->where($val,$query,$aVal,$m[1]=='OR'?'AND':'OR',$level+1);
				$query.=$indent.")";
			}else{
				$operator=false;
				if(!is_array($val)){
					$val=array('=',$val);
				}
				$operator = $val[0];
				if($operator=='SQL'){
					$operator=false;
				}

				if($operator== 'IN' ){
					$t='';
					$inSepa='';
					foreach($val[1] as $v){
						$t .= $inSepa . "'" . addslashes($v) . "'";
						$inSepa=',';
					}
					$val[1]=sprintf('%s IN (%s) ',$this->_map($col),$t);
					$operator=false;
				}

				if($operator== 'BETWEEN' ){
					$tmp=sprintf('%s BETWEEN :prm_col_%s and :prm_col_%s',$this->_map($col),count($aVal),count($aVal)+1);
					$aVal[':prm_col_'.count($aVal)] = $val[1];
					$aVal[':prm_col_'.count($aVal)] = $val[2];
					$val[1] = $tmp;
					$operator=false;
				}

				if($operator){
					$query .= sprintf("%s %s(%s %s :prm_col_%s) ",$sepa,$indent,$this->_map($col),$operator,count($aVal));
					$aVal[':prm_col_'.count($aVal)] = $val[1];
				}else{
					$query .= sprintf("%s %s(%s) ",$sepa,$indent,$val[1]);
				}
				$sepa	= $indent.$joinOperator." ";
				$k++;
			}
		}
	}
	/**
	 *
	 * @param unknown $o
	 * @throws Exception
	 */
	public function get($o,$options=array()){
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
		$where = '';

		$this->where(akead('where',$o,array()),$where,$aVal,'AND',0);

		if(array_key_exists('wheresql',$o)){
			$query .= 	$o['wheresql'];
		}else{
			$query .= ($where=='')?' TRUE ':$where;
		}

		if(array_key_exists('limit',$o)){
			$query .= sprintf("\nLIMIT %s,%s",akead('start',$o,0),$o['limit']);
		}

		/*foreach($aVal as $key=>$val){
			if(!is_null($val)){
				db($key."=>".json_encode($val));
				$query = str_replace($key,'"'+addslashes($val)+"'",$query);
			}
		}*/

		$stmt = $this->getConnection()->prepare($query);

		foreach($aVal as $key=>$val){
			if(!is_null($val)){
				$stmt->bindValue($key, $val);
			}else{
				$stmt->bindValue($key, null,PDO::PARAM_NULL);
			}
		}

		if($debugsql){
			fb($query."\n".json_encode($aVal));
		}

		$this->getConnection()->setAttribute(PDO::ATTR_CASE,PDO::CASE_LOWER);

		if ($stmt->execute()){
			if(akead('uniqKey',$options,false)){
				$tmp		= $stmt->fetchAll(PDO::FETCH_ASSOC);
				$aResult	= array();
				foreach($tmp as $v){
					$aResult[$v[strtolower($options['uniqKey'])]]=$v;
				}
				return $aResult;
			}else{
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
		}else{
			throw new Exception($query.'; / '.json_encode($stmt->errorInfo()),1);
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
		$bAllPkPresent=true;
		$pkVal=array();
		$aPk=array();
		foreach($this->pk as $key){
			$aPk[]=strtolower($key);
			$key=strtolower($key);
			if(!array_key_exists($key,$o) || is_null($o[$key])){
				$bAllPkPresent=false;
			}else{
				$pkVal[$key] = $o[$key];
			}
		}
		$duplicateQuery='';
		if($bAllPkPresent){
			$mode	='update';
			$query	= sprintf("INSERT INTO %s  \nSET ",$this->table);
		}else{
			$mode	='insert';
			$query	= sprintf("INSERT INTO  %s \nSET ",$this->table);
		}

		$sepa	= '';
		foreach($o as $col=>$val){
			$query .= sprintf("%s \n\t%s=:%s ",$sepa,$this->_map($col),strtolower($col));
			$duplicateQuery .= sprintf("%s \n\t%s=:%s ",$sepa,$this->_map($col),strtolower($col));
			$sepa	= ',';
			if ($mode=='insert'|| ($mode=='update' && !in_array($col,$aPk))){
			}
		}

		if ($mode=='update'){
			/*$query .= sprintf(" \nWHERE ");
			$sepa='';
			foreach($pkVal as $key=>$val){
				$query .= sprintf(" \n  %s \n\t(%s=:%s) ",$sepa,$this->_map($key),$key);
				$sepa=' AND ';
			}*/
			$query .= "\n\nON DUPLICATE KEY UPDATE \n".$duplicateQuery;
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
		//db($query);//die();
		if($debugsql){
			fb($query);
		}
		if ($stmt->execute()){
			if($mode=='update'){
				return count($pkVal)==1?array_pop($pkVal):$pkVal;
			}else{
				return $this->getConnection()->lastInsertId();
			}
		}else{
			throw new Exception(json_encode($stmt->errorInfo()),1);
		}
	}

	public function __get($o){
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
				$val=array('=',$val);
			}
			$operator = $val[0];
			if($operator=='SQL'){
				$operator=false;
			}
			if($operator== 'IN' ){
				$t='';
				$inSepa='';
				foreach($val[1] as $v){
					$t .= $inSepa . "'" . addslashes($v) . "'";
					$inSepa=',';
				}
				$val[1]=sprintf('%s IN (%s) ',$this->_map($col),$t);
				$operator=false;
			}

			if($operator){
				$query .= sprintf("%s \n%s %s :prm_col_%s ",$sepa,$this->_map($col),$operator,$k);
				$aVal[':prm_col_'.$k] = $val[1];
			}else{
				$query .= sprintf("%s \n %s ",$sepa,$val[1]);
			}
			$sepa	= " AND ";
			$k++;
		}

		if(array_key_exists('wheresql',$o)){
			$query .= 	$o['wheresql'];
		}else{
			if($k==0){
				$query .= ' TRUE ';
			}
		}
		if(array_key_exists('limit',$o)){
			$query .= sprintf("\nLIMIT %s,%s",akead('start',$o,0),$o['limit']);
		}

		/*foreach($aVal as $key=>$val){
		if(!is_null($val)){
		db($key."=>".json_encode($val));
		$query = str_replace($key,'"'+addslashes($val)+"'",$query);
		}
		}*/

		$stmt = $this->getConnection()->prepare($query);

		foreach($aVal as $key=>$val){
			if(!is_null($val)){
				$stmt->bindValue($key, $val);
			}else{
				$stmt->bindValue($key, null,PDO::PARAM_NULL);
			}
		}

		if($debugsql){
			fb($query."\n".json_encode($aVal));
		}

		$this->getConnection()->setAttribute(PDO::ATTR_CASE,PDO::CASE_LOWER);

		if ($stmt->execute()){
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}else{
			throw new Exception(json_encode($stmt->errorInfo()),1);
		}
	}

}