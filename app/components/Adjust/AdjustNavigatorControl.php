<?php

namespace app\components\Adjust;

use Nette\Application\UI,
	Nette\ComponentModel\IContainer,
	app\services\AdjustManager,
	app\services\Translator;



class AdjustNavigatorControl extends UI\Control
{
	/** @var \app\services\Adjust\AdjustManager */
	public $adjustManager;
	
	/** @var \app\services\Translator */
	public $translator;
	
	public function __construct(AdjustManager $adjustManager, Translator $translator, IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);
		$this->adjustManager = $adjustManager;
		$this->translator = $translator;
	}
	
	public function render()
	{
		$this->template->setTranslator($this->translator);
		$this->template->setFile(__DIR__ . '/navigator.latte');

		$this->template->menu = $this->buildMenu($this->adjustManager->adjustData);
		
		$this->template->render();
	}
	
	/**
	 * @param array $data
	 * @return array 
	 */
	protected function buildMenu(array $data)
	{
		$menu = array();
		foreach ($data as $page => $pageData) {
			foreach ($pageData['methods']['render'] as $method => $methodData) {
				if ($this->adjustManager->isViewAllowed($page, $method)) {
					$menu[$pageData['label']][] = array(
						'page' => $page,
						'method' => $method,
						'label' => $methodData['label']
					);
				}
			}
		}
		return $menu;
	}
	
}