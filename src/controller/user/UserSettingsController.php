<?php
/**
 * Contains controller managing user preferences.
 *
 * @copyright  Copyright (c) 2010-2012 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Controller__User
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\controller\user;

use Exception;
use fajr\Context;
use fajr\controller\BaseController;
use libfajr\AIS2Utils;
use libfajr\base\Preconditions;
use libfajr\trace\Trace;
use fajr\Request;
use fajr\Response;
use fajr\util\FajrUtils;
use sfStorage;
use fajr\config\FajrConfig;
use fajr\config\FajrConfigLoader;
use fajr\settings\SkinSettings;
use fajr\SessionStorageProvider;
use fajr\LoginManager;
use fajr\exceptions\AuthenticationRequiredException;

/**
 * Controller, which manages user settings.
 *
 * @package    Fajr
 * @subpackage Controller__Settings
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class UserSettingsController extends BaseController
{
  private $settingsStorage;
  private $config;
  /** @var LoginManager */
  private $loginManager;
  
  public static function getInstance()
  {
    return new UserSettingsController(SessionStorageProvider::getInstance(),
        FajrConfigLoader::getConfiguration(), LoginManager::getInstance());
  }

  public function __construct(sfStorage $settingsStorage, FajrConfig $config,
      LoginManager $loginManager)
  {
    $this->settingsStorage = $settingsStorage;
    $this->config = $config;
    $this->loginManager = $loginManager;
  }

  public function invokeAction(Trace $trace, $action, Context $context)
  {
    Preconditions::checkIsString($action);

    if (!$this->loginManager->isLoggedIn()) {
      throw new AuthenticationRequiredException();
    }

    parent::invokeAction($trace, $action, $context);
  }

  public function runSettings(Trace $trace, Context $context)
  {
    $request = $context->getRequest();
    $response = $context->getResponse();

    $response->setTemplate('settings/settings');
  }

  public function runSkin(Trace $trace, Context $context)
  {
    $request = $context->getRequest();
    $response = $context->getResponse();
    $skinSettings = new SkinSettings($this->config, $this->settingsStorage);

    // set skin
    if ($request->getParameter('skinSelect')) {
      $skinSettings->setUserSkinName($request->getParameter('skinSelect'));
      // apply the skin for current request(user skin may be applied before this function)
      $response->setSkin($skinSettings->getUserSkin());
    }

    $response->set('availableSkins', $skinSettings->getAvailableSkins());
    $response->set('currentSkin', $skinSettings->getUserSkinName());

    $response->setTemplate('settings/skin');
  }
}
