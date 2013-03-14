<?php

namespace app\components\Test;

use Nette\Application\UI;



/**
 * @adjust
 * @label Komponenta s jedinou metodou renderfoo()
 */
class Test2Control extends UI\Control
{
	
	/**
	 * @adjust Definuje si vlastní práva
	 * @label Foo metoda
	 * @resource test
	 * @privilege view
	 */
	public function renderfoo()
	{
		$this->template->setFile(__DIR__ . '/test.latte');
		$this->template->render();
	}
}