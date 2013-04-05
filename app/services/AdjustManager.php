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
	const VIEW_PREFIX = 'render',
			SIGNAL_PREFIX = 'handle';
	
	
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
			$classRef = new Reflection\ClassType($class);
			if (!$classRef->hasAnnotation('adjust')) {
				continue;
			}
			if (!$classRef->implementsInterface('Nette\Application\UI\IRenderable')) {
				throw new InvalidArgumentException("Component '$class' must be instance of IRenderable.");
			}

			$pageResources = $this->annotationToArray($classRef->getAnnotation('resource'));
			$pagePrivileges = $this->annotationToArray($classRef->getAnnotation('privilege'));

			$pageMethods = array(
				self::VIEW_PREFIX => array(),
				self::SIGNAL_PREFIX => array()
			);
			
			foreach ($classRef->getMethods(ReflectionMethod::IS_PUBLIC
					& ~ReflectionMethod::IS_ABSTRACT
					& ~ReflectionMethod::IS_STATIC) as $methodRef) {
				$methodName = $methodRef->getName();
				$methodType = substr($methodName, 0, 6);
				if ($methodType !== self::VIEW_PREFIX && $methodType !== self::SIGNAL_PREFIX) {
					continue;
				}
				if (!$methodRef->hasAnnotation('adjust')) {
					continue;
				}
				
				$method = strtolower(substr($methodName, 6));
				$pageMethods[$methodType][$method] = array(
					'label' => $methodRef->getAnnotation('label') ?: ($method ?: 'default'),
					'resources' => array_unique(array_merge(
							$this->annotationToArray($methodRef->getAnnotation('resource')),
							$pageResources)),
					'privileges' => array_unique(array_merge(
							$this->annotationToArray($methodRef->getAnnotation('privilege')),
							$pagePrivileges))
				);
			}

			$adjustData[$page] = array(
				'class' => $class,
				'file' => $classRef->getFileName(),
				'label' => $classRef->getAnnotation('label') ?: $page,
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
		return $this->isAllowed($page, self::VIEW_PREFIX, $method);
	}
	
	
	
	/**
	 * @param string $page
	 * @param string $signal
	 * @return boolean 
	 */
	public function isSignalAllowed($page, $signal)
	{
		return $this->isAllowed($page, self::SIGNAL_PREFIX, $signal);
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

	
	
	/**
	 * @param string $annotation 
	 * @return array
	 */
	protected function annotationToArray($annotation)
	{
		return array_filter(preg_split('/\s+/', $annotation));
	}
}