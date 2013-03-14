<?php

use app\components\Adjust\AdjustNavigatorControl,
	app\services\AdjustManager,
	app\services\Translator,
	Nette\Application;



class AdjustPresenter extends BasePresenter
{
	/** @var string @persistent */
	public $page;

	/** @var string @persistent */
	public $method;
	
	/** @var \app\services\AdjustManager */
	public $adjustManager;
	
	/** @var \app\services\Translator */
	public $translator;
	
	protected function startup()
	{
		parent::startup();
		
		if ($this->signal !== NULL) {
			if (!$this->adjustManager->isSignalAllowed($this->page, $this->signal)) {
				throw new Application\ForbiddenRequestException;
			}
		}
		
		if (!$this->adjustManager->isViewAllowed($this->page, $this->method)) {
			throw new Application\ForbiddenRequestException;
		}
	}
	
	public function renderPage()
	{
		$this->template->page = $this->page;
		$this->template->method = $this->method;
	}
	
	public function createComponentMenu($name)
	{
		return new AdjustNavigatorControl($this->adjustManager, $this->translator, $this, $name);
	}
	
	public function createComponentPage($name)
	{
		$page = $this->adjustManager->getPage($this->page);
		return $page ? new $page['class']($this, $name) : NULL;
	}
	
	public function injectAdjustManager(AdjustManager $adjustManager)
	{
		$this->adjustManager = $adjustManager;
	}
	
	public function injectTranslator(Translator $translator)
	{
		$this->translator = $translator;
	}
	
}