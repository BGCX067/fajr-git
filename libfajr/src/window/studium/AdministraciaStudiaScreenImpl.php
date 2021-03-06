<?php
// Copyright (c) 2010-2011 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * TODO
 *
 * PHP version 5.3.0
 *
 * @package    Libfajr
 * @subpackage Window__Studium
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace libfajr\window\studium;

use libfajr\window\studium\AdministraciaStudiaScreen;
use libfajr\trace\Trace;
use libfajr\connection\SimpleConnection;
use libfajr\window\AIS2AbstractScreen;
use libfajr\window\RequestBuilderImpl;
use libfajr\window\ScreenRequestExecutor;
use libfajr\window\DialogData;
use libfajr\window\ScreenData;
use libfajr\data\AIS2TableParser;
use libfajr\data\DataTable;
use libfajr\data\ActionButton;
use libfajr\util\StrUtil;
use libfajr\util\MiscUtil;
use libfajr\exceptions\ParseException;

/**
 * Trieda reprezentujúca jednu obrazovku so zoznamom štúdií a zápisných listov.
 *
 * @package    Libfajr
 * @subpackage Window__Studium
 * @author     Martin Kralik <majak47@gmail.com>
 */
class AdministraciaStudiaScreenImpl extends AIS2AbstractScreen
    implements AdministraciaStudiaScreen
{
  const APP_LOCATION_PATTERN = '@webui\(\)\.startApp\("(?P<name>[^"]+)","(?P<params>[^"]+)"\);@';
  const PARAM_NAME_PATTERN = '@(?:&paramName=(?P<paramName>[A-Za-z0-9]*))@';
  
  /**
   * @var AIS2TableParser
   */
  private $parser;

  public function __construct(Trace $trace, ScreenRequestExecutor $executor, AIS2TableParser $parser)
  {
    $data = new ScreenData();
    $data->appClassName = 'ais.gui.vs.es.VSES017App';
    $data->additionalParams = array('kodAplikacie' => 'VSES017');
    $components['dataComponents']['studiaTable_dataView'] = new DataTable("studiaTable_dataView");
    $components['dataComponents']['zapisneListyTable_dataView'] = new DataTable("zapisneListyTable_dataView");

    $components['actionComponents']['nacitatDataAction'] = new ActionButton("nacitatDataAction");
    $components['actionComponents']['terminyHodnoteniaAction'] = new ActionButton("terminyHodnoteniaAction");
    $components['actionComponents']['hodnoteniaPriemeryAction'] = new ActionButton("hodnoteniaPriemeryAction");

    parent::__construct($trace, $executor, $data, $components);
    $this->parser = $parser;
  }

  public function getZoznamStudii(Trace $trace)
  {
    return $this->components['studiaTable_dataView'];
  }

  public function getZapisneListy(Trace $trace, $studiumIndex)
  {
    $table = $this->components['studiaTable_dataView'];
    $table->selectSingleRow((integer)$studiumIndex);
    $table->setActiveRow((integer)$studiumIndex);

    $this->doAction('nacitatDataAction');

    return $this->components['zapisneListyTable_dataView'];
  }

  public function getParamNameFromZapisnyListIndex(Trace $trace, $zapisnyListIndex, $action)
  {
    $table = $this->components['zapisneListyTable_dataView'];
    $table->selectSingleRow((integer)$zapisnyListIndex);
    $table->setActiveRow((integer)$zapisnyListIndex);

    $response = $this->doAction($action);

    try {
      return $this->parseParamNameFromResponse($response);
    }
    catch (ParseException $ex) {
      throw new ParseException("Nepodarilo sa zistiť paramName pre akciu $action: " . $ex->getMessage(), null, $ex);
    }
  }

  private function parseParamNameFromResponse($response)
  {
      $data = $response->getElementById("init-data");
      $data = $data->textContent;

      $data = StrUtil::match(self::APP_LOCATION_PATTERN, $data, 'params');
      if ($data === false) {
        throw new ParseException("Location of APP_PATTERN failed.");
      };
      $data = StrUtil::match(self::PARAM_NAME_PATTERN, $data, 'paramName');
      if ($data === false) {
        throw new ParseException("Parsing of ids from zapisnyListIndex failed.");
      }
      return $data;
  }
  
  public function getPrehladKreditovDialog(Trace $trace, $studiumIndex)
  {
    $data = new DialogData();
    $data->compName = 'ziskaneKredityAction';
    $data->embObjName = 'studiaTable';
    $data->index = $studiumIndex;
    return new PrehladKreditovDialogImpl($trace, $this, $data);
  }

}
?>
