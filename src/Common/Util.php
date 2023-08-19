<?php

namespace App\Common;

class Util
{
    /**
     * Convert all applicable characters to HTML entities.
     *
     * @param string $text The string
     *
     * @return string The html encoded string
     */
    public static function html(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

	public static function getQueryParam($params, $key) 
	{
		if (empty($params) || array_key_exists($key, $params)) return null;
		
		return $params[$key];
	}
}
