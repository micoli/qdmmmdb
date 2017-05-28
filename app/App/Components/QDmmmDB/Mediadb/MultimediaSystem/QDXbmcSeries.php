<?php
namespace App\Components\QDmmmDB\Mediadb\MultimediaSystem;

use App\Components\QDmmmDB\Mediadb\Series\QDSeriesProxy;

class QDXbmcSeries extends QDSeriesProxy{

	function getShows(){
		$this->initdb();
		$sql = "select
					sh.c12 as tvdbid,
					sh.c00 as title,
					sh.c05 as date,
					group_concat(sh.c16 order by sh.idShow) as paths,
					group_concat(sh.idShow order by sh.idShow) as idShows,
					group_concat(sh.c17 order by sh.idShow) as idPaths,
					count(distinct ep.c18) as nbEpisode
				from tvshow sh
				inner join episode ep on sh.idShow=ep.idShow
				group by tvdbid
				order by sh.c00;";
		$res=array();
		$resSql = self::$XbmcDBPDO->query($sql);
		foreach ($resSql as $v){
			$v['title']=utf8_encode($v['title']);
			$res[]=$v;
		}
		return $res;
	}

	function getShowStruct($tvdbid){
		$serieStruct=simpleXMLToArray(\simplexml_load_string($this->getXmlDocFromSerieId($tvdbid)));
		$nbSeasons=0;
		foreach($serieStruct['Episode'] as $ep){
			$nbSeasons=max($ep['SeasonNumber'],$nbSeasons);
		}
		$serieStruct['Series']=array_merge($serieStruct['Series'],array('nbSeasons'=>$nbSeasons));
		return $serieStruct;
	}

	function preGetShowSeasons($sTvdbID){
		$tmp=$this->getShowStruct($sTvdbID);
		return $tmp['Series'];
	}

	function getShowsSeasons($sTvdbID){
		$serieStruct=$this->getShowStruct($sTvdbID);
		$this->initdb();
		$sql = sprintf("
				select
					ep.c00 as episode_title,
					ep.c05 as episode_date,
					ep.c12 as episode_season,
					ep.c13 as episode_episode,
					ep.c18 as episode_fullfilename,
					fi.strFilename as episode_filename,
					pa.strPath as episode_path
				from tvshow sh
				inner join episode ep on sh.idShow=ep.idShow
				inner join files fi on fi.idFile=ep.idFile
				inner join path pa on pa.idPath=fi.idPath
				where sh.c12=%s
				group by ep.c18
				order by ep.c12,ep.c13,pa.strPath;",$sTvdbID);


		$seasonStruct=array();
		for($n=1;$n<=$serieStruct['Series']['nbSeasons'];$n++){
			$seasonStruct[sprintf('season_%02d',$n)]=array(
				'title'		=> '',
				'present'	=> false,
				'exists'	=> false,
				'files'		=> array()
			);
		}

		$res=array();
		$resSql = self::$XbmcDBPDO->query($sql);
		$maxSeason=0;
		$maxEpisode=0;
		foreach($serieStruct['Episode'] as $episode){
			if($episode['SeasonNumber']!=0){
				$maxSeason=max($maxSeason,$episode['SeasonNumber']);
				$maxEpisode=max($maxEpisode,$episode['EpisodeNumber']);
			}
		}
		for($j=1;$j<=$maxEpisode;$j++){
			$res[$j]=array_merge(array('episodeNumber'=>$j),$seasonStruct);
			for($i=1;$i<=$maxSeason;$i++){
				$seasonString=sprintf('season_%02d',$i);
				$res[$j][$seasonString]['exists']=false;
			}
		}

		foreach($serieStruct['Episode'] as $episode){
			if($episode['SeasonNumber']!=0){
				$seasonString=sprintf('season_%02d',$episode['SeasonNumber']);
				$res[$episode['EpisodeNumber']][$seasonString]['episode_formatted_number']=sprintf('%02dx%02d',$episode['SeasonNumber'],$episode['EpisodeNumber']);
				$res[$episode['EpisodeNumber']][$seasonString]['title'	]=$episode['EpisodeName'];
				$res[$episode['EpisodeNumber']][$seasonString]['exists'	]=true;
			}
		}

		while ($v=$resSql->fetch(PDO::FETCH_ASSOC)){
			$seasonString=sprintf('season_%02d',$v['episode_season']);
			$localpath=$this->getLocalpath($v['episode_path'],true);
			$v['key'	] = $localpath['key'];
			$v['lang'	] = $this->getLangFromPath($v['episode_path']);
			$res[$v['episode_episode']][$seasonString]['present']=true;
			$res[$v['episode_episode']][$seasonString]['files'][]=$v;
		}
		return array_values($res);
	}

	function getEpisodeList($sTvdbID,$sSeason){
		$serieStruct=$this->getShowStruct($sTvdbID);
		$this->initdb();
		$sql = sprintf("
			select
				ep.c00			as episode_title,
				ep.c05			as episode_date,
				ep.c12			as episode_season,
				ep.c13			as episode_episode,
				ep.c18			as episode_fullfilename,
				fi.strFilename	as episode_filename,
				pa.strPath		as episode_path
			from	tvshow			sh
			inner	join episode	ep on sh.idShow=ep.idShow
			inner	join files	fi on fi.idFile=ep.idFile
			inner	join path		pa on pa.idPath=fi.idPath
			where	sh.c12=%s and ep.c12='%s'
			order by ep.c12*1,ep.c13*1,pa.strPath;",
			$sTvdbID,
			$sSeason*1
		);

		$res=array();
		$resSql = self::$XbmcDBPDO->query($sql);

		while ($v=$resSql->fetch(PDO::FETCH_ASSOC)){
			$localpath=$this->getLocalpath($v['episode_fullfilename'],true);
			$v['key'	] = $localpath['key'];
			$v['lang'	] = $this->getLangFromPath($v['episode_path']);
			$v['episode_title']=utf8_encode($v['episode_title']);
			$res[]=$v;
		}
		return $res;
	}
}

/*
		select sh.idShow,pashow.strPath,fi.idFile,sh.c00,sh.c16,epi.c12,epi.c13,epi.c00,pa.strPath,fi.strFilename,
		group_concat(distinct concat(art_show.type,'|',art_show.url)),
		group_concat(distinct concat(art_epi.type,'|',art_epi.url)),
		sh.c01
		from tvshow sh
		inner join  tvshowlinkpath tvslp on tvslp.idShow=sh.idShow
		inner join  path pashow on pashow.idPath=tvslp.idPath
		inner join  episode epi on epi.idShow=sh.idShow
		inner join  files fi on fi.idFile=epi.idFile
		inner join  path pa on pa.idPath=fi.idPath
		left join   art art_show on art_show.media_type='tvshow' and art_show.media_id=sh.idShow
		left join   art art_epi on art_epi.media_type='episode' and art_epi.media_id=epi.idEpisode
		where sh.c12=76290
		group by fi.idFile
		order by epi.idEpisode,sh.c00,epi.c12,epi.c13*1;
 */
