<?php

namespace app\components\Test;

use Nette\Application\UI;



/**
 * @adjust Nemá label
 * @resource test
 * @privilege view
 */
class Test3Control extends UI\Control
{
	
	/**
	 * @adjust Nemá label, dědí práva od třídy
	 */
	public function render()
	{
		$this->template->setFile(__DIR__ . '/test.latte');
		$this->template->render();
	}
	
	/**
	 * @adjust Nemá label, definuje si vlastní práva
	 * @resource test
	 * @privilege view
	 */
	public function renderbar()
	{
		$this->template->setFile(__DIR__ . '/test.latte');
		$this->template->render();
	}
}