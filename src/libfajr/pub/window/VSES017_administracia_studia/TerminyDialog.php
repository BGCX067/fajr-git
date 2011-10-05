<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Pub__Window__VSES017_administracia_studia
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace libfajr\pub\window\VSES017_administracia_studia;

use libfajr\pub\window\LazyDialog;
use libfajr\pub\data_manipulation\SimpleDataTable;
use libfajr\pub\base\Trace;

interface TerminyDialog extends LazyDialog
{
  /**
   * @returns SimpleDataTable
   */
  public function getZoznamTerminov(Trace $trace);

  /**
   * @returns ZoznamPrihlasenychDialog
   */
  public function getZoznamPrihlasenychDialog(Trace $trace, $terminIndex);

  public function prihlasNaTermin(Trace $trace, $terminIndex);
}
