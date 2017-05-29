<?php
namespace App\Commands\QDmmmDB\Kodi;

use Knp\Command\Command as KnpCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use App\Components\QDmmmDB\Mediadb\MultimediaSystem\KodiActions;

class SyncVideo extends KnpCommand{
	const ARG_SERVER_ADRESS = 'serverAdress';
	const ARG_PORT = 'port';
	const ARG_USERNAME = 'username';
	const ARG_PASSWORD = 'password';
	const ARG_SOURCE = 'source';

	protected function configure() {
		$this
		->setName("kodi:syncVideo")
		->setDescription("SyncVideo")
		->setDefinition(
			new InputDefinition(array(
				new InputOption(self::ARG_SERVER_ADRESS, 'a', InputOption::VALUE_REQUIRED),
				new InputOption(self::ARG_PORT, 'p', InputOption::VALUE_OPTIONAL,'port','8080'),
				new InputOption(self::ARG_USERNAME, 'u', InputOption::VALUE_OPTIONAL,'username','kodi'),
				new InputOption(self::ARG_PASSWORD, 'w', InputOption::VALUE_OPTIONAL,'password','kodi'),
				new InputOption(self::ARG_SOURCE, 's', InputOption::VALUE_REQUIRED,'sources/Paths, multiple sources can be separated by ";"'),
			))
		);

	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$oKodiRpc = new KodiActions(
			$input->getOption(self::ARG_SERVER_ADRESS),
			$input->getOption(self::ARG_PORT),
			$input->getOption(self::ARG_USERNAME),
			$input->getOption(self::ARG_PASSWORD)
		);

		foreach(explode(';',$input->getOption(self::ARG_SOURCE)) as $sSource){
			$output->writeln(sprintf(
				'<info>%s</info> : <comment>%s</comment>',
				$sSource,
				$oKodiRpc->syncVideo($sSource)
			));
		}
	}
}