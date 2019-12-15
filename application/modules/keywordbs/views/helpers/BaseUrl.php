<?php
/**
 * BaseUrl.php
 */

/** Zend_View_Helper_Abstract.php */
require_once 'Zend/View/Helper/Abstract.php';

class Zend_View_Helper_BaseUrl extends Zend_View_Helper_Abstract
{
	function __constract()
	{
	}

	function baseUrl()
	{
		$x = substr($_SERVER['PHP_SELF'], 0, -9);
		return $x;
	}
}