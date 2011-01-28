<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * sfStorageAutoloader is an autoloader for the service container classes.
 *
 * @package    symfony
 * @subpackage dependency_injection
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfStorageAutoloader
{
  /**
   * Registers sfStorageAutoloader as an SPL autoloader.
   */
  static public function register()
  {
    ini_set('unserialize_callback_func', 'spl_autoload_call');
    spl_autoload_register(array(new self, 'autoload'));
  }

  /**
   * Handles autoloading of classes.
   *
   * @param  string  $class  A class name.
   *
   * @return boolean Returns true if the class has been loaded
   */
  public function autoload($class)
  {
    if (0 !== strpos($class, 'sf') || strpos($class, 'Storage') === null)
    {
      return false;
    }

    $filename = dirname(__FILE__).'/lib/'.$class.'.class.php';
    if (is_file($filename) ) {
      require $filename;
      return true;
    }
    return false;
  }
}
