<?php
namespace App\Components\QDmmmDB\Mediadb\Series;

use App\Components\QDmmmDB\Misc\ToolsFiles;

class SerieFile
{

	public $filename;

	public $dirname;

	public $extension;

	public $saison;

	public $episode;

	public $serie;

	public $rgx;

	public $rgxnum;

	public $rgx_match;

	public $root_file;

	public $clean_root_file;

	public $found = false;

	public function __construct($sFullFilename)
	{
		$aFileStruct = ToolsFiles::pathinfo_utf($sFullFilename);

		$this->filename = $aFileStruct['basename'];
		$this->filenameNoExtension = $aFileStruct['filename'];
		$this->extension = $aFileStruct['extension'];
		$this->dirname = $aFileStruct['dirname'];

		if (! $this->isMediaFile()) {
			return;
		}

		foreach (\App\Components\QDmmmDB\Configuration\Series::$arrRegex as $k => $rgx) {
			if (preg_match('`' . $rgx['rgx'] . '`i', $this->filename, $match)) {
				if ($rgx['tyear'] && preg_match('!(19|20)[0-9]{2}!', $this->filename)) {
					continue;
				}
				$this->saison = $match[$rgx['s']] * 1;
				$this->episode = $match[$rgx['e']] * 1;
				$this->serie = trim($match[$rgx['n']]," \t\n\r\0\x0B-");
				$this->rgx = $rgx['rgx'];
				$this->rgxnum = $k;
				$this->rgx_match = $match;
				$this->found = true;
				$pos = strpos($this->filename, $this->rgx_match[0]);
				if ($pos !== false) {
					$this->root_file = substr($this->filename, 0, $pos);
					$this->clean_root_file = preg_replace('! !', ' ', ucfirst(strtolower(str_replace(array(
						'.',
						'_'
					), array(
						' ',
						' '
					), ($this->root_file)))));
				}
				break;
			}
		}
	}

	public function __toArray()
	{
		return [
			'found' => $this->found,
			'filename' => $this->filename,
			'filenameNoExtension' => $this->filenameNoExtension,
			'extension' => $this->extension,
			'saison' => $this->saison,
			'episode' => $this->episode,
			'rgx' => $this->rgx,
			'clean_root_file' => $this->clean_root_file,
			'root_file' => $this->root_file
		]
		// 'fullfilename' => $this->full,
		// 'inFolder' => $this->inFolder,
		// 'folder' => $this->folder,
		// 'root' => $this->root,
		// 'subPath' => $this->subpath,
		// 'renamed' => $this->renamed,
		;
	}

	public function isMediaFile()
	{
		return in_array(strtolower($this->extension), \App\Components\QDmmmDB\Configuration\Media::$allowedExt);
	}

	public function isSubtitleFile()
	{
		return in_array(strtolower($this->extension), \App\Components\QDmmmDB\Configuration\Media::$subtitlesExt);
	}
}