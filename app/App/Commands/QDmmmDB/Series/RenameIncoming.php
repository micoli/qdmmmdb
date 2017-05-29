<?php
namespace App\Commands\QDmmmDB\Series;

use App\Components\QDmmmDB\Mediadb\Series\SeriesBatch;
use Knp\Command\Command as KnpCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Components\Commands\ArgumentParser;

class RenameIncoming extends KnpCommand{
	const ARG_SERIE_PATHS = 'seriePaths';
	const ARG_PATH = 'path';
	const ARG_DRY_RUN = 'dryRun';

	protected function configure() {
		$this
		->setName("series:incoming")
		->setDescription("rename incoming series files")
		->setDefinition(
			new InputDefinition(
				array(
					new InputOption(self::ARG_SERIE_PATHS, 's', InputOption::VALUE_REQUIRED),
					new InputOption(self::ARG_PATH, 'p', InputOption::VALUE_REQUIRED),
					new InputOption(self::ARG_DRY_RUN, 'd', InputOption::VALUE_OPTIONAL,'dry run',false),
				)
			)
		);
	}


	protected function execute(InputInterface $input , OutputInterface $output){
		$qd = new SeriesBatch($this->getSilexApplication());
		$result = $qd->renameIncoming(
			$input->getOption(self::ARG_SERIE_PATHS),
			$input->getOption(self::ARG_PATH),
			ArgumentParser::toBool($input->getOption(self::ARG_DRY_RUN))
		);
		db($result);
		//$output->writeln(sprintf('  <comment>%s</comment>',"aaaa"));
	}
}