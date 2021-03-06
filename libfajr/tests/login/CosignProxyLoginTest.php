<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains tests for CosignProxyLogin class
 *
 * @package    Libfajr
 * @subpackage Login
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */

namespace libfajr\login;

use libfajr\login\CosignCookieLogin;
use libfajr\connection\AIS2ServerConnection;
use libfajr\connection\AIS2ServerUrlMap;
use libfajr\connection\HttpConnection;
use libfajr\exceptions\LoginException;
use libfajr\login\CosignServiceCookie;
use PHPUnit_Framework_TestCase;

/**
 * @ignore
 */
class CosignProxyLoginTest extends PHPUnit_Framework_TestCase
{
  private $connection;
  private $serverConnection;

  public function setUp()
  {
    $this->connection = $this->getMock('\libfajr\connection\HttpConnection');
    $this->serverConnection = new AIS2ServerConnection($this->connection,
        new AIS2ServerUrlMap("ais2.test"), null);
  }

  public function testLogin()
  {
    $_SERVER['REMOTE_USER']='remote-user';
    $_SERVER['COSIGN_SERVICE']='cosign-groupware2';
    $_SERVER['SERVER_NAME']='groupware2.cosign.test';
    $_COOKIE['cosign-groupware2']='mBzVvgxevy ayZDlBoS6I2Vmf15p5fCMhQB8Un-5 vgNxT19vii4gO9nMcCCTjD7FKaEoornGg0g6h8e5y1TXVj5ccIFQRmtd-dDYWCFR1J0dC1-ozurhrZ t250';
    $this->connection->expects($this->once())
                     ->method('addCookie')
                     ->with($this->equalTo('cosign-groupware'), 
                         $this->equalTo('qsZJi0nbBZfbJfDZ3dJ7J-5yqsEjON+DtqOHYnMEPTQCT7vzeomMO+CmkWV+w1aPk0gdxaIV-ucVPvG6lA4UYoGAbVDWrYK1UZCfbmzMQJH1QAH1W4+ieYUF89bV'),
                         $this->equalTo(0),
                         $this->equalTo('/'),
                         $this->equalTo('groupware.cosign.test'))
                     ->will($this->returnValue(true));
    $login = new CosignProxyLogin(__DIR__.'/testdata/cosignProxyDir', 'cosign-groupware');
    $login->login($this->serverConnection);
  }
}

?>
