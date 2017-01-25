<?php
class QDServiceLocatorPEAR implements QDLocator{
	protected $base = '.';
	public function __construct($directory='.')  {
	  $this->base = (string) $directory;
	}
	public function canLocate($class)  {
	  $path = $this->getPath($class);
	  if (file_exists($path)) return true;
	  else return false;
	}
	public function getPath($class)  {
	  return $this->base . '/' . str_replace('_', '/', $class) . '.php';
	}
}
QDServiceLocator::attachLocator(new QDServiceLocatorPEAR(), 'PEAR');
