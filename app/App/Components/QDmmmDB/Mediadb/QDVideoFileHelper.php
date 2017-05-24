<?php
namespace App\Components\QDmmmDB\Mediadb;

use App\Components\QDmmmDB\Misc\Tools;

class QDVideoFileHelper{
	var $bin_ffmpeg;
	var $bin_mplayer;
	var $cacheFodler;
	var $debug=false;

	function __construct() {
		$this->bin_ffmpeg='ffmpeg';
		$this->bin_mplayer='mplayer';
		$this->cacheFolder='/var/www/cache/videothumb/';
		@mkdir($this->cacheFolder);
	}

	function sec2hms ($sec, $padHours = false){
		//http://www.laughing-buddha.net/php/lib/sec2hms/
		// start with a blank string
		$hms = "";
		// do the hours first: there are 3600 seconds in an hour, so if we divide
		// the total number of seconds by 3600 and throw away the remainder, we're
		// left with the number of hours in those seconds
		$hours = intval(intval($sec) / 3600);
		// add hours to $hms (with a leading 0 if asked for)
		$hms .= ($padHours)
			? str_pad($hours, 2, "0", STR_PAD_LEFT). ":"
			: $hours. ":";
		// dividing the total seconds by 60 will give us the number of minutes
		// in total, but we're interested in *minutes past the hour* and to get
		// this, we have to divide by 60 again and then use the remainder
		$minutes = intval(($sec / 60) % 60);
		// add minutes to $hms (with a leading 0 if needed)
		$hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ":";
		// seconds past the minute are found by dividing the total number of seconds
		// by 60 and using the remainder
		$seconds = intval($sec % 60);
		// add seconds to $hms (with a leading 0 if needed)
		$hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);

		// done!
		return $hms;
	}

	function binaryRun($cmd){
		$output= `$cmd 2>&1`;
		if ($this->debug){
			db("\n\n---------------------\n\n$cmd\n\n$output");
		}
		return $output;
	}

	function getVideoFileInfo($videoFile){
		$cmd = sprintf('%s -frames 1 -vo null -ao null "%s" ',$this->bin_mplayer,$videoFile);
		$output = $this->binaryRun($cmd);
		if (preg_match('/Invalid and inefficient vfw-avi/s', $output)){
			return array(
				'corrupted'=>true
			);
		}
		$cmd = sprintf('%s -i "%s" ',$this->bin_ffmpeg,$videoFile);
		$output = $this->binaryRun($cmd);
		$total = 0;
		if ($this->debug)db($output);
		if (preg_match('/Duration: ((\d+):(\d+):(\d+))/s', $output, $time)) {
			$htotal = sprintf('%02d:%02d:%02d',$time[2],$time[3],$time[4]);
			$total = ($time[2] * 3600) + ($time[3] * 60) + $time[4];
		}
		return array(
			'corrupted'	=>false,
			'duration'	=>$total,
			'hduration'	=>$htotal
		);

	}

	function svc_makeVideoPreview(){
		return $this->makeVideoPreview($_REQUEST);
	}

	function makeVideoPreview($o){
		$videoFile = $o['videoFile'];
		$this->debug = Tools::array_key_exists_assign_default('debug', $o, false);
		$fileMD5 = md5($videoFile);
		$outputFolder=$this->cacheFolder.$fileMD5."/";
		$fileInfo = $this->getVideoFileInfo($videoFile);
		if ($fileInfo['corrupted']){
			if ($this->debug) print "$videoFile corrupted <br/>";
			return $fileInfo;
		}else{
			if ($this->debug)  "$videoFile OK<br/>";
		}
		$start		= Tools::array_key_exists_assign_default("start"	, $o, 180);
		$duration	= Tools::array_key_exists_assign_default("duration", $o,  30);
		$outputFilename = sprintf('%ssample.avi',$outputFolder);
		if(file_exists($outputFilename)){
			unlink($outputFilename);
		}
		$cmd = sprintf('%s -sameq -ss %d -t %d -i "%s" "%s"',
			$this->bin_ffmpeg,
			$start,
			$duration,
			$videoFile,
			$outputFilename,
			$outputFilename
		);
		//db($cmd);
		$this->binaryRun($cmd);
		$fileInfo['preview'		]='sample.avi';
		$fileInfo['folderMD5'	]=$fileMD5;
		return $fileInfo;

	}

	function svc_thumbnails(){
		return $this->makeVideoThumbnails($_REQUEST);
	}

	function makeVideoThumbnails($o){
		//http://blog.amnuts.com/2007/06/22/create-a-random-thumbnail-of-a-video-file/
		$videoFile = $o['videoFile'];
		$this->debug = Tools::array_key_exists_assign_default('debug', $o, false);
		$fileMD5 = md5($videoFile);
		$outputFolder=$this->cacheFolder.$fileMD5."/";
		@mkdir($outputFolder);
		chmod($outputFolder,0777);
		$nbFrames = Tools::array_key_exists_assign_default('nbFrames', $o,10);

		//print $outputFolder."<br/>";

		$fileInfo = $this->getVideoFileInfo($videoFile);
		if ($fileInfo['corrupted']){
			if ($this->debug) print "$videoFile corrupted <br/>";
			return $fileInfo;
		}else{
			if ($this->debug)  "$videoFile OK<br/>";
		}
		// get the duration and a random place within that
		//print_r($fileInfo);
		$arrFiles = glob($outputFolder.'*.jpg');
		$step = (int)($fileInfo['duration']/$nbFrames);
		if(Tools::array_key_exists_assign_default('forceThumb', $o, false)||count($arrFiles)==0){
			//may be a rm on each file here
			$cmd = sprintf('%s -nosound -forceidx -idx  -ss 00:00:01.001 -vo jpeg:outdir="%s" -sstep %d  -benchmark -ni -nobps -noextbased -quiet -noidle -frames %d "%s" ',
				$this->bin_mplayer,
				$outputFolder,
				$step,
				$nbFrames,
				$videoFile
			);
			//db($cmd);
			$return = $this->binaryRun($cmd);
			$arrFiles = glob($outputFolder.'*.jpg');
		}
		foreach($arrFiles as $k=>&$f){
			$f = array(
				'fullname'	=> $f,
				'url'		=> $fileMD5.'/'.basename($f),
				'img'		=> basename($f),
				'ts'		=> $this->sec2hms($k*$step),
				'tss'		=> $k*$step
			);

			//print sprintf('<img src="/cache/videothumb/%s/%s" height="40"/><br/>',$fileMD5,$f['img']);
		}
		$fileInfo['thumbs'		]=$arrFiles;
		$fileInfo['folderMD5'	]=$fileMD5;
		return $fileInfo;
	}
}