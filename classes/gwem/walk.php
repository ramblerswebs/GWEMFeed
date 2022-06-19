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

//    const WM_TIMEFORMAT = "Y-m-d\TH:i:s.vZ";
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
        //$this->id = str_replace("WM", "", $wm_walk->id); // This should be removed post testing
        $this->status = new stdClass();
        $this->status->value = ($wm_walk->status == "confirmed") ? "published" : "cancelled";
        $this->status->value = "published";

        $this->difficulty = new stdClass();
        $this->difficulty->text = ($wm_walk->difficulty != false) ? $wm_walk->difficulty->description : "Unknown";
        // Correct the case sensitivity
        if ($this->difficulty->text == "Easy access") {
            $this->difficulty->text = "Easy Access";
        }
        $this->strands = new stdClass();
        $this->strands->items = array();
        $this->linkedEvent = new stdClass();
        $this->linkedEvent->text = "";                          // linked_event
        $this->festivals = new stdClass();
        $this->festivals->items = array();
        $this->walkContact = new stdClass();
        $this->walkContact->contact = new stdClass();
        if ($wm_walk->walk_leaders[0] != null)
        {
            $this->walkContact->contact->displayName = $wm_walk->walk_leaders[0]->name ;                                    // walk_leaders
            $this->walkContact->contact->email = $wm_walk->walk_leaders[0]->email_form;
            $this->walkContact->contact->telephone1 = $wm_walk->walk_leaders[0]->telephone;
            $this->walkLeader = $wm_walk->walk_leaders[0]->name ;                              // walk_leaders         
        }
        else{
            $this->walkContact->contact->displayName = "Not Set" ;                                    // walk_leaders
            $this->walkContact->contact->email = "Not Set";
            $this->walkContact->contact->telephone1 = "Not Set";
            //$this->walkLeader = $wm_walk->walk_leaders[0]->name ;                              // walk_leaders         
            $this->walkLeader = "Not Set";                              // walk_leaders         

        }
        $this->walkContact->isWalkLeader = true;
        $this->walkContact->contact->telephone2 = "";
        $this->walkContact->contact->groupCode = $wm_walk->group_code;

        $this->linkedWalks = new stdClass();
        $this->linkedWalks->items = array();
        $this->linkedRoute;
        $this->title = $wm_walk->title;
        $this->description = $wm_walk->description ;
        $this->groupCode = $wm_walk->group_code;
        $this->groupName = $wm_walk->group_name;
        $this->additionalNotes = "";
        $walkDate = DateTime::createFromFormat(self::WM_TIMEFORMAT, $wm_walk->start_date_time);
        if ($walkDate) {
            $walkDate->setTime(0,0,0,0); 
            $this->date = $walkDate->format(self::GWEM_TIMEFORMAT);   
        }
        $this->distanceKM = $wm_walk->distance_km;
        $this->distanceMiles = $wm_walk->distance_miles;
        $walkFinishTime = DateTime::createFromFormat(self::WM_TIMEFORMAT, $wm_walk->finish_date_time);
        if ($walkFinishTime) { $this->finishTime = $walkFinishTime->format('H:i:s');   }                            // finish_date_time
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
        $this->pace = "";
        $this->ascentMetres = $wm_walk->ascent_metres;
        $this->ascentFeet = $wm_walk->ascent_feet;
        $this->gradeLocal = ($wm_walk->difficulty != false) ? $wm_walk->difficulty->description : "Unknown";;
        $this->attendanceMembers = null;
        $this->attendanceNonMembers = null;
        $this->attendanceChildren = null;
        $this->cancellationReason = $wm_walk->cancellation_reason;
        $dateUpdated = DateTime::createFromFormat(self::WM_TIMEFORMAT, $wm_walk->date_updated);
        if ($dateUpdated) { $this->dateUpdated = $dateUpdated->format("Y-m-d\TH:i:sP"); }
        $dateCreated = DateTime::createFromFormat(self::WM_TIMEFORMAT, $wm_walk->date_created);
        if ($dateCreated) { $this->dateCreated = $dateCreated->format("Y-m-d\TH:i:sP"); }
        $this->media = array();

        // Build up the points to store
        $this->points = array() ;                      // start_location, meeting_location
        if ($wm_walk->start_location)
        {
            $len = count($this->points);
            $this->points[$len] = $this->location2point($wm_walk->start_location, "Start");
        }
        if ($wm_walk->meeting_location)
        {
            $len = count($this->points);
            $this->points[$len] = $this->location2point($wm_walk->meeting_location, "Meeting");
        }
        if ($wm_walk->finish_location)
        {
            $len = count($this->points);
            $this->points[$len] = $this->location2point($wm_walk->finish_location, "End");
        }
        $this->groupInvite = new stdClass(); 
        $this->groupInvite->groupCode = null;       // groups_invited   ??
        $this->isLinear = strtolower($wm_walk->shape) == "linear" ? TRUE : FALSE; 
        $this->url = $wm_walk->url;
    }

    private function location2point($location, $typeString)
    {
        $point = new stdClass();

        $point->description = $location->description ;
        $point->gridRef = $location->grid_reference_6;
        $point->latitude = $location->latitude;
        $point->longitude = $location->longitude;
        $point->postcode = $location->postcode;
        $point->postcodeLatitude = 0;
        $point->postcodeLongitude = 0;
//        $point->Easting = "";
//        $point->Northing = "";
        $point->showExact = true;
        $pointTime = DateTime::createFromFormat(self::WM_TIMEFORMAT, $location->date_time);
        if ($pointTime) { $point->time = $pointTime->format('H:i:s'); }                              
        // finish_date_time
        $point->typeString = $typeString;
        return $point;
    }
}


