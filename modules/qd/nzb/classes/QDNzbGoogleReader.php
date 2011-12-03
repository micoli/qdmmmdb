<?
include "greader.class.php";
class QDNzbRssReader {
	function __construct() {
		$this->rssreader = new JMDReader($GLOBALS['conf']['qdnzbrss']['greader']['username'],$GLOBALS['conf']['qdnzbrss']['greader']['password']);
	}
	function svc_readRSS(){
		print_r(json_decode($read->listAll("starred","r=n")));	
	}
}
?>
