<?php
namespace App\Commands;

use Knp\Command\Command as KnpCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApiCommand extends KnpCommand{

	protected function configure() {
		$this
		->setName("api")
		->setDescription("An api debug command!");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$app = $this->getSilexApplication();
		$routes = $app['api.doc']();
		foreach($routes as $route){
			$output->writeln(sprintf('<info>%s</info> <comment>%s</comment>',$route['path'],implode(',',$route['methods'])));
			foreach($route['actions'] as $action){
				$output->writeln(sprintf('  <comment>%s</comment> %s',implode(',',$action['methods']),$action['controller']));
			}
		}
	}
}