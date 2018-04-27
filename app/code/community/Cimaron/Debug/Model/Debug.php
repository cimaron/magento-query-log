<?php

class Cimaron_Debug_Model_Debug extends Mage_Core_Model_Abstract {

	protected static $sql_log = array();

	/**
	 *
	 */
	public function start($stmt, $params = null) {

		$sql = (string)$stmt;
		if (!empty($params)) {

			$idx = ($params == array_values($params));
		
			foreach ($params as $name => $param) {

				if (is_string($param)) {
					$param = "'" . $param . "'";
				}

				if ($idx) {
					$sql = preg_replace('/\?/', $param, $sql, 1);
				} else {
					$sql = str_replace($name, $param, $sql);
				}
			}
		}
		
		self::$sql_log[] = new Cimaron_Debug_Model_Debug_Query(array(
			'stmt' => $stmt,
			'params' => $params,
			'sql' => $sql,
			'start' => microtime(true),
			'end' => 0,
			'time' => 0,
			'stack' => array(),
		));
	}
	
	/**
	 *
	 */
	public function end() {

		$log = array_pop(self::$sql_log);
		$bt = debug_backtrace();

		//Find top of stack at event all
		$found = false;

		foreach ($bt as $i => $trace) {
			//echo $trace['function'] . "\n";
			if ($trace['function'] == 'query') {
				$found = true;
			} elseif ($found) {
				break;
			}
		}

		$stack = $log->getStack() ? $log->getStack() : array();

		//Record stack trace
		for ($j = $i; $j < count($bt); $j++) {
			$file = $bt[$j]['file'];
			if (strpos($file, realpath($_SERVER['DOCUMENT_ROOT'])) === 0) {
				$file = '~'.str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', $file);		
			}
			$stack[] = array('file' => $file, 'line' => $bt[$j]['line']);
		}
		
		$log->setStack($stack);

		$log->setEnd(microtime(true));
		$log->setTime($log->end - $log->start);

		self::$sql_log[] = $log;	
	}

	/**
	 *
	 */
	public function onBeforeQuery($observer) {

		$stmt = $observer->getEvent()->getStmt();
		$params = $observer->getEvent()->getParams();

		$this->start($stmt->queryString, $params);
	}

	/**
	 *
	 */
	public function onAfterQuery($observer) {
	
		$stmt = $observer->getEvent()->getStmt();

		$this->end();
	}

	/**
	 *
	 */
	public function onHttpResponseSendBefore($observer) {

		$renderer = Mage::getModel('cimaron_debug/render_log');
		$renderer->setData(self::$sql_log);		
		$renderer->render($observer);
	}
}

