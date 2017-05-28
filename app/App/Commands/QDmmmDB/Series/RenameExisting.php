<?php
namespace App\Commands\QDmmmDB\Series;

use App\Components\QDmmmDB\Mediadb\Series\QDSeriesBatch;
use Knp\Command\Command as KnpCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RenameExisting extends KnpCommand{
	const ARG_DRY_RUN = 'dryRun';

	protected function configure() {
		$this
		->setName("series:renameExisting")
		->setDescription("rename incoming series files")
		/*->setDefinition(
			new InputDefinition(array(
				new InputOption(self::ARG_SERIE_PATHS, 's', InputOption::VALUE_REQUIRED),
				new InputOption(self::ARG_PATH, 'p', InputOption::VALUE_REQUIRED),
				new InputOption(self::ARG_DRY_RUN, 'd', InputOption::VALUE_OPTIONAL,false),
			))
		)*/;

	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$qd = new QDSeriesBatch($this->getSilexApplication());
		$qd->renameExisting();
		$output->writeln(sprintf('  <comment>%s</comment>',"aaaa"));
	}
}