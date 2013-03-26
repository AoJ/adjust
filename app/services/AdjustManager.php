<?php

namespace app\services;

use Nette,
	Nette\Reflection,
	Nette\Application\IRouter,
	Nette\Application\Routers\Route,
	Nette\InvalidArgumentException,
	Nette\Security\User,
	Nette\Caching,
	ReflectionMethod;



class AdjustManager extends Nette\Object
{
	/** @var Nette\Application\IRouter */
	public $router;
	
	/** @var Nette\Security\User */
	public $user;
	
	/** @var Nette\Caching\IStorage */
	public $storage;
	
	/** @var array */
	public $config;
	
	/** @var string */
	public $name;
	
	/** @var array */
	public $adjustData;

	
	
	/**
	 * @param User $user
	 * @param IRouter $router
	 * @param Caching\IStorage $storage
	 * @param mixed $config
	 * @param string $name 
	 */
	public function __construct(User $user, IRouter $router, Caching\IStorage $storage, array $config, $name)
	{
		$this->user = $user;
		$this->router = $router;
		$this->storage = $storage;
		$this->config = $config;
		$this->name = $name;
		$this->adjustData = array();
	}
	
	
	
	public function createRoutes()
	{
		$this->router[] = new Route('adjust/<page>[/<method>]', 'Adjust:page');
	}
	
	
	
	/**
	 * @param string $page
	 * @return array|NULL
	 */
	public function getPage($page)
	{
		return isset($this->adjustData[$page]) ? $this->adjustData[$page] : NULL;
	}
	
	
	
	public function readAdjustData()
	{
		$cache = new Caching\Cache($this->storage, $this->name);
		if (NULL === ($this->adjustData = $cache->load($this->config))) {
			$adjustData = $this->parseConfig($this->config);
			$this->adjustData = $cache->save($this->config, $adjustData, array(
				Caching\Cache::FILES => $this->grabFileList($adjustData)
			));
		}
	}
	
	
	
	/**
	 * @param array $config
	 * @return array 
	 */
	protected function grabFileList(array $config)
	{
		$files = array();
		foreach ($config as $page => $pageData) {
			$files[] = $pageData['file'];
		}
		return $files;
	}
	
	
	
	/**
	 * @param array $config
	 * @return array
	 * @throws InvalidArgumentException 
	 */
	protected function parseConfig(array $config)
	{
		$adjustData = array();
		foreach ($config as $page => $class) {
			$cRef = new Reflection\ClassType($class);
			if (!$cRef->hasAnnotation('adjust')) {
				continue;
			}
			if (!$cRef->implementsInterface('Nette\Application\UI\IRenderable')) {
				throw new InvalidArgumentException("Component '$class' must be instance of IRenderable.");
			}

			$pageResources = array_filter(explode(' ', $cRef->getAnnotation('resource')));
			$pagePrivileges = array_filter(explode(' ', $cRef->getAnnotation('privilege')));

			$pageMethods = array('render' => array(), 'handle' => array());
			foreach ($cRef->getMethods(ReflectionMethod::IS_PUBLIC & ~ReflectionMethod::IS_ABSTRACT & ~ReflectionMethod::IS_STATIC) as $mRef) {
				$methodName = $mRef->getName();
				$methodType = substr($methodName, 0, 6);
				if ($methodType !== 'render' && $methodType !== 'handle') {
					continue;
				}
				if (!$mRef->hasAnnotation('adjust')) {
					continue;
				}
				
				$method = strtolower(substr($methodName, 6));
				$pageMethods[$methodType][$method] = array(
					'label' => $mRef->getAnnotation('label') ?: ($method ?: 'default'),
					'resources' => array_unique(array_merge(array_filter(explode(' ', $mRef->getAnnotation('resource'))), $pageResources)),
					'privileges' => array_unique(array_merge(array_filter(explode(' ', $mRef->getAnnotation('privilege'))), $pagePrivileges))
				);
			}

			$adjustData[$page] = array(
				'class' => $class,
				'file' => $cRef->getFileName(),
				'label' => $cRef->getAnnotation('label') ?: $page,
				'methods' => $pageMethods
			);
		}
		return $adjustData;
	}
	
	
	
	/**
	 * @param string $page
	 * @param string $method
	 * @return boolean 
	 */
	public function isViewAllowed($page, $method)
	{
		return $this->isAllowed($page, 'render', $method);
	}
	
	
	
	/**
	 * @param string $page
	 * @param string $signal
	 * @return boolean 
	 */
	public function isSignalAllowed($page, $signal)
	{
		return $this->isAllowed($page, 'handle', $signal);
	}
	
	
	
	/**
	 * @param string $page
	 * @param string $type
	 * @param string $action
	 * @return boolean 
	 */
	protected function isAllowed($page, $type, $action)
	{
		if (isset($this->adjustData[$page]['methods'][$type][$action])) {
			foreach ($this->adjustData[$page]['methods'][$type][$action]['resources'] as $resource) {
				foreach ($this->adjustData[$page]['methods'][$type][$action]['privileges'] as $privilege) {
					if ($this->user->isAllowed($resource, $privilege)) {
						return TRUE;
					}
				}
			}
		}
		return FALSE;
	}
	
}