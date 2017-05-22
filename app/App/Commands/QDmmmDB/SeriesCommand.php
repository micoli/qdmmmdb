<?php
namespace App\Commands\QDmmmDB;

use Knp\Command\Command as KnpCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SeriesCommand extends KnpCommand{

	protected function configure() {
		$this
		->setName("series")
		->setDescription("An series command line!");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$app = $this->getSilexApplication();
		$output->writeln(sprintf('  <comment>%s</comment>',"aaaa"));
	}
}