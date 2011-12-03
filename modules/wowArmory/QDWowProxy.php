<?php
  class QDWowProxy{
  	var $QDNet;
    var $armoryAdress, $cache, $cacheminutes, $zone, $realm;
  	function __construct(){
      $this->QDNet = new QDNet();			
  	}
    function wowCharacterTalent($dom){
      $character = $dom->getElementsByTagName('character')->item(0);
      $st = $this->QDNet->getCacheURL('http://'.$this->armoryAdress.'/character-talents.xml?'.$character->getAttribute('charUrl'),'character-talents',$this->cacheminutes,false);
      $domwowhead = new DOMDocument();
      $domwowhead->loadXML($st);
      $pNode = $dom->importNode($domwowhead->getElementsByTagName('talentTab')->item(0),true);
      $character->appendChild($pNode);
    }
    function wowCharacterReputation($dom){
      $character = $dom->getElementsByTagName('character')->item(0);
      $st = $this->QDNet->getCacheURL('http://'.$this->armoryAdress.'/character-reputation.xml?'.$character->getAttribute('charUrl'),'character-reputation',$this->cacheminutes,false);
      $domwowhead = new DOMDocument();
      $domwowhead->loadXML($st);
      $pNode = $dom->importNode($domwowhead->getElementsByTagName('reputationTab')->item(0),true);
      $character->appendChild($pNode);
    }
  
    function wowItemDisplayId($dom){
      $characterInfo =$dom->getElementsByTagName('characterInfo')->item(0);
      $characterTab = $dom->getElementsByTagName('characterTab')->item(0);
      $characterInfo->removeChild($characterTab);
      $data3D = $dom->createElement('datas3D','');      
      $characterInfo->appendChild($data3D);
      $page = $dom->documentElement;
  
      $itemList = $_REQUEST['itemList'];
      $domwowhead = new DOMDocument();
      $arrItems = explode('|',$itemList);
      foreach ($arrItems as $item){
        list($slot,$itemID)=split('\,',$item);
        $st = $this->QDNet->getCacheURL("http://fr.wowhead.com/?item=".$itemID."&xml",'item',-1,true);
        $domwowhead->loadXML($st);
        $jsonEquip = $domwowhead->getElementsByTagName('jsonEquip')->item(0);
        if ($jsonEquip){
          $pNode = $dom->importNode($jsonEquip,true);
          $data3D->appendChild($pNode);
        }
      }
        
      
      /*$st = $this->QDNet->getCacheURL("http://fr.wowhead.com/?item=".$element->getAttribute('id')."&xml",60,$cache);
      $domwowhead = new DOMDocument();
      $domwowhead->loadXML($st);*/
    }
    
    
    function wowSearchItemDetail($dom){
      $results = $dom->getElementsByTagName('armorySearch')->item(0);
      $st = $this->QDNet->getCacheURL('http://'.$this->armoryAdress.'/item-tooltip.xml?i='.$_REQUEST['i'],'item-tooltip',-1,true);
      $domwowhead = new DOMDocument();
      $domwowhead->loadXML($st);
      $subSite = $dom->createElement('searchItemDatas','');      
      $results->appendChild($subSite);
      foreach($domwowhead->getElementsByTagName('itemTooltip')->item(0)->childNodes as $el){
        $pNode = $dom->importNode($el,true);
        $subSite->appendChild($pNode);                          
      }
    }
  
    function wowItemDetailSearch($dom){
      return $this->wowItemDetail($dom,"/page/armorySearch/searchResults/items/item");
    }
    
    function wowItemDetail($dom,$filterItem="//item"){
      $xpath = new DOMXpath($dom);
      $elements = $xpath->query($filterItem);
      if (!is_null($elements)) {
        $domwowhead = new DOMDocument();
        foreach ($elements as $element) {
          $st = $this->QDNet->getCacheURL('http://'.$this->armoryAdress.'/item-info.xml?i='.$element->getAttribute('id'),'item-info',-1,true);
          if (strlen($st)==0) $st = $this->QDNet->getCacheURL('http://'.$this->armoryAdress.'/item-info.xml?i='.$element->getAttribute('id'),'item-info',-1,true);
          $domwowhead->loadXML($st);
          
          $element->setAttribute('onList',1);
  
          //infos
          $subSite = $dom->createElement('info','');      
          $element->appendChild($subSite);
          $item = $domwowhead->getElementsByTagName('item')->item(0);
          foreach($item->childNodes as $el){
            $pNode = $dom->importNode($el,true);
            $subSite->appendChild($pNode);                          
          }
          $element->setAttribute('itemLevel',$item->getAttribute('level'));
           
          //datas 
          $st = $this->QDNet->getCacheURL('http://'.$this->armoryAdress.'/item-tooltip.xml?i='.$element->getAttribute('id'),'item-tooltip',-1,true);
          $domwowhead->loadXML($st);
          $subSite = $dom->createElement('datas','');      
          $element->appendChild($subSite);
          foreach($domwowhead->getElementsByTagName('itemTooltip')->item(0)->childNodes as $el){
            $pNode = $dom->importNode($el,true);
            $subSite->appendChild($pNode);                          
          }
          
          //gems & enchants
          $improvment = $dom->createElement('improvments',''); 
          $element->appendChild($improvment);
          $arrImp = array($element->getAttribute('gem0Id'),$element->getAttribute('gem1Id'),$element->getAttribute('gem2Id'));//,$element->getAttribute('permanentenchant'));
          foreach ($arrImp as $kk=>$vv){
            if ($vv!=0){
              $st = $this->QDNet->getCacheURL('http://'.$this->armoryAdress.'/item-tooltip.xml?i='.$vv,'item-tooltip',-1,true);
              $domwowhead->loadXML($st);
              //if ($kk==3) print($vv."#".$st);
              $el = $domwowhead->getElementsByTagName('itemTooltip')->item(0);
              $pNode = $dom->importNode($el,true);
              $improvment->appendChild($pNode);
              $pNode->setAttribute('improvmentRank',$kk);
              //$improvmentRank = $dom->createElement('improvmentRank',$kk);      
              //$pNode->appendChild($improvmentRank);
            }
          }     
        }
      }
    }
    
    function displayWowXmlXsl($xml,$arrPlugin,$xsl='',$XslOnClient = false){
      $md5 = md5(implode(',',$arrPlugin).$xml);
      $xmlCacheFileName= "./cache/xml/".$md5.".xmlz";
      if (false && file_exists($xmlCacheFileName)){
        print  gzuncompress(file_get_contents($xmlCacheFileName));
        die();
      }   
      $sdom = simplexml_load_string ($xml);
      $doc = dom_import_simplexml($sdom);
      $dom = new DOMDocument('1.0','UTF-8');
      $xmlstylesheet = new DOMProcessingInstruction( 'xml-stylesheet','href="'.$xsl.'" type="text/xsl"');
      if ($XslOnClient) {
        $dom->appendChild($xmlstylesheet);
      }
      $dom_sxe = $dom->importNode(dom_import_simplexml($sdom), true);
      $dom_sxe = $dom->appendChild($dom_sxe);
      foreach($arrPlugin as $plugFunction){
      	$this->$plugFunction(&$dom);
      }
   
      if ($XslOnClient) {
        $proc = new XSLTProcessor;
        $xsldoc = new DOMDocument;
        $xsldoc->load($xsl);
        $proc->importStyleSheet($xsldoc); // attachement des règles xsl
        //die($dom->saveXML());
        echo $proc->transformToXML($dom);
      }
      $xmlStr = $dom->saveXML();
      file_put_contents($xmlCacheFileName,gzcompress($xmlStr));
      print  $xmlStr;       
  }
	function run_common(){
    Header("content-type: application/xml;charset=utf-8;");
    $this->armoryAdress = 'eu.wowarmory.com';
    $this->cacheminutes = 123*59+59;
    $this->cache      = (isset($_REQUEST['t'])?$_REQUEST['t']:true);
    $this->zone       = (isset($_REQUEST['z'])?$_REQUEST['z']:'');
    $this->realm      = (isset($_REQUEST['r'])?$_REQUEST['r']:'');		
    $this->character  = (isset($_REQUEST['c'])?$_REQUEST['c']:'');
    $this->item       = (isset($_REQUEST['i'])?$_REQUEST['i']:'');
    $this->guild      = (isset($_REQUEST['g'])?$_REQUEST['g']:'');
	}
  function svc_dungeonTree(){
  	$this->run_common();
    $st = $this->QDNet->getCacheURL('http://'.$this->armoryAdress.'/data/dungeonStrings.xml','dungeons',$this->cacheminutes,false);
    $this->displayWowXmlXsl($st,array()); 
  } 
  function svc_searchItem(){
    $this->run_common();
    $st = $this->QDNet->getCacheURL('http://'.$this->armoryAdress.'/search.xml?searchType=items&pr='.$this->realm.'&pn='.$this->character.'&pi='.$this->item,'search',$this->cacheminutes,$this->cache);
    $this->displayWowXmlXsl($st,array('wowItemDetailSearch','wowSearchItemDetail')); 
  } 
  function svc_infoCharacter(){
    $this->run_common();
    $st = $this->QDNet->getCacheURL('http://'.$this->armoryAdress.'/character-sheet.xml?r='.$this->realm.'&n='.$this->character.'&p=1','character-sheet',$this->cacheminutes,$this->cache);
    $this->displayWowXmlXsl($st,array('wowCharacterReputation','wowCharacterTalent','wowItemDetail'));
  } 
  function svc_infoCharacterModelViewer(){
    $this->run_common();
    $st = $this->QDNet->getCacheURL('http://'.$this->armoryAdress.'/character-sheet.xml?r='.$this->realm.'&n='.$this->character.'&p=1','character-sheet',$this->cacheminutes,$this->cache);
    $this->displayWowXmlXsl($st,array('wowItemDisplayId'));
  } 
  function svc_infoGuilde(){
    $this->run_common();
    $st = $this->QDNet->getCacheURL('http://'.$this->armoryAdress.'/guild-info.xml?r='.$this->realm.'&n='.$this->guild.'&p=1','guild-info',$this->cacheminutes,$this->cache);
    $this->displayWowXmlXsl($st,array(),'xsl/guild.xsl'); 
  } 
}
?>