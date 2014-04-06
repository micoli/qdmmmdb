<?php
/**
 * Classe d'exception QDException
 *
 * Classe ������tandant la classe Exception native.
 * Permet de r������cup������rer les erreurs et exceptions, et de les traiter (affichage personnalis������,
 * ������criture des fichiers de logs XML...)
 *
 * @author Jb
 */
class QDException extends Exception
{
	/**
		* Niveau de l'erreur
		*
		* @var integer
		*/
		protected $level = 0;

	/**
		* Code de l'erreur
		*
		* @var integer
		*/
		protected $code = 0;

	/**
		* Description de l'erreur
		*
		* @var string
		*/
		protected $message;

	/**
		* Fichier ou a eu lieu l'erreur
		*
		* @var string
		*/
		protected $file;

	/**
		* Ligne ou a eu lieu l'erreur
		*
		* @var integer
		*/
		protected $line;
		/**
		* Indique si c'est une erreur ou une exception
		*
		* @var boolean
		*/
		public $isErreur = -1;
		/**
		* Trace personnalis������e (au cas o������ l'on r������cup������re une trace faite par une autre exception pour l'afficher
		*
		* @var Array
		*/
		protected $QDTrace = "";

		/**
		 * Construit l'objet MyException
		 *
		 * @param string $message description de l'erreur
		 * @return void
		 */
		public function __construct()
		{
				switch (func_num_args())
				{
						case 6 : parent::__construct(func_get_arg(1));
										$this->level = (integer) func_get_arg(0);
										$this->file  = (string) func_get_arg(2);
										$this->line  = (integer) func_get_arg(3);
										$this->isErreur  = (integer) func_get_arg(4);
										$this->QDTrace  =  func_get_arg(5);
										break;
						case 5 : parent::__construct(func_get_arg(1));
										$this->level = (integer) func_get_arg(0);
										$this->file  = (string) func_get_arg(2);
										$this->line  = (integer) func_get_arg(3);
										$this->isErreur  = (integer) func_get_arg(4);
										break;
						case 4 : parent::__construct(func_get_arg(1));
										$this->level = (integer) func_get_arg(0);
										$this->file  = (string) func_get_arg(2);
										$this->line  = (integer) func_get_arg(3);
										break;
						case 3 : parent::__construct(func_get_arg(0));
										$this->code = (integer) func_get_arg(1);
										$this->QDTrace =  func_get_arg(2);
										break;
						case 2 : parent::__construct(func_get_arg(0));
										$this->code = (integer) func_get_arg(1);
										break;
						case 1 : parent::__construct(func_get_arg(0));
				}
		}

		/**
		 * Retourne le niveau de l'erreur
		 *
		 * @return integer
		 */
		function getLevel()
		{
				return $this->level;
		}

		/**
		 * Retourne la trace personnalis������e de l'exception
		 *
		 * @return integer
		 */
		function getQDTrace()
		{
			if (is_array($this->QDTrace))
				return $this->QDTrace;
			else
				return $this->getTrace();
		}

		/**
		 * Retourne le niveau de l'erreur sous forme de chaine
		 *
		 * @return string
		 */
		function getLevelAsString()
		{
				switch ($this->level)
				{
						case E_ERROR           : return "E_ERROR";
						case E_WARNING         : return "E_WARNING";
						case E_PARSE           : return "E_PARSE";
						case E_NOTICE          : return "E_NOTICE";
						case E_CORE_ERROR      : return "E_CORE_ERROR";
						case E_CORE_WARNING    : return "E_CORE_WARNING";
						case E_COMPILE_ERROR   : return "E_COMPILE_ERROR";
						case E_COMPILE_WARNING : return "E_COMPILE_WARNING";
						case E_USER_ERROR      : return "E_USER_ERROR";
						case E_USER_WARNING    : return "E_USER_WARNING";
						case E_USER_NOTICE     : return "E_USER_NOTICE";
						case E_ALL             : return "E_ALL";
						case E_STRICT          : return "E_STRICT";
						case -1                : return "EXCEPTION";
				}
		}
		/**
		 * Ecrit un fichier XML contenant une rapport d'erreurs
		 *
		 * @param directory : repertoire qui acceuillera les fichiers de logs
		 * @return nothing
		 */
		function writeLog($directory)
		{
			$datetime=date("Ymd-Hms");
			$fp = fopen($directory."logerror-".$datetime.".xml",'w');
			fwrite($fp,"<?xml version='1.0' standalone='yes'?><errors><error><datetime>".$datetime."</datetime>");

			if ($this->isErreur != -1){
				$bigstr="<level>".$this->getLevelAsString()."</level>".
								"<message>".$this->getMessage()."</message>".
								"<file>".$this->getFile()."</file>".
								"<line>".$this->getLine()."</line>";

				$arr = array_reverse($this->getTrace());
				array_pop($arr);
				$fils=0;
				foreach ($arr as $key=>$file)
				{
					$fils=1;
						if ($key == 0)
							$bigstr.="<errors>";

						$tmpfile=file($file['file']);
						$real_line=$tmpfile[$file['line']-1];
						$bigstr.="<error><file>".$file['file']."</file>".
															"<line>".$file['line']."</line>".
															"<function>".$file['function']."(".implode(' , ',$file['args']).")</function>".
															"<extract>".trim($real_line)."</extract>".
											"</error>";
				} // end foreach
				if ($fils==1)
					$bigstr.="</errors>";
				$bigstr.="</error>";
				fwrite($fp,$bigstr);
			} // end if
			else{
				// en cas d'exception
				$bigstr="<level>Exception</level>".
								"<message>".$this->getMessage()."</message>".
								"<file>".$this->getFile()."</file>".
								"<line>".$this->getLine()."</line>";

				$arrDbg=debug_backtrace();
				array_pop($arrDbg);
				$real_line='';
				$fils=0;

				foreach ($arrDbg as $key=>$Dbg)
				{
					$fils=1;
					if ($key == 0)
						$bigstr.="<errors>";

					$tmpfile=file($Dbg['file']);
					$real_line=$tmpfile[$Dbg['line']-1];

					$bigstr.="<error><file>".$Dbg['file']."</file>".
														"<line>".$Dbg['line']."</line>".
														"<function>".$Dbg['function']."()</function>".
														"<extract>".trim($real_line)."</extract>".
										"</error>";
				} // end foreach

				if ($fils==1)
					$bigstr.="</errors>";
				$bigstr.="</error>";
				fwrite($fp,$bigstr);
		} //end else

		fwrite($fp,"</errors>");
		fclose($fp);
		return 1;
	}// writeLog();

