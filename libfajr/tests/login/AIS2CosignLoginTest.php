<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains tests for AIS2CosignLoginImpl class
 *
 * @package    Libfajr
 * @subpackage Login
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace libfajr\login;

use libfajr\login\CosignLogin;
use libfajr\connection\AIS2ServerConnection;
use libfajr\connection\AIS2ServerUrlMap;
use libfajr\connection\HttpCoonection;
use libfajr\exceptions\AIS2LoginException;
use PHPUnit_Framework_TestCase;

/**
 * @ignore
 */
class AIS2CosignLoginTest extends PHPUnit_Framework_TestCase
{
  private $responseLoggedIn;
  private $responseNotLogged;
  private $responseLogout;

  private $serverConection;
  private $connection;
  private $cosignLogin;
  private $login;

  public function setUp()
  {
    $this->responseLoggedIn = file_get_contents(__DIR__.'/testdata/aisLoggedIn.dat');
    $this->responseNotLogged = file_get_contents(__DIR__.'/testdata/aisNotLogged.dat');
    $this->responseLogout = file_get_contents(__DIR__.'/testdata/aisLogout.dat');

    $this->connection = $this->getMock('\libfajr\connection\HttpConnection');
    $this->cosignLogin = $this->getMock('\libfajr\login\Login');
    $this->serverConnection = new AIS2ServerConnection($this->connection,
        new AIS2ServerUrlMap("ais2.test"), null);
    $this->login = new AIS2CosignLogin($this->cosignLogin);
  }

  public function testIsLoggedAlreadyLogged()
  {
    $this->connection->expects($this->once())
                     ->method('get')
                     ->will($this->returnValue($this->responseLoggedIn));
    $this->assertTrue($this->login->isLoggedIn($this->serverConnection));
  }

  public function testIsLoggedNotLogged()
  {
    $this->connection->expects($this->once())
                     ->method('get')
                     ->will($this->returnValue($this->responseNotLogged));
    $this->assertFalse($this->login->isLoggedIn($this->serverConnection));
  }

  public function testIsLoggedFailure()
  {
    $this->connection->expects($this->once())
                     ->method('get')
                     ->will($this->returnValue("problem"));
    $this->setExpectedException('\Exception');
    $this->login->isLoggedIn($this->serverConnection);
  }

  public function testLoginOk()
  {
    $this->connection->expects($this->once())
                     ->method('get')
                     ->will($this->returnValue($this->responseLoggedIn));
    $this->cosignLogin->expects($this->once())
                      ->method('login')
                      ->will($this->returnValue(true));
    $this->login->login($this->serverConnection);
  }

  public function testLoginFailure()
  {
    $this->connection->expects($this->once())
                     ->method('get')
                     ->will($this->returnValue($this->responseNotLogged));
    $this->cosignLogin->expects($this->once())
                      ->method('login')
                      ->will($this->returnValue(true));
    $this->setExpectedException('\Exception');
    $this->login->login($this->serverConnection);
  }

}

?>
