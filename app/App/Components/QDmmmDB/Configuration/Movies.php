<?php
namespace App\Components\QDmmmDB\Configuration;

class Movies
{

	public static $movieFolderWithYear = 1;

	public static $arrHiddenmovie = [
		[
			'rgx' => 'sample'
		]
	];

	public static $arrKeepSpecialTag = [
		[
			'rgx' => 'TS',
			'rep' => 'TS',
			'multiple' => 0
		],
		[
			'rgx' => 'screener',
			'rep' => 'TS',
			'multiple' => 0
		],
		[
			'rgx' => 'VO',
			'rep' => 'VO',
			'multiple' => 0
		],
		[
			'rgx' => 'VOST',
			'rep' => 'VO',
			'multiple' => 0
		],
		[
			'rgx' => 'VOSTFR',
			'rep' => 'VO',
			'multiple' => 0
		],
		[
			'rgx' => 'R5',
			'rep' => 'R5',
			'multiple' => 0
		],
		[
			'rgx' => '(pt|dvd|cd|part)([0-9])',
			'rep' => 'cd',
			'multiple' => 1
		]
	];

	public static $arrCleanupMoviesRegexStrict = [
		[
			'rgx' => 'DVD[0-9]'
		],
		[
			'rgx' => 'DVDRIP'
		],
		[
			'rgx' => 'DVD'
		],
		[
			'rgx' => 'Fwd'
		],
		[
			'rgx' => 'BDRip'
		],
		[
			'rgx' => 'Brrip'
		],
		[
			'rgx' => ' md '
		],
		[
			'rgx' => ' TS '
		],
		[
			'rgx' => ' FR '
		],
		[
			'rgx' => ' VO '
		],
		[
			'rgx' => ' R5'
		],
		[
			'rgx' => ' BY '
		],
		[
			'rgx' => ' 20[0-9][0-9] '
		],
		[
			'rgx' => ' CD[0-9] '
		],
		[
			'rgx' => ' VO '
		],
		[
			'rgx' => ' VOST '
		],
		[
			'rgx' => ' VOSTFR '
		],
		[
			'rgx' => ' screener '
		],
		[
			'rgx' => '\\?',
			'rep' => '-'
		],
		[
			'rgx' => '’',
			'rep' => '\''
		],
		[
			'rgx' => ':',
			'rep' => '-'
		]
	];

	public static $arrCleanupMoviesRegex = [
		[
			'rgx' => '\\.'
		],
		[
			'rgx' => '-'
		],
		[
			'rgx' => '_'
		],
		[
			'rgx' => ' [0-9]{4} .*'
		],
		[
			'rgx' => ' FRENCH '
		],
		[
			'rgx' => 'dvdrip',
			'rep' => 'DVD'
		],
		[
			'rgx' => 'dvdscr',
			'rep' => 'DVD'
		],
		[
			'rgx' => 'part 1',
			'rep' => 'part1'
		],
		[
			'rgx' => 'part 2',
			'rep' => 'part2'
		],
		[
			'rgx' => 'part 3',
			'rep' => 'part3'
		],
		[
			'rgx' => 'part 4',
			'rep' => 'part4'
		],
		[
			'rgx' => 'part1',
			'rep' => 'CD1'
		],
		[
			'rgx' => 'part2',
			'rep' => 'CD2'
		],
		[
			'rgx' => 'part3',
			'rep' => 'CD2'
		],
		[
			'rgx' => 'part4',
			'rep' => 'CD4'
		],
		[
			'rgx' => 'repack'
		],
		[
			'rgx' => 'STV'
		],
		[
			'rgx' => 'Hush'
		],
		[
			'rgx' => 'Utt'
		],
		[
			'rgx' => 'AC3'
		],
		[
			'rgx' => 'xvid'
		],
		[
			'rgx' => 'divx'
		],
		[
			'rgx' => 'dolby'
		],
		[
			'rgx' => 'STS'
		],
		[
			'rgx' => '1CD'
		],
		[
			'rgx' => 'TRUEFRENCH'
		],
		[
			'rgx' => 'HDRIP'
		],
		[
			'rgx' => 'WEBRIP'
		],
		[
			'rgx' => 'WEB'
		],
		[
			'rgx' => 'AAC'
		],
		[
			'rgx' => '2014'
		],
		[
			'rgx' => '2015'
		],
		[
			'rgx' => 'extended'
		],
		[
			'rgx' => 'x264'
		],
		[
			'rgx' => 'avitech'
		],
		[
			'rgx' => 'Kittoff'
		]
	];
}