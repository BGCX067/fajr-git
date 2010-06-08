<?php
/* {{{
Copyright (c) 2010 Martin Sucha
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
 }}} */

	require_once 'supporting_functions.php';
	require_once 'AIS2AbstractLogin.php';
	require_once 'AIS2Utils.php';

/**
 * Trieda reprezentujúca prihlasovanie pomocou cookie
 *
 * @author majak, ms
 */
class AIS2CookieLogin extends AIS2AbstractLogin {
	private $cookie = null;

	public function  __construct($cookie) {
		assert($cookie !== null);
		$this->cookie = $cookie;
	}

	public function login() {
		assert($this->cookie !== null);
		if ($this->loggedIn) return false;

		$cookieFile = getCookieFile();
		$fh = fopen($cookieFile, 'a');
		if (!$fh) throw new Exception('Neviem otvoriť súbor s cookies.');
		fwrite($fh, "ais2.uniba.sk	FALSE	/	TRUE	0	cosign-filter-ais2.uniba.sk	".str_replace(' ', '+', $this->cookie));
		fclose($fh);
		$data = AIS2Utils::request(self::LOGIN);
		if (preg_match('@\<title\>IIKS \- Prihlásenie\</title\>@', $data))
			return false;
		$this->loggedIn = true;
		return true;
	}

}