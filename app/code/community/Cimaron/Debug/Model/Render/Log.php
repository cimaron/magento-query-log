<?php

class Cimaron_Debug_Model_Render_Log extends Cimaron_Debug_Model_Render_Abstract {

	/**
	 *
	 */
	public function render($observer) {
	
		$data = $this->getData();
		
		$route = $this->getRoute();

		foreach ($data as $log) {
		
			$stack = $log->getStack();

			$out = sprintf("%s\n\t%s\n\t%s\n\t%s\n\t",
				$route,
				str_replace("\n", " ", $log->getSql()),
				sprintf("%s:%s", $stack[0]['file'], $stack[0]['line']),
				$this->_renderTime($log->getTime())
			);

	        Mage::log($out, null, "query.log", true);		
		}
	
	}

    /**
     *
     */
    protected function getRoute() {

		$request = Mage::app()->getRequest();

		$route = sprintf("%s/%s/%s",
			$request->getRequestedRouteName(),
			$request->getRequestedControllerName(),
			$request->getRequestedActionName()
		);

		return $route;
    }
}

