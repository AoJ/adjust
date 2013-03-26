<?php

namespace app\components\Test;

use Nette\Application\UI;



/**
 * @adjust Stejný label
 * @label Grupování podle labelu
 * @resource test
 * @privilege view
 */
class Test4Control extends UI\Control
{
	
	/**
	 * @adjust
	 * @label Komponenta 4
	 */
	public function render()
	{
		$this->template->setFile(__DIR__ . '/test.latte');
		$this->template->render();
	}
}