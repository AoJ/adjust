<?php

namespace app\extensions;

use Nette\Config\CompilerExtension;



class AdjustExtension extends CompilerExtension
{
	const PREFIX = 'adjust';
	
	public function loadConfiguration()
	{
		$serviceName = $this->prefix(self::PREFIX . 'Manager');
		
		$this->getContainerBuilder()->addDefinition($serviceName)
				->setClass('\app\services\AdjustManager', array(
					'@user',
					'@router',
					'@cacheStorage',
					'%' . self::PREFIX . '%',
					$serviceName
				))
				->addSetup('readAdjustData')
				->addSetup('createRoutes')
				->addTag('run');
	}
	
}