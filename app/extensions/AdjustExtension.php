<?php

namespace app\extensions;

use Nette\Config\CompilerExtension;



class AdjustExtension extends CompilerExtension
{
	const PREFIX = 'adjust';
	
	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		
		$container->addDefinition($this->prefix('adjustManager'))
				->setClass('\app\services\AdjustManager', array(
					'@user',
					'@router',
					'@cacheStorage',
					'%' . self::PREFIX . '%',
					$this->prefix('adjustManager')
				))
				->addSetup('readAdjustData')
				->addSetup('createRoutes')
				->addTag('run');
	}
	
}