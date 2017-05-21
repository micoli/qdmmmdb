<?php
namespace App\Components\QDmmmDB\Misc;

class ToolsFiles {
	//http://php.net/manual/en/function.pathinfo.php
	//@author jjoss at mail dot ru 04-Feb-2009 06:15
	function pathinfo_utf($path) {
		if		(strpos($path, '/') !== false){
			$basename = end(explode('/', $path));
		}elseif (strpos($path, '\\') !== false){
			$basename = end(explode('\\', $path));
		}else{
			return false;
		}
		if (empty($basename)) return false;

		$dirname = substr($path, 0, strlen($path) - strlen($basename) - 1);

		if (strpos($basename, '.') !== false){// && is_file($path)){
			$extension		= end(explode('.', $path));
			$filename		= substr($basename, 0, strlen($basename) - strlen($extension) - 1);
		}else{
			$extension		= '';
			$filename		= $basename;
		}

		return array(
			'fullPath'	=> dirname($path),
			'mtime'		=> file_exists($path)?date('Y-m-d H:i:s',filemtime($path)):0,
			'file'		=> $path,
			'dirname'	=> $dirname,
			'basename'	=> $basename,
			'extension'	=> $extension,
			'filename'	=> $filename
		);
	}
}