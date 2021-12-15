<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of file
 *
 * @author Chris Vaughan
 */
class WalksFile {

    const TIMEFORMAT = "Y-m-d\TH:i:s";
    private $walks = [];

    public function __construct($walksfile) {
        $json = file_get_contents($walksfile);
        $this->walks = json_decode($json);
    }

    public function allWalks() {
        return $this->walks;
    }

    public static function cmpDates($a, $b) {
        $walkDate1 = DateTime::createFromFormat(self::TIMEFORMAT, $a->date);
        $walkDate2 = DateTime::createFromFormat(self::TIMEFORMAT, $b->date);
        $match = $walkDate1 > $walkDate2 ? 1 : -1;

        return $match;
    }
}

