<?php
/*
Copyright (c) 2010 Martin Králik

 Permission is hereby granted, free of charge, to any person
 obtaining a copy of this software and associated documentation
 files (the "Software"), to deal in the Software without
 restriction, including without limitation the rights to use,
 copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the
 Software is furnished to do so, subject to the following
 conditions:

 The above copyright notice and this permission notice shall be
 included in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 OTHER DEALINGS IN THE SOFTWARE.
*/

	function redirect($url = null)
	{
		if ($url === null) $url = 'fajr.php';
		header('Location: '.$url);
		exit();
	}
	
	function pluck($haystack, $pattern)
	{
		$matches = array();
		if (!preg_match($pattern, $haystack, $matches)) return false;
		return $matches[1];
	}
	
	function pluckAll($haystack, $pattern)
	{
		$matches = array();
		if (!preg_match_all($pattern, $haystack, $matches, PREG_SET_ORDER)) return false;
		else return $matches;
	}
	
	function dump($s)
	{
		if (is_array($s)) foreach ($s as $value) dump($value);
		else echo '<pre>'.htmlspecialchars($s).'</pre><hr/>';
	}
	
	function gzdecode($data)
	{
		$g = tempnam(dirname(__FILE__).DIRECTORY_SEPARATOR.'temp', 'gzip');
		@file_put_contents($g, $data);
		ob_start();
		readgzfile($g);
		$d = ob_get_clean();
		unlink($g);
		return $d;
	}
	
	function getCookieFile()
	{
		return dirname(__FILE__).DIRECTORY_SEPARATOR.'cookies'.DIRECTORY_SEPARATOR.session_id();
	}
	
	function generatePattern($tableDefinition)
	{
		$pattern = '@\<tr id\=\'row_(?P<index>[^\']*)\' rid\=\'[^\']*\'\>';
		foreach ($tableDefinition as $column)
		{
			$pattern .= '\<td[^>]*\>\<div\>(?P<'.$column['name'].'>[^<]*)\</div\>\</td\>';
		}
		$pattern .= '\</tr\>@';
		return $pattern;
	}

	
?>
