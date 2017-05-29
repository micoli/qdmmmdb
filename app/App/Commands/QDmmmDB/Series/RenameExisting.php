<?php
namespace App\Commands\QDmmmDB\Series;

use App\Components\QDmmmDB\Mediadb\Series\SeriesBatch;
use Knp\Command\Command as KnpCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use App\Components\Commands\ArgumentParser;

class RenameExisting extends KnpCommand{
	const ARG_DRY_RUN = 'dryRun';
	const ARG_DRY_REFRESH = 'refresh';

	protected function configure() {
		$this
		->setName("series:existing")
		->setDescription("rename already classified series files")
		->setDefinition(
			new InputDefinition(array(
				new InputOption(self::ARG_DRY_RUN, 'd', InputOption::VALUE_OPTIONAL,false),
				new InputOption(self::ARG_DRY_REFRESH, 'r', InputOption::VALUE_OPTIONAL,true),
			))
		);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$qd = new SeriesBatch($this->getSilexApplication());
		$qd->renameExisting(
			ArgumentParser::toBool($input->getOption(self::ARG_DRY_RUN)),
			ArgumentParser::toBool($input->getOption(self::ARG_DRY_REFRESH))
		);
		$output->writeln(sprintf('  <comment>%s</comment>',"done"));
	}
}