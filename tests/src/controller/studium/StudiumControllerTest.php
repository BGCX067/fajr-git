<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains tests for StudiumController class
 *
 * @package    Fajr
 * @subpackage Controller__Studium
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\controller\studium;

use PHPUnit_Framework_TestCase;

use fajr\Request;
use fajr\Response;
use fajr\Context;
use fajr\MockInvocationParameters;
use libfajr\storage\TemporarilyModifiableStorage;
use libfajr\storage\MemoryStorage;
use libfajr\storage\FileStorage;
use libfajr\regression\fixtures\FakeData;
use libfajr\window\studium\StudiumFakeFactoryImpl;
use libfajr\trace\NullTrace;
use libfajr\window\AIS2ApplicationEnum;

/**
 * @ignore
 */
class StudiumControllerTest extends PHPUnit_Framework_TestCase
{
  private $request;
  private $response;
  private $context;
  private $storage;

  private $controller;

  public function setUp()
  {
    $this->response = new Response();
    $this->requestParams = new MockInvocationParameters();
    $time = mktime(0, 0, 0, 1, 8, 2010);
    $this->request = new Request($this->requestParams, $time);
    $this->context = new Context();
    $this->context->setRequest($this->request);
    $this->context->setResponse($this->response);
    $temporary_storage = new MemoryStorage();
    $permanent_storage = new FileStorage(
        array('root_path' => FakeData::getDirectory()));
    $this->storage = new TemporarilyModifiableStorage(
        array('permanent_storage' => $permanent_storage,
              'temporary_storage' => $temporary_storage));
    $this->context->setSessionStorage($this->storage);
    $factory = new StudiumFakeFactoryImpl($this->storage);
    $this->loginManager = $this->getMock('\fajr\LoginManager', array(), 
        array(), '', false);
    $this->loginManager->expects($this->any())
                       ->method('isLoggedIn')
                       ->will($this->returnValue(true));
    $this->router = $this->getMock('\fajr\Router', array(), array(), '', false);

    $this->controller = new StudiumController($factory, $time, $this->loginManager, $this->router);

    $this->storage->write('ais/aisApps', array(AIS2ApplicationEnum::ADMINISTRACIA_STUDIA));
  }

  public function testHodnotenia()
  {
    $this->controller->invokeAction(new NullTrace(), 'Hodnotenia', $this->context);
    $data = $this->response->getData();
    $this->assertEquals(10, count($data['hodnotenia']));
    $this->assertEquals(2, count($data['priemery']));
  }
}
