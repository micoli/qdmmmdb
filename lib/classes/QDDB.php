<?php
include('QDException.php');;
/**
 * @package QDDB
 * @filesource
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
	class QDDB {
		var $v=array('pdoDB' => '', 'debug' => '0', 'dbEngine' => '', 'sth' => '', 'mode' => PDO::FETCH_ASSOC);

		// {{{ constructor()
		/**
		* [EN]constructor of the main class, nothing to specifie,
		* [EN]all variables to blank or default
		* [FR]Constructeur de la classe. Rien √† sp√©cifier,
		* [FR]toutes les variables initialis√©es √† blank ou par d√©faut
		*
		* @access public
		*/
		function QDDB($dbConnectionString=null, $username=null, $password=null){
			if(is_null($dbConnectionString)) $dbConnectionString = $GLOBALS['conf']['qddb']['connection'];
			if(is_null($username)) $username = $GLOBALS['conf']['qddb']['username'];
			if(is_null($password)) $password = $GLOBALS['conf']['qddb']['password'];

			try{
				$this->v['dbEngine']=substr($dbConnectionString, 0, strpos($dbConnectionString, ':'));
				$this->v['dbConnectionString'] = $dbConnectionString;
				$tmp = split(":",$dbConnectionString);
				$arrTmp = split(";",$tmp[1]);
				foreach($arrTmp as $v){
					$tmp = split("=",$v);
					$this->v['dbprm'][$tmp[0]] = $tmp[1];
				}
				$this->v['username'] = $username;
				$this->v['password'] = $password;
				$this->connect();

			}
			catch (PDOException $e){
				throw new QDException("-1", str_replace($username, "***", $e->getMessage()), $e->getFile(), $e->getLine(), -1, array());
			}
		}// }}}

		function connect(){
			$this->v['pdoDB'] = new PDO($this->v['dbConnectionString'], $this->v['username'], $this->v['password']);
		}

		function __sleep(){
			unset($this->v['pdoDB']);
			unset($this->v['sth']);
			return( array_keys( get_object_vars( &$this ) ) );
		}

		function __wakeup(){
			$this->connect();
			$this->v['sth']=NULL;
			//$this->v['pdoDB']=NULL;
		}

		/**
		 * R√©cup√®re le moteur de BD utilis√© avec l'objet en cours
		 *
		 * @return string
		 */
		function getDbEngine(){
			return $this->v["dbEngine"];
		}// getDbEngine

		/**
		 * Execute une requete sur la BD (ne renvoie pas de jeux de r√©sultat, mais le nombre d'enregistrements touch√©es)
		 *
		 * @param query : requ√™te √† √©xecuter
		 *
		 * @return int
		 */
		function execute($query,$arrWhere=array()){
			try{
				//if ($this->v["pdoDB"]->exec($this->v["pdoDB"]->quote($query))){
				$this->v['sth'] = $this->v['pdoDB']->prepare($query);

				if($this->v["debug"]==1) print "<br>".$query;
				if ($this->v['sth']->execute($arrWhere)){
					return 1;
				}
				else{
					if ($this->v["debug"]==1){
						$arrError = $this->v["pdoDB"]->errorInfo();
						throw new QDException ("<b>Erreur SQL ".$arrError[0]."</b> : ".$arrError[2]
							." <br> Lors de la requÔøΩte : <i>".$query."</i>");
					}
					else
						return -1;
				}
			}
			catch (PDOException $e){
				throw new QDException("-1", $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
			catch(Exception $e){
				throw new QDException("-1", $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
		} // Execute


		/**
		 * Effectue la manipulation BD pour effacer un enregistrement
		 *
		 * @param tablename : Nom de la table
		 * @param sqlWhere : Chaine de la clause WHERE (peut √™tre parametr√©e ou non)
		 * @param arrWhere : En cas de sqlWhere parametr√©e, doit contenir les valeurs correspondantes (avec marqueur de positionnement '?')
		 *
		 * @return boolean
		 */
		function dbDelete($tablename,$sqlWhere='1=1', $arrWhere= array(),$secure=true){
			if (!$GLOBALS['gblQD']->isStandAlone()){
				if (!$GLOBALS['gblQD']->v['QDSec']->getUserTableRights($tablename,'D')) return -2;
			}
			try{
				$query='DELETE FROM '.$tablename.' WHERE '.$sqlWhere;
				$this->v['sth'] = $this->v['pdoDB']->prepare($query);
				if ($this->v['sth']->execute($arrWhere)){
					return 1;
				}else{
					if ($this->v["debug"]==1){
						$arrError = $this->v['sth']->errorInfo();
						throw new QDException ('<b>Erreur SQL '.$arrError[0].'</b> : '.$arrError[2]
							.' <br> Lors de la requÔøΩte : <i>'.$query.'</i>');
					}
					else
						return -1;
				}
			}
			catch(PDOException $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
			catch(Exception $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
		} // dbDelete

		/**
		 * Effectue la manipulation BD pour insÔøΩrer/mettre ÔøΩ jour un enregistrement
		 *
		 * @param action  : INSERT ou UPDATE
		 * @param tablename : Nom de la table
		 * @param arrKeys : Tableau contenant la liste des champs
		 * @param arrValues : Tableau contenant la liste des valeurs (doit √™tre ordonn√© de la m√™me fa√ßon que la liste des champs)
		 * @param sqlWhere : Chaine de la clause WHERE (peut √™tre parametr√©e ou non)
		 * @param arrWhere : En cas de sqlWhere parametr√©e, doit contenir les valeurs correspondantes (avec marqueur de positionnement "?")
		 *
		 * @return boolean
		 */
		function dbInsertOrUpdateOneArray($action, $tablename, $arrKeysValues, $sqlWhere='1', $arrWhere='',$secure=true){
			return $this->dbInsertOrUpdate($action, $tablename, array_keys($arrKeysValues),array_values($arrKeysValues), $sqlWhere, $arrWhere,$secure);
		}
		function dbInsertOrUpdate($action, $tablename, $arrKeys, $arrValues, $sqlWhere='1', $arrWhere='',$secure=true){
			$this->v['sth']=NULL;
			try{
				if ($action == 'INSERT'){
					if (is_array($arrKeys)){
						$strKeys= '('.implode (', ', $arrKeys).')';
					}else{
						$strKeys='';
					}
					// Je cr√©√© la chaine de caract√™res contenant autant de ? que de champs √† ins√©rer
					$strValues = str_pad(' ? ', 3*(count($arrValues)-1)+3, ',? ');
					$query='INSERT INTO '.$tablename.' '.$strKeys.' VALUES ('.$strValues.')';
					//print "\n".$query."<br>\n";
					//db($arrKeys  );
					//db($arrValues);

					$this->v['sth'] = $this->v['pdoDB']->prepare($query);

				}else{ // end if INSERT
					$strKeys= implode ('= ?, ', $arrKeys).' = ?';
					$query='UPDATE '.$tablename.' SET '.$strKeys.' WHERE '.$sqlWhere;
					$this->v['sth'] = $this->v['pdoDB']->prepare($query);
				}// end if UPDATE
				$arrError = $this->v['pdoDB']->errorInfo();
				if (($arrError[0]+0)>0){
						throw new QDException ('<b>Erreur SQL '.$arrError[0].'</b> : '.$arrError[2]
							.' <br> Lors de la requÔøΩte : <i>'.$query.'</i>');

				}
				if ($this->v['debug']==1) echo $query . '/arrKeys: '.implode(',',$arrKeys) .'/arrValues:'.implode(',',$arrValues).'/sqlWhere:'.$sqlWhere;//.'/arrWhere:'.implode(',',$arrWhere);
				// Rajout d'un √©l√©ment vide en t√™te de tableau, car les index de ces tableaux doivent commencer √† 1, et non √† 0
				if (is_array($arrWhere)){
					//array_unshift(&$arrValues, ' ');
					// Si arrWhere est pr√©sent, on fusionne les deux tableaux
					$arrValues = array_merge($arrValues, $arrWhere);
				}
				foreach ($arrValues as $Vkey=> &$Value){
					if ($this->v['debug']==1) echo '<br/>/bindParam: '.($Vkey) ."  " .htmlentities($Value).'<br/>';//.'/arrWhere:'.implode(',',$arrWhere);
					if (($Value == 'NULL') or is_null($Value)){
						$Value = NULL;
						$this->v['sth']->bindParam($Vkey+1, $Value, PDO::PARAM_NULL);
					}else{
						$this->v['sth']->bindParam($Vkey+1, $Value);
					}
				}
				$this->v['sth']->execute();
				$arrError = $this->v['sth']->errorInfo();
				if (($arrError[0]+0)>0){
					//print "<b>Erreur SQL ".$arrError[0]."</b> : ".$arrError[2]." <br> Lors de la requÔøΩte : <i>".$query."</i>";
					if ($this->v['debug']==1){
						$arrError = $this->v['sth']->errorInfo();
						throw new QDException ('<b>Erreur SQL '.$arrError[0].'</b> : '.$arrError[2]
							.' <br> Lors de la requÔøΩte : <i>'.$query.'</i>');
					}else{
						return -1;
					}
				}else{
					return 1;
				}
			}catch(PDOException $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}catch(Exception $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
		} // dbInsertorUpdate

		/**
		 * Effectue la manipulation BD pour ins√©rer un enregistrement
		 *
		 * @param tablename : Nom de la table
		 * @param arrKeys : Tableau contenant la liste des champs
		 * @param arrValues : Tableau contenant la liste des valeurs (doit √™tre ordonn√© de la m√™me fa√ßon que la liste des champs)
		 *
		 * @return boolean
		 */
		function dbInsert($tablename, $arrKeys, $arrValues,$secure=true){
			if ($secure && array_key_exists('gblQD',$GLOBALS) && !$GLOBALS['gblQD']->isStandAlone()){
				if (!$GLOBALS['gblQD']->v['QDSec']->getUserTableRights($tablename,'I')) return -2;
			}
			return $this->dbInsertOrUpdate('INSERT', $tablename, $arrKeys, $arrValues,'','',$secure);
		}

		/**
		 * Effectue la manipulation BD pour mettre ÔøΩ jour un enregistrement
		 *
		 * @param tablename : Nom de la table
		 * @param arrKeys : Tableau contenant la liste des champs
		 * @param arrValues : Tableau contenant la liste des valeurs (doit ÔøΩtre ordonnÔøΩ de la mÔøΩme faÔøΩon que la liste des champs)
		 * @param sqlWhere : Chaine de la clause WHERE (peut ÔøΩtre parametrÔøΩe ou non)
		 * @param arrWhere : En cas de sqlWhere parametrÔøΩe, doit contenir les valeurs correspondantes (avec marqueur de positionnement '?')
		 *
		 * @return boolean
		 */
		function dbUpdate($tablename, $arrKeys, $arrValues, $sqlWhere='1', $arrWhere='',$secure=true){
			if (!$GLOBALS['gblQD']->isStandAlone()){
				if (!$GLOBALS['gblQD']->v['QDSec']->getUserTableRights($tablename,'U')) return -2;
			}
			return $this->dbInsertOrUpdate('UPDATE', $tablename, $arrKeys, $arrValues, $sqlWhere, $arrWhere,$secure);
		}

		/**
		 * Retourne le dernier ID inser√©
		 *
		 * @return string
		 */
		function getLastId(){
			try{
				return $this->v['pdoDB']->lastInsertId();
			}
			catch(PDOException $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
			catch(Exception $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
		} // getLastId

		/**
		 * ExÔøΩcute une requete SQL, et renvie un tableau
		 *
		 * @param query : String contenant la requete parametr√©e
		 * @param sqlWhere : Chaine de la clause WHERE (peut ÔøΩtre parametrÔøΩe ou non)
		 * @param arrWhere : Tableau contenant  les valeurs correspondantes (avec marqueur de positionnement '?' ou emplacement nommÔøΩs)
		 * @param mode : contenu du tableau (par d√©faut √† PDO::FETCH_ASSOC
		 *
		 * @return boolean
		 */
		function query2Array($query, $arrWhere=array(), $mode = PDO::FETCH_ASSOC){
				//$uni=uniqid('_');
				$this->v['sth']=NULL;
				if($this->v["debug"]==1) print "<br>".$query.'/'.$arrWhere;
				$this->v['sth'] = $this->v['pdoDB']->prepare($query);
				//QDSajax_add_returned_error('query2Array',$query);
				if (!$this->v['sth']){
					print $query . '  s\'est mal d√©roul√©e QDDB.php';
					return array();
				}
				if ($this->v['sth']->execute($arrWhere)){
					return $this->v['sth']->fetchAll($mode);
				}else{
					if ($this->v["debug"]==0){
						$arrError = $this->v['sth']->errorInfo();
						throw new QDException ("<b>Erreur SQL ".$arrError[0]."</b> : ".$arrError[2]." <br> Lors de la requ√™te : <i>".$query."</i>");
					}else{
						return -1;

					}
				}

		}// query2Array

		/**
		 * ExÔøΩcute une requete SQL, et renvie un tableau
		 *
		 * @param query : String contenant la requÔøΩte parametrÔøΩe
		 * @param UnikKey : champ servant de clÔøΩ unique
		 * @param sqlWhere : Chaine de la clause WHERE (peut ÔøΩtre parametrÔøΩe ou non)
		 * @param arrWhere : Tableau contenant  les valeurs correspondantes (avec marqueur de positionnement '?' ou emplacement nommÔøΩs)
		 * @param mode : contenu du tableau (par dÔøΩfaut ÔøΩ PDO::FETCH_ASSOC
		 *
		 * @return boolean
		 */
		function query2ArrayUnikKey($query, $UnikKey,$arrWhere=array(), $mode = PDO::FETCH_ASSOC){
			$tmparr=array();
			foreach($this->query2Array($query, $arrWhere, $mode) as $k=>$v){
				$tmparr[$v[$UnikKey]]=$v;
			}
			return $tmparr;
		}// query2ArrayUnikKey

		/**
		 * ExÔøΩcute une requete SQL, et renvie un tableau
		 *
		 * @param $table : String contenant la table
		 * @param $ColValue : String contenant le champ de la valeur
		 * @param UnikKey : champ servant de clÔøΩ unique
		 * @param sqlWhere : Chaine de la clause WHERE (peut ÔøΩtre parametrÔøΩe ou non)
		 * @param arrWhere : Tableau contenant  les valeurs correspondantes (avec marqueur de positionnement '?' ou emplacement nommÔøΩs)
		 * @param mode : contenu du tableau (par dÔøΩfaut ÔøΩ PDO::FETCH_ASSOC
		 *
		 * @return boolean
		 */
		function query2TwoColUnikKey($table,$colValue, $unikKey,$arrWhere=array(), $mode = PDO::FETCH_ASSOC){
			foreach($this->query2Array("select $unikKey,$colValue from $table", $arrWhere, $mode) as $k=>$v){
				$tmparr[$v[$unikKey]]=$v[$colValue];
			}
			return $tmparr;
		}// query2TwoColUnikKey

		/**
		 * ExÔøΩcute une requete SQL, et stocke un objet PDoStatement
		 *
		 * @param query : String contenant la requÔøΩte parametrÔøΩe
		 * @param sqlWhere : Chaine de la clause WHERE (peut ÔøΩtre parametrÔøΩe ou non)
		 * @param arrWhere : Tableau contenant  les valeurs correspondantes (avec marqueur de positionnement '?' ou emplacement nommÔøΩs)
		 * @param mode : contenu du tableau (par dÔøΩfaut ÔøΩ PDO::FETCH_ASSOC
		 *
		 * @return bool
		 */
		function query2Sth($query, $arrWhere=array(), $mode = PDO::FETCH_ASSOC){
			try{
				$this->v['sth']=NULL;
				$this->v['sth'] = $this->v['pdoDB']->prepare($query);
				//echo $query.'YYY'.method_exists($this->v['sth'], 'execute');
				//debug_var($query);
				//db($query);
				//db($arrWhere);
				if (is_object($this->v['sth']) && ($this->v['sth']->execute($arrWhere))){
					$arrError = $this->v['sth']->errorInfo();
					$this->v['mode'] = $mode;
					return 1;
				}
				else{
					$arrError =array();
					if (is_object($this->v['sth'])){
						$arrError = $this->v['sth']->errorInfo();
					}
					if ($this->v['debug']==0){
						throw new QDException ('<b>Erreur SQL '.$arrError[0].'</b> : '.$arrError[2]
							.' <br> Lors de la requÔøΩte : <i>'.$query.'</i>');
					}
					else{
						$this->v['error']=$arrError;
						return -1;
					}
				}
			}
			catch(PDOException $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
			catch(Exception $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
		}// query2Array

		/**
		 * ExÔøΩcute Fetch sur le PDO Statement stockÔøΩ dans l'objet
		 *
		 * @param mode : mode d'execution du fetch (par dÔøΩfaut rÔøΩcupÔøΩre le mode stockÔøΩ)
		 *
		 * @return array
		 */
		function Fetch($mode=''){
			try{
				if (!$mode)
					$mode = $this->v['mode'];
				if (!$this->v['sth'])
					throw new QDException('ExÔøΩcution de la fonction QDDB->Fetch() sans avoir prÔøΩ-ÔøΩxÔøΩcutÔøΩ de requÔøΩte', -1);
				return $this->v['sth']->fetch($mode);
			}
			catch(PDOException $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
			catch(Exception $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
		} // Fetch

		/**
		 * Retourne la valeur de la colonne choisie
		 *
		 * @param columnName : String contenant le nom de la colonne cherchÔøΩe
		 * @param tableName : String contenant le nom de la table concernÔøΩe
		 * @param sqlWhere : String contenant la chaine WHERE, paramÔøΩtrÔøΩe ou non
		 * @param arrWhere : Tableau contenant  les valeurs correspondantes (avec marqueur de positionnement "?" ou emplacement nommÔøΩs)
		 *
		 * @return string
		 */
		function agrLookUp($columnName, $tableName, $sqlWhere="1", $arrWhere = array()){
			try{
				$query='SELECT '.$columnName.' as result FROM '.$tableName.' WHERE '.$sqlWhere;
				if($this->v["debug"]==1) print "<br>".$query.'/'.$arrWhere;
				$this->v['sth']=NULL;
				//print '<pre>';
				//print_r($query);
				//print_r(debug_backtrace());
				$this->v['sth'] = $this->v['pdoDB']->prepare($query);
				if ($this->v['sth'] == null){
					throw new QDException("Erreur lors de le requÔøΩte QDFormDB :<br>".$query, -1, array());
				}
				if ($this->v['sth']->execute($arrWhere)){
					return $this->v['sth']->fetchColumn();
				}
				else
					return -1;
			}
			catch(PDOException $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
			catch(Exception $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
		} // agrLookUp

		/**
		 * Retourne la moyenne de la colonne choisie
		 *
		 * @param columnName : String contenant le nom de la colonne cherchÔøΩe
		 * @param tableName : String contenant le nom de la table concernÔøΩe
		 * @param sqlWhere : String contenant la chaine WHERE, paramÔøΩtrÔøΩe ou non
		 * @param arrWhere : Tableau contenant  les valeurs correspondantes (avec marqueur de positionnement '?' ou emplacement nommÔøΩs)
		 *
		 * @return string
		 */
		function agrAvg($columnName, $tableName, $sqlWhere='1', $arrWhere = array()){
			try{
				return $this->agrLookUp('AVG('.$columnName.')', $tableName, $sqlWhere, $arrWhere);
			}
			catch(PDOException $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
			catch(Exception $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
		} // agrAvg
		/**
		 * Retourne le minimum de la colonne choisie
		 *
		 * @param columnName : String contenant le nom de la colonne cherchÔøΩe
		 * @param tableName : String contenant le nom de la table concernÔøΩe
		 * @param sqlWhere : String contenant la chaine WHERE, paramÔøΩtrÔøΩe ou non
		 * @param arrWhere : Tableau contenant  les valeurs correspondantes (avec marqueur de positionnement '?' ou emplacement nommÔøΩs)
		 *
		 * @return string
		 */
		function agrMin($columnName, $tableName, $sqlWhere='1', $arrWhere = array()){
			try{
				return $this->agrLookUp('MIN('.$columnName.')', $tableName, $sqlWhere, $arrWhere);
			}
			catch(PDOException $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
			catch(Exception $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
		} // agrMin
		/**
		 * Retourne le maximum de la colonne choisie
		 *
		 * @param columnName : String contenant le nom de la colonne cherchÔøΩe
		 * @param tableName : String contenant le nom de la table concernÔøΩe
		 * @param sqlWhere : String contenant la chaine WHERE, paramÔøΩtrÔøΩe ou non
		 * @param arrWhere : Tableau contenant  les valeurs correspondantes (avec marqueur de positionnement '?' ou emplacement nommÔøΩs)
		 *
		 * @return string
		 */
		function agrMax($columnName, $tableName, $sqlWhere='1', $arrWhere = array()){
			try{
				return $this->agrLookUp('MAX('.$columnName.')', $tableName, $sqlWhere, $arrWhere);
			}
			catch(PDOException $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
			catch(Exception $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
		} // agrMax

		/**
		 * Retourne la somme des valeurs de la colonne choisie
		 *
		 * @param columnName : String contenant le nom de la colonne cherchÔøΩe
		 * @param tableName : String contenant le nom de la table concernÔøΩe
		 * @param sqlWhere : String contenant la chaine WHERE, paramÔøΩtrÔøΩe ou non
		 * @param arrWhere : Tableau contenant  les valeurs correspondantes (avec marqueur de positionnement '?' ou emplacement nommÔøΩs)
		 *
		 * @return string
		 */
		function agrSum($columnName, $tableName, $sqlWhere='1', $arrWhere = array()){
			try{
				return $this->agrLookUp('SUM('.$columnName.')', $tableName, $sqlWhere, $arrWhere);
			}
			catch(PDOException $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
			catch(Exception $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
		} // agrSum

		/**
		 * Retourne le nombre de ligne
		 *
		 * @param tableName : String contenant le nom de la table concernÔøΩe
		 * @param sqlWhere : String contenant la chaine WHERE, paramÔøΩtrÔøΩe ou non
		 * @param arrWhere : Tableau contenant  les valeurs correspondantes (avec marqueur de positionnement '?' ou emplacement nommÔøΩs)
		 *
		 * @return string
		 */
		function agrCount($tableName, $sqlWhere='1', $arrWhere = array()){
			try{
				return $this->agrLookUp('COUNT(*)', $tableName, $sqlWhere, $arrWhere);
			}
			catch(PDOException $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
			catch(Exception $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
		} // agrSum

		/**
		 * Commence une transaction BD
		 *
		 * @return boolean
		 */
		function beginTransaction(){
			try{
				return $this->v['pdoDB']->beginTransaction();
			}
			catch(PDOException $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
			catch(Exception $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
		} // beginTransaction

		/**
		 * Valide les instructions de la transaction, et termine la transaction
		 *
		 * @return boolean
		 */
		function commit(){
			try{
				return $this->v['pdoDB']->commit();
			}
			catch(PDOException $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
			catch(Exception $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
		} // commit

		/**
		 * Annule les instructions de la transaction, et termine la transaction
		 *
		 * @return boolean
		 */
		function rollBack (){
			try{
				return $this->v['pdoDB']->rollBack();
			}
			catch(PDOException $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
			catch(Exception $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
		} // rollBack

		function getPDOError(){
			$tt = $this->v['sth']->errorInfo();
			return $tt [2];
		}

		/**
		 * Renvoie un tableau associatif contentnant le dÔøΩtail de la table passÔøΩ en paramÔøΩtre
		 *
		 * @param tablename : nom de la table voulue
		 *
		 * @return array
		 */
		function DDLGetFields ($tablename){
			try{
					$dbEngine = $this->getDbEngine();
					if ($dbEngine == 'mysql')
					{
						$arrFields=$this->query2Array('DESCRIBE '.$tablename);

						foreach ($arrFields as $k=>&$Field)
						{
							// On remplace les tuypes secondaires par des types principaux plus lisibles
							$Field['Type']=str_replace(array('tinyint','mediumint','bool','bigint','int'), 'ENTIER',
									str_replace(array('tinytext','mediumtext','longtext', 'text'), 'TEXTE',
										str_replace(array('varchar','char'), 'CHAINE', str_replace(array('date','datetime','timestamp','time', 'year'), 'DATE/TEMPS',
										str_replace(array('enum','set'), 'ENUM',
										str_replace(array('float','decimal','double'), 'FLOAT',
										str_replace(array('tinyblob','mediumblob', 'longblob', 'varbinary', 'binary', 'blob'), 'BINAIRE', $Field['Type'])))))));
						}
						return $arrFields;
				}
				else
					return -1;
			}
			catch(PDOException $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
			catch(Exception $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
		} // DDLGetFields

		/**
		 * Renvoie un tableau associatif contentnant la liste des tables de la base sur laquelle on est connectÔøΩ
		 *
		 * @return array
		 */
		function DDLGetTables (){
			try{
				$dbEngine=$this->getDbEngine();
				if ($dbEngine == 'mysql')
				{
					$arrTablesTmp=$this->query2Array('SHOW TABLES');
					foreach ($arrTablesTmp as $k=>&$Table)
					{
							$arrTables[]=$Table['Tables_in_'.$this->v['dbprm']['dbname']];
					}
					return $arrTables;
				}
				else
					return -1;
			}
			catch(PDOException $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
			catch(Exception $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
		} // DDLGetTables
		/**
		 * Renvoie un tableau associatif contentnant la liste des tables de la base sur laquelle on est connectÔøΩ
		 *
		 * @return array
		 */
		function DDLGetFieldsMeta ($tableName){
			try{
				$arrResult=array();
				$sql  = ' select * from '.$tableName.' where false';
				$sth = $this->v['pdoDB']->prepare($sql);
				$sth->execute();
				for($i=0;$i<$sth->columnCount();$i++){
					$t= $sth->getColumnMeta($i);
					$arrResult[$t['name']]=$t;
				}
				return $arrResult;
			}
			catch(PDOException $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
			catch(Exception $e){
				throw new QDException('-1', $e->getMessage(), $e->getFile(), $e->getLine(), -1,  $e->getTrace());
			}
		} // DDLGetFieldsMeta
	}//end class
?>
