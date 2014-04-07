<?php
class QDMisc{
	static function array2htmltable($array){
		$toOutput = '<table cellspacing=0 cellpadding=0 border=2>';
		$showHeader = true;
		foreach($array as $row){
			$toOutput .= '<tr>';
			if($showHeader){
				$keys = array_keys($row);
				for($i=0;$i<count($keys);$i++){
					$toOutput .= '<td>' . $keys[$i] . '</td>';
				}
				$toOutput .= '</tr><tr>';
				$showHeader = false;
			}
			$values = array_values($row);
			for($i=0;$i<count($values);$i++){
				$toOutput .= '<td>' . $values[$i] . '</td>';
			}
			$toOutput .= '</tr>';
		}
		$toOutput .= '</table>';
		return $toOutput;
	}
}
?>