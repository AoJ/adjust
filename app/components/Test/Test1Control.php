<?php

namespace app\components\Test;

use Nette\Application\UI;



/**
 * @adjust
 * @label Komponenta s jedinou metodou render()
 * @resource test
 * @privilege view
 */
class Test1Control extends UI\Control
{
	
	/**
	 * @adjust Dědí práva od třídy
	 * @label Defaultní metoda
	 */
	public function render()
	{
		$this->template->setFile(__DIR__ . '/test.latte');
		$this->template->render();
	}
	
}