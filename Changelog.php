<?php
class Changelog {
    private static $changes = array (
        array('2010-01-xx', '0.1', 'Maják zverejnil prvú verziu Fajr-u'),
        array('2010-02-15', '0.1', 'Fajr sa presunul na google code'),
        array('2010-02-16', '0.1', 'AIS2 sa upgradol, prestali fungovať  niektoré veci'),
        array('2010-05-22', '0.2', 'Pribudli nové tabuľky, skryli sme
              zbytočné stĺpce a celkovo vylepšili vzhľad'),
        array('2010-05-23', '0.2', 'Fajr prešiel na beta testing :-)'),
        );

    private static $limit = 6;

    public static function getChangelog() {

        $data = "<div class='changelog'>\n<strong>Changelog:</strong><ul>\n";
        $tmp_array = array_slice(array_reverse(Changelog::$changes), 0,
                          Changelog::$limit);
        foreach ($tmp_array as $change) {
            $data .= '<li>'.$change[0].' (verzia ' . $change[1] . ') - ';
            $data .= $change[2]."</li>\n";
        }
        $data .= "</ul></div>\n";
        return $data;
    }
}


?>