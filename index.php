<?php

error_reporting(E_ERROR);
ini_set('display_errors', 1);
ini_set('assert.warning', 1);
ini_set('default_socket_timeout', 120);

define("SITE_ROOT","http://gwem.theramblers.org.uk");
define("VERSION_NUMBER", "0.0.2");
define("WALKFILE", "cache/allwalks");       // Cache file, name to be updated with groups
define("NOTIFY", "webmaster@wiltsswindonramblers.org.uk");
define("GWEMFEED", "https://www.ramblers.org.uk/api/lbs/walks");
// Test Site Definitinon Below
//define("WALKMANAGER", "https://uat-be.ramblers.nomensa.xyz/api/volunteers/walksevents?types=group-walk");

// Live Site Definition Below
define("WALKMANAGER", "https://walks-manager.ramblers.org.uk/api/volunteers/walksevents?");

// API Key for the active site (UAT or Live)
define("APIKEY", "853aa876db0a37ff0e6780db2d2addee");

//define("RAMBLERSWEBSSITES", "https://sites.ramblers-webs.org.uk/feed.php");
// Default to using Walks Manager
define("BR", "<br>");
define("USECACHE", "0");    // Default option as to whether to use a cache store
define("USEGWEM", "0");     // Default option as to whether to search GWEM for details
define("USEWM", "1");       // Default option as to whether to search Walks Manager for details
define("INCWALKS", "1");       // Default option as to whether to search for Walk information
define("INCEVENTS", "1");       // Default option as to whether to search for event information

// 	First Release
if (version_compare(PHP_VERSION, '7.4.0') < 0) {
    echo 'You MUST be running on PHP version 7.4.0 or higher, running version: ' . \PHP_VERSION . BR;
    die();
}
// set current directory to current run directory
$exepath = dirname(__FILE__);
define('BASE_PATH', dirname(realpath(dirname(__FILE__))));
chdir($exepath);
$key = NULL;

require('classes/autoload.php');
spl_autoload_register('autoload');

//try {
//    Functions::wm_log('HTTP_REFERER : ' . $_SERVER["HTTP_REFERER"] . ', REMOTE_ADDR : ' . $_SERVER["REMOTE_ADDR"] . ', REMOTE_HOST : ' . $_SERVER["REMOTE_HOST"] );
//} catch (Error $e)
//{
//    Functions::wm_log('*** Unable to obtain Server Variables ****');
//}

//Get Command line parameters
try{
    $opts = new Options();
    $groupCode = strtoupper($opts->gets("groups")) ;
    if (trim($groupCode) == "") { $groupCode = null; }
    $days = $opts->gets("days");
    $distance = $opts->gets("distance");
    $limit = $opts->gets("limit"); 
    $dow = $opts->gets("dow"); // Day of Week Function does not work on original GWEM Feed
    $useCache = $opts->gets("usecache");
    $useGWEM = $opts->gets("usegwem");
    $useWM = $opts->gets("usewm");
    $ids = $opts->gets("ids");
    $date_start = $opts->gets("date_start");
    $date_end = $opts->gets("date_end");
    $include_walks = $opts->gets("incwalks");
    $include_events = $opts->gets("incevents");
    
}
catch (Error $e)
{
    Functions::wm_error($e, null,"Failed to parse the URL Parameters : " . $_GET , true);
}

$block = false; 
// Validate to see if we should process the group.
if ($groupCode != null) // There is something to validate
{
    $excludedGroups = array("ZZZ");

    foreach ($excludedGroups as $excludedGroup)
    {
        if ($groupCode == $excludedGroup)
        {
            $block = true;
        }
    }
}

if (!$block)
{
    // Log the request
    Functions::wm_log(SITE_ROOT . $_SERVER["REQUEST_URI"]);

    //Only return walks for the Group
    try {
        $urlOpts = new StdClass();
        $urlOpts->date_start = $date_start;
        $urlOpts->date_end = $date_end;
        $urlOpts->groupCode = $groupCode;
        $urlOpts->days = $days;
        $urlOpts->limit = $limit;
        $urlOpts->ids = $ids;
        $urlOpts->include_walks = Functions::determineUse(INCWALKS,$include_walks);
        $urlOpts->include_events = Functions::determineUse(INCEVENTS,$include_events);

        // Ensure the URL Parameters are valid.
        Functions::ValidateWMURLParameters($urlOpts);

        $gwemurl = GWEMFEED ;
        $wmurl = WALKMANAGER ;
        $walkfile = Functions::getWalkFileName(WALKFILE, $groupCode);
        $gwemurl = Functions::getGWEMFeedURL(GWEMFEED, $urlOpts);
        $wmurl = Functions::getWMFeedURL(WALKMANAGER, APIKEY, $urlOpts);
        
    }
    catch (Error $e)
    {
        Functions::wm_error($e, null, "Failed to calculate correct URL's", true);
    }

    // Define the properties to validate against the feed
    $gwemproperties = array("id", "description", "walkLeader", "groupCode" );
    $wmproperties = array("id", "description", "group_code" );

    // Determine the use of the cache
    $useCacheStore = Functions::determineUse(USECACHE, $useCache);
    $useGWEM = Functions::determineUse(USEGWEM, $useGWEM);
    $useWM = Functions::determineUse(USEWM, $useWM);

    // Now you need to ensure you can convert the source into a matching GWEM Feed
    try {
        $walks = Functions::getWalkInformation($walkfile, $gwemurl, $wmurl, $gwemproperties, $wmproperties, $useCacheStore, $useGWEM, $useWM);
    }
    catch (Error $e)
    {
        Functions::wm_error($e, null, "Failed to complete getWalkInformation successfully", true);
    }

    // Filter the information based on the details provided
    try {
        foreach ($walks as $i=>$walk)
        {
            // Validate the correct date
            if (!Functions::ValidDays($walks[$i], $days) ||
                !Functions::ValidDistance($walks[$i], $distance)) {
                unset($walks[$i]);
            }
        }
    }
    catch (Error $e)
    {
        Functions::wm_error($e, null, "Failed to validate Days and Distance correctly successfully", true);
    }

    // For the remaining items, Sort into date order
    try {
        usort($walks, "WalksFile::cmpDates");
        if ($limit != null)
        {
            // Get the upper limit of the array
            $upperlimit = (int) $limit;
            if ($upperlimit > 0) {
                for ($i = count($walks)-1; $i >= $upperlimit; $i--) {
                    unset($walks[$i]);
                }    
            }
        }
    }
    catch (Error $e)
    {
        Functions::wm_error($e, null, "Failed to remove walks beyond limit successfully", true);
    }
    $json = json_encode($walks);
}
else{
    Functions::wm_log("----- Blocked Request for " . $_SERVER["REQUEST_URI"]);

    $json = "[{\"error\":\"This request has been blocked by the server " . $groupCode . "\"}]";
}
// Encode the information as JSON feed

header("Access-Control-Allow-Origin: *");
header("Content-type: application/json");
echo $json ;
return ;
