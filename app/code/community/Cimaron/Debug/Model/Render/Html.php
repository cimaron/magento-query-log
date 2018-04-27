<?php

class Cimaron_Debug_Model_Render_Html extends Cimaron_Debug_Model_Render_Abstract {

	protected $styles = array(
				'debug' => 'text-align: left; background-color: #FFFFFF; font-size: 14px; font-family: monospace',
				'debug_line' => 'border-bottom: solid 1px #CCCCCC; padding: 10px;',
				'debug_files' => 'font-size: 12px; padding: 10px; list-style: decimal inside; display: none;',
				'debug_stack' => 'color: #3399FF; padding: 10px; display: block;',
				'debug_file' => '',
				'debug_file_line' => 'font-weight: bold;',
				'debug_sql' => 'background-color: #FFFFEE; padding: 10px; border: dotted 1px #FF0000;',
				'debug_time' => 'font-weight: bold; color: #000033; padding: 10px;',
			);

	/**
	 *
	 */
	public function render($observer) {

		if (isset($_REQUEST['isAjax'])) {
			return;
		}

		$response = $observer->getEvent()->getResponse();
		
		$body = $response->getBody();
		
		$out = "";
		$out .= $this->_renderStats();
		$out .= $this->_renderSql();
		
		if (strpos($body, '</html>') !== false) {
			$body = str_replace('</html>', $out . '</html>', $body);
		} else {
			$body .= $out;
		}

		$response->setBody($body);		
	}

	/**
	 *
	 */
	protected function _renderStats() {

		$out = "";

		$styles = $this->styles;

		$out .= '<div style="' . $styles['debug'] . '">';
		$out .= "\n";

		$out .= '<div style="' . $styles['debug_time'] . '">';
		$out .= 'Total Queries: ' . count($this->getData());
		$out .= '</div>';		
		$out .= "\n";

		$out .= '<div style="' . $styles['debug_time'] . '">';
		$out .= 'Total Time: ' . $this->_renderTime($this->getTotalTime());
		$out .= '</div>';
		$out .= "\n";

		$out .= '</div>';
		$out .= "\n";
		
		return $out;
	}
	
	/**
	 *
	 */
	protected function _renderSql() {

		$out = "";
		
		if (count($this->getData())) {

			$styles = $this->styles;
			$out .= '<div style="' . $styles['debug'] . '">';
			foreach ($this->getData() as $log) {
				$out .= '<div style="' . $styles['debug_line'] . '">';
				$out .= "\n";
				
				$out .= '<div style="' . $styles['debug_sql'] . '">';
				$out .= $this->_prettySql($log->getSql());
				$out .= '</div>';
				$out .= "\n";
				
				$out .= '<div style="' . $styles['debug_time'] . '">';
				$out .= 'Time: ' . $this->_renderTime($log->getTime());
				$out .= '</div>';				
				$out .= "\n";

				$out .= '<a style="' . $styles['debug_stack'] . '" href="#" onclick="' .
					'var s = this.nextSibling.style;' . 
					'if (s.display == \'none\') { ' . 
						's.display = \'block\';' .
						'this.innerHTML = \'Stack &uarr;\';' .
					'} else {' . 
						's.display = \'none\';' . 				
						'this.innerHTML = \'Stack &darr;\';' .
					'}' . 
					'return false;"' . 
					'>Stack &darr;</a>';
				
				$out .= '<ol style="' . $styles['debug_files'] . '">';
				foreach ($log->stack as $file) {
					$out .= '<li style="' . $styles['debug_file'] . '">';
					$out .= $file['file'] . ':<span style="' . $styles['debug_file_line'] . '">' . $file['line'] . '</span>';
					$out .= '</li>';
					$out .= "\n";
				}
				$out .= '</ol>';
				$out .= "\n";
				
				$out .= '</div>';
				$out .= "\n";
			}
			$out .= '</div>';
			$out .= "\n";
		}

		return $out;
	}
	

	
	/**
	 * Borrowed from plg_system_debug in Joomla project
	 */
	protected function _prettySql($sql) {

		$prefix = Mage::getConfig()->getTablePrefix();
		if (!trim($prefix)) {
			$prefix = "__NO_PREFIX__";
		}

		$newlineKeywords = '#\b(FROM|LEFT|INNER|OUTER|WHERE|SET|VALUES|ORDER|GROUP|HAVING|LIMIT|ON|AND|CASE)\b#i';

		$sql = htmlspecialchars($sql, ENT_QUOTES);

		$sql = preg_replace($newlineKeywords, "<br />\n&#160;&#160;\\0", $sql);

		$regex = array(

		// Tables are identified by the prefix
		'/(=)/'
		=> '<b style="color: #990000; font-weight: bold;">$1</b>',

		// All uppercase words have a special meaning
		'/(?<!\w|>)([A-Z_]{2,})(?!\w)/x'
		=> '<span style="color: #000066; font-weight: bold;">$1</span>',

		// Tables are identified by the prefix
		'/(' . $prefix . '[a-z_0-9]+)/'
		=> '<span style="color: #006600; font-weight: bold;">$1</span>'

		);

		$sql = preg_replace(array_keys($regex), array_values($regex), $sql);

		$sql = str_replace('*', '<b style="color: red;">*</b>', $sql);

		return $sql;
	}	
}

