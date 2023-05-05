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
        $this->status = new stdClass();
        $this->status->value = ($wm_walk->status == "confirmed") ? "published" : "cancelled";

        $this->difficulty = new stdClass();
        $this->difficulty->text = ($wm_walk->difficulty != false) ? $wm_walk->difficulty->description : "Moderate";
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
        if ($wm_walk->walk_leader != null)
        {
            $this->walkContact->contact->displayName = $wm_walk->walk_leader->name ;                                    // walk_leaders
            $this->walkContact->contact->form = $wm_walk->walk_leader->email_form;
            $this->walkContact->contact->telephone1 = ($wm_walk->walk_leader->telephone == null) ? "" : $wm_walk->walk_leader->telephone;
            $this->walkLeader = $wm_walk->walk_leader->name ;                              // walk_leaders         
        }
        else{
            $this->walkContact->contact->displayName = "" ;                                    // walk_leaders
            $this->walkContact->contact->email = "";
            $this->walkContact->contact->telephone1 = "";
            $this->walkLeader = "";                              // walk_leaders         

        }
        $this->walkContact->isWalkLeader = false;
        $this->walkContact->contact->telephone2 = "";

        $this->linkedWalks = new stdClass();
        $this->linkedWalks->items = array();
        $this->linkedRoute;
        $this->title = $wm_walk->title;
        $this->description = $wm_walk->description ;
        $this->groupCode = $wm_walk->group_code;
        $this->groupName = $wm_walk->group_name;
        $this->additionalNotes = $wm_walk->additional_details;
        $walkDate = DateTime::createFromFormat(self::WM_TIMEFORMAT, $wm_walk->start_date_time);
        if ($walkDate) {
            try {
                    $walkDate->setTime(0,0,0,0); 
                }
            catch (Exception $e)
                {
                    $a = $e;
                }
            $this->date = $walkDate->format(self::GWEM_TIMEFORMAT);   
        }
        $this->distanceKM = $wm_walk->distance_km;
        $this->distanceMiles = $wm_walk->distance_miles;
        $walkFinishTime = DateTime::createFromFormat(self::WM_TIMEFORMAT, $wm_walk->end_date_time);
        if ($walkFinishTime) { $this->finishTime = $walkFinishTime->format('H:i:s');   }                            // finish_date_time
        $this->suitability = new stdClass();
        $this->suitability->items = array();
        $this->surroundings = new stdClass();
        $this->surroundings->items = array();
        $this->theme = new stdClass();
        $this->theme->items = array();
        $this->specialStatus = new stdClass();
        $this->specialStatus->items = array();
        $this->facilities = new stdClass();
        $this->map_services($wm_walk->facilities, $this->facilities);
        $this->map_services($wm_walk->accessibility, $this->suitability);
        $this->map_services($wm_walk->transport, $this->facilities);
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
        $mediacount = 0;
        if ($wm_walk->media != null)
        {
            foreach($wm_walk->media as $walkmedia)
            {
                $this->media[$mediacount] = new stdClass();
                $this->media[$mediacount]->caption = $wm_walk->media[$mediacount]->alt;
                $this->media[$mediacount]->copyright = "";
                $pos = strrpos($wm_walk->media[$mediacount]->styles[2]->url,'/', 0);
                $this->media[$mediacount]->url =  $wm_walk->media[$mediacount]->styles[2]->url;
                $this->media[$mediacount]->fileName = substr($wm_walk->media[$mediacount]->styles[2]->url, $pos+1);
                $mediacount++;
            }
        }

        // Build up the points to store
        $this->points = array() ;                      // start_location, meeting_location
        if ($wm_walk->start_location)
        {
            $len = count($this->points);
            $this->points[$len] = $this->location2point($wm_walk->start_location, "Start", $wm_walk->start_date_time);
        }
        if ($wm_walk->meeting_location)
        {
            $len = count($this->points);
            $this->points[$len] = $this->location2point($wm_walk->meeting_location, "Meeting", $wm_walk->meeting_date_time);
        }
        if ($wm_walk->end_location)
        {
            $len = count($this->points);
            $this->points[$len] = $this->location2point($wm_walk->end_location, "End", $wm_walk->end_date_time);
        }
        $this->groupInvite = new stdClass(); 
        $this->groupInvite->groupCode = null;       // groups_invited   ??
        $this->isLinear = strtolower($wm_walk->shape) == "linear" ? TRUE : FALSE; 
        $this->url = $wm_walk->url;
    }

    private function map_services($source, $dest )
    {
        $entry = 0;
        if ($source != null)
        {
            $dest->items = array();
            foreach($source as $item)
            {
                $dest->items[$entry] = new stdClass();
                $dest->items[$entry]->text = $source[$entry]->description ;
                $entry++;
            }
        }                       // facilities

    }

    private function location2point($location, $typeString, $tmPoint)
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
        $pointTime = DateTime::createFromFormat(self::WM_TIMEFORMAT, $tmPoint);
        if ($pointTime) { $point->time = $pointTime->format('H:i:s'); }                              
        // finish_date_time
        $point->typeString = $typeString;
        return $point;
    }
}


