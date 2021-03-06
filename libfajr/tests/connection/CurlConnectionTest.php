<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Libfajr
 * @subpackage Connection
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace libfajr\connection;
use PHPUnit_Framework_TestCase;
use libfajr\connection\CurlConnection;
use libfajr\trace\NullTrace;
/**
 * @ignore
 */
class CurlConnectionTest extends PHPUnit_Framework_TestCase
{
  const COOKIE_FILE = '/tmp/curl_connection_test';
  /** curl connection options */
  private $opt;

  public function setUp()
  {
    @unlink(self::COOKIE_FILE);
    $this->opt = array (
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_VERBOSE => false,
        );
    $this->markTestSkipped('This test uses unreliable resources and is flaky.');
  }

  public function tearDown()
  {
    @unlink(self::COOKIE_FILE);
  }

  public function testGet()
  {
    $connection = new CurlConnection($this->opt, self::COOKIE_FILE);

    $response = $connection->get(new NullTrace, 'fmph.uniba.sk');
    $this->assertRegExp("@<title>Fakulta matematiky, fyziky a informatiky</title>@",
                        $response);
  }

  public function testPostAndCookiePersistence()
  {
    $connection = new CurlConnection($this->opt, self::COOKIE_FILE);
    // zozen si cookie do cosignu, inak to nefunguje
    $response = $connection->get(new NullTrace, 'https://login.uniba.sk');
    // skus sa prihlasit
    $this->assertPostCosignLogin($connection, true);
  }

  private function assertPostCosignLogin($connection, $shouldSucceed)
  {
    $response = $connection->post(new NullTrace, 'https://login.uniba.sk/cosign.cgi',
                                  array('login' => 'fajr_curl_connection_test_username',
                                        'password' => 'password',
                                        'submit' => 'Prihlásiť'));

    $this->assertEquals($shouldSucceed,
                        preg_match("@fajr_curl_connection_test_username@", $response) > 0);
  }

  public function testAddCookies()
  {
    $connection = new CurlConnection($this->opt, self::COOKIE_FILE);
    $response = $connection->get(new NullTrace, 'https://login.uniba.sk');
    // reset cookie to wrong one
    $connection->addCookie("cosign", "wrong_cookie_value", 0, "/", "login.uniba.sk");
    $this->assertPostCosignLogin($connection, false);
  }

  public function testClearCookies()
  {
    $connection = new CurlConnection($this->opt, self::COOKIE_FILE);
    $response = $connection->get(new NullTrace, 'https://login.uniba.sk');
    $connection->clearCookies();
    $this->assertPostCosignLogin($connection, false);
  }

  public function testAddAndClearCookies()
  {
    $connection = new CurlConnection($this->opt, self::COOKIE_FILE);
    $response = $connection->get(new NullTrace, 'https://login.uniba.sk');
    // reset cookie to wrong one
    $connection->addCookie("cosign", "wrong_cookie_value", 0, "/", "login.uniba.sk");
    $this->assertPostCosignLogin($connection, false);
    $connection->clearCookies();
    $this->assertPostCosignLogin($connection, false);
    $response = $connection->get(new NullTrace, 'https://login.uniba.sk');
    $this->assertPostCosignLogin($connection, true);
  }
}