	/**
	 * Sort Of Implode for debug
	 *
	 * @return string
	 */
	function Array2String($Array) {
		$Return='';
		$NullValue="NULL";
		foreach ($Array as $Key => $Value) {
		if(is_array($Value)){
			$ReturnValue=' array=>['.$this->Array2String($Value).']';
		}else{
			if (is_scalar($Value)){
				$ReturnValue=(strlen($Value)>0)?$Value:$NullValue;
			}else{
				$ReturnValue=" Object ";
			}
		}
		$Return.=('['.$Key.']=>'.$ReturnValue).';';
		}
		return (substr($Return,0,-1)).";";
	}//Array2String

	/**
	 * Retourne un r������sum������ de l'erreur, avec un bouton pour cacher/voir le corps de l'erreur
	 *
	 * @return string
	 */
		public function getSummary()
		{
			print $this->getMessage();
			return "<pre>".print_r($this->getTrace(),true)."</pre>" ;
	//   if ($this->isErreur != -1 or is_array($this->getQDTrace())){
				// en cas d'erreur
				$arrTrace =  ($this->isErreur == -1 ? $this->getQDTrace() : array_reverse($this->getTrace()));

				array_pop($arrTrace);
				$strFunctions='<hr>';
				foreach (array_reverse($arrTrace) as $key=>$file){
					if (array_key_exists('file',$file)){
						$tmpfile=file($file['file']);
						$extract=$tmpfile[$file['line']-1];
						$strFunctions.="\n<ul style='margin-left:0;padding-left:0.75em;'><li><b>";
						$strFunctions.=$file['function'];
						if (array_key_exists('args',$file)){
							$fileArgsStr = $this->Array2String($file['args']);
						}else{
							$fileArgsStr = "";
						}
						print_r($fileArgsStr);
						$strFunctions.="\n\n\n<br>(<pre>".($fileArgsStr)."</pre>)</b> dans ";
						$strFunctions.=$file['file']." ������ la ligne ".$file['line']."<br>";
						$strFunctions.="\n<i>Extrait de la ligne</i> : ";
						$strFunctions.=highlight_string(trim($extract),true)."<hr>";
					}
				}
				foreach ($arrTrace as $key=>$file){
					$strFunctions.="</li></ul>";
				}
				$tmpfile=file($this->file);
				$extrait=$tmpfile[$this->line-1];
				$string  = "\n<big><font color='red'>".($this->isErreur?"Erreur PHP ".$this->getLevelAsString()." ":"Exception lev���������e ")."</font></big>";
				$string .= "\n(<a href=# onClick='if(document.getElementById(1).style.display== \"block\")document.getElementById(1).style.display=\"none\";else{document.getElementById(1).style.display=\"block\"}'>Voir</a>)<br />\r\n";
				$string .= "\n<div id=1 style='display:block'><strong>Attention</strong> : ".$this->getMessage().".<br />\r\n";
				$string.=$strFunctions;
				$string .= "\nDans le fichier <strong>".$this->getFile()."</strong> &agrave; la ligne <strong><font color=\"blue\">".highlight_string($this->getLine(),true)."</color></strong>.<br />\r\n";
				$string .= "\n<br /></div>\r\n";
				//return ($string);//
				return ''.($string).'';
		}//getSummary
}// QDException
/**
 * Active QDException a chaque erreur
 *
 * @return void
 */
function error_handler($level, $message, $file, $line)
{
	// En cas d'erreur que l'on souhaite r������cup������rer, on d������clenche une exception
db($message);//			throw new QDException($level, $message, $file, $line, 1);

}

/**
 * Active QDException a chaque exception
 *
 * @return void
 */
function exception_handler($exception)
{
		// test de la pr������sence de la m������thode getSummary, qui atteste de la pr������sence de la classe QDException
		if (method_exists($exception, 'getSummary')){
			//echo "<pre>";print_r(debug_backtrace());
			// rajouter le test sur la variable blobal
			if (/* test de la variable globale */  0  )
			$exception->writeLog('D:/_DATAS/xampp/htdocs/sandbox/');
			// Mettre l'empeclacement des logs en variable globale
			if (php_sapi_name()=='cli'){
				die($exception->getSummary());
			}else{
				die(strip_tags($exception->getSummary()));
			}

		}
		else
		{
			// En cas de passage ici, cela signifie que notre classe d'exception n'est pas charg������e ou qu'une exception
			// autre que QDException n'a pas ������t������ r������cuper������e
			// donc on lance une exception classique
			die($exception->getMessage());
		}
}
	// R������cup������rer la variable globale error_reporting, et la mettre en param������tre de la fonction

	set_exception_handler("exception_handler");
	// L'error handler n'est pris que pour les erreurs d������finis dans le error_reporting
	set_error_handler("error_handler", error_reporting(E_ERROR | E_WARNING | E_PARSE ));


?>