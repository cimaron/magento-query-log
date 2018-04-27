<?php

abstract class Cimaron_Debug_Model_Render_Abstract extends Mage_Core_Model_Abstract {

	abstract public function render($observer);
	
	public function getTotalTime() {
		
		$data = $this->getData();
		
		$sum = 0;
		foreach ($data as $item) {
			$sum += $item->getTime();
		}
		
		return $sum;
	}
	
	/**
	 *
	 */
	protected function _renderTime($time) {
		return ($time > 1 ? round($time, 3) . 's' : round($time * 1000, 3) . 'ms');
	}

}

