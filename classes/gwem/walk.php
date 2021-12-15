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

class GwemWalk {

    const WM_TIMEFORMAT = "Y-m-d\TH:i:s";
    const GWEM_TIMEFORMAT = "Y-m-d\TH:i:s";


    public $id;              // id
    public $status;          // status
    public $difficulty;      // difficulty/description
    public $strands;
    public $linkedEvent;     // linked_event
    public $festivals;
    public $walkContact;     // walk_leaders
    public $linkedWalks;
    public $linkedRoute;
    public $title;           // title
    public $walkLeader;
    public $description;     // description
    public $groupCode;       // group_code
    public $groupName;       // group_name
    public $additionalNotes;
    public $date;            // start_date_time
    public $distanceKM;      // distance_km
    public $distanceMiles;   // distance_miles
    public $finishTime;      // finish_date_time
    public $suitability;
    public $surroundings;
    public $theme;
    public $specialStatus;
    public $facilities;      // facilities
    public $pace;
    public $ascentMetres;    // ascent_metres
    public $ascentFeet;      // ascent_feet
    public $gradeLocal;
    public $attendanceMembers;
    public $attendanceNonMembers;
    public $attendanceChildren;
    public $cancellationReason;  // cancellation_reason
    public $dateUpdated;     // date_updated
    public $dateCreated;     // date_created
    public $media ;
    public $points ;         // start_location, meeting_location
    public $groupInvite;     // groups_invited   ??
    public $isLinear;        // shape
    public $url;             // url

    public function __construct($wm_walk) {

        $this->id = $wm_walk->id;
        $this->status = new stdClass();
        $this->status->value = $wm_walk->status;

        $this->difficulty = new stdClass();
        $this->difficulty->text = ($wm_walk->difficulty != false) ? $wm_walk->difficulty->description : "Unknown";;

        $this->strands = new stdClass();
        $this->strands->items = array();
        $this->linkedEvent = new stdClass();
        $this->linkedEvent->text = "";                          // linked_event
        $this->festivals = new stdClass();
        $this->festivals->items = array();
        $this->walkContact;                                     // walk_leaders
        $this->linkedWalks = new stdClass();
        $this->linkedWalks->items = array();
        $this->linkedRoute;
        $this->title = $wm_walk->title;
        $this->walkLeader = null ;                              // walk_leaders     
        $this->description = $wm_walk->description ;
        $this->groupCode = $wm_walk->group_code;
        $this->groupName = $wm_walk->group_name;
        $this->additionalNotes = null;
        $walkDate = DateTime::createFromFormat(self::WM_TIMEFORMAT, $wm_walk->start_date_time);
        $walkDate->setTime(0,0,0,0);
        $this->date = $walkDate->format(self::GWEM_TIMEFORMAT);   
        $this->distanceKM = $wm_walk->distance_km;
        $this->distanceMiles = $wm_walk->distance_miles;
        $this->finishTime = null;                                 // finish_date_time
        $this->suitability = new stdClass();
        $this->suitability->items = array();
        $this->surroundings = new stdClass();
        $this->surroundings->items = array();
        $this->theme = new stdClass();
        $this->theme->items = array();
        $this->specialStatus = new stdClass();
        $this->specialStatus->items = array();
        $this->facilities = new stdClass();                       // facilities
        $this->facilities->items = array();
        $this->pace = null;
        $this->ascentMetres = $wm_walk->ascent_metres;
        $this->ascentFeet = $wm_walk->ascent_feet;
        $this->gradeLocal = ($wm_walk->difficulty != false) ? $wm_walk->difficulty->description : "Unknown";;
        $this->attendanceMembers = null;
        $this->attendanceNonMembers = null;
        $this->attendanceChildren = null;
        $this->cancellationReason = $wm_walk->cancellation_reason;
        $this->dateUpdated = $wm_walk->date_updated;
        $this->dateCreated = $wm_walk->date_created;
        $this->media = array();
        // Build up the points to store
        $this->points = array() ;                      // start_location, meeting_location
        //if ($wm_walk->start_locationarray_push($this->points, new GwemPoint($wm_walk->start_location, 'Start'));
        //if ($wm_walk->meeting_location != null) {
        //    array_push($this->points, new GwemPoint($wm_walks->meeting_location, 'Meeting'));
        //}
        $this->groupInvite = new stdClass(); 
        $this->groupInvite->groupCode = null;       // groups_invited   ??
        $this->isLinear = strtolower($wm_walk->shape) == "linear" ? TRUE : FALSE; 
        $this->url = $wm_walk->url;
    }
}


