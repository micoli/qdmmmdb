<?php
namespace App\Commands\QDmmmDB\Series;

use Knp\Command\Command as KnpCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ExistingCommand extends KnpCommand{
	protected function configure() {
		$this
		->setName("series:existing")
		->setDescription("rename incoming series files")
		->addArgument('foo', InputArgument::REQUIRED, 'The directory')
		->addArgument('foo2', InputArgument::REQUIRED, 'The directory')
		->addArgument('foo3', InputArgument::OPTIONAL, 'The directory')
		->addOption('foo4','f', InputOption::VALUE_REQUIRED,'foo4');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$qd = new \QDSeriesBatch($this->getSilexApplication());
		$output->writeln(sprintf('  <comment>%s</comment>',"aaaa"));
	}
}