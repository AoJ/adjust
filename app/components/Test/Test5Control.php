<?php

namespace app\components\Test;

use Nette\Application\UI;



/**
 * @adjust Stejný label
 * @label Grupování podle labelu
 * @resource test
 * @privilege view
 */
class Test5Control extends UI\Control
{
	
	/**
	 * @adjust
	 */
	public function render()
	{
		$this->template->setFile(__DIR__ . '/test.latte');
		$this->template->render();
	}
}