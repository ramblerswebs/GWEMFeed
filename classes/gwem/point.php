<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of file
 *
 * @author Keith Grimes
 */

class GwemPoint {
    public $description ;
    public $easting;    // Not provided
    public $gridref;    // Not provided
    public $latitude;
    public $longitude;
    public $northing;   // Not provided
    public $postcode;
    public $postcodeLatitude; // Not Provided
    public $postcodeLongitude; // Not Provided
    public $showexact;  // Not set - default false
    public $time;           // Provided but not set
    public $typestring;

    public function __construct($wm_point, $type) {
        $typestring = $type;
        $gridref = $wm_point->grid_reference_6;
        $description = $wm_point->description;
        $latitude = $wm_point->latitude;
        $longitude = $wm_point->longitude;
        $postcode = $wm_point->postcode;
        $showexact = false;
    }
}


