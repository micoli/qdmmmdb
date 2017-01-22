<?php
class svcBatchIpbx{
	public function pub_redirectorEnqueuer($o){
		amiRedirectorEnqueuer::init(
			'localhost',
			'test',
			'1234',
			'php /var/www/qdmmmdb/cli.php exw_action=local.batchIpbx.redirectorDequeuer',
			array('momCnx'=>'tcp://momCnx')
		);
		amiRedirectorEnqueuer::runEvents();
	}

	public function pub_redirectorDequeuer($o){
		amiRedirectorDequeuer::init($o['momCnx']);
		amiRedirectorDequeuer::runEvents();
	}
}