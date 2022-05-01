<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('assert.warning', 1);
ini_set('default_socket_timeout', 120);

define("VERSION_NUMBER", "0.0.2");
define("WALKFILE", "cache/allwalks");       // Cache file, name to be updated with groups
define("NOTIFY", "webmaster@wiltsswindonramblers.org.uk");
define("GWEMFEED", "https://www.ramblers.org.uk/api/lbs/walks");
//define("WALKMANAGER", "https://virtserver.swaggerhub.com/abateman/Ramblers-third-parties/1.0.0/api/volunteers/walksevents");
//define("WALKMANAGER", "https://uat-be.ramblers.nomensa.xyz/api/volunteers/walksevents?types=group-walk&status=confirmed,cancelled");
define("WALKMANAGER", "https://virtserver.swaggerhub.com/abateman/Ramblers-third-parties/1.0.0/api/volunteers/walksevents?types=group-walk&status=confirmed,cancelled");

//define("RAMBLERSWEBSSITES", "https://sites.ramblers-webs.org.uk/feed.php");
define("BR", "<br>");
define("USECACHE", "0");    // Default option as to whether to use a cache store
define("USEGWEM", "0");     // Default option as to whether to search GWEM for details
define("USEWM", "1");       // Default option as to whether to search Walks Manager for details

// 	First Release
if (version_compare(PHP_VERSION, '7.0.0') < 0) {
    echo 'You MUST be running on PHP version 7.0.0 or higher, running version: ' . \PHP_VERSION . BR;
    die();
}
// set current directory to current run directory
$exepath = dirname(__FILE__);
define('BASE_PATH', dirname(realpath(dirname(__FILE__))));
chdir($exepath);
$key = NULL;

require('classes/autoload.php');
spl_autoload_register('autoload');

//Get Command line parameters
$opts = new Options();
$groupCode = $opts->gets("groups") ;
$days = $opts->gets("days");
$distance = $opts->gets("distance");
$limit = $opts->gets("limit"); 
$dow = $opts->gets("dow"); // Day of Week Function does not work on original GWEM Feed
$useCache = $opts->gets("usecache");
$useGWEM = $opts->gets("usegwem");
$useWM = $opts->gets("usewm");
$ids = $opts->gets("ids");


//Only return walks for the Group
$gwemurl = GWEMFEED ;
$wmurl = WALKMANAGER ;
$walkfile = Functions::getWalkFileName(WALKFILE, $groupCode);
$gwemurl = Functions::getGWEMFeedURL(GWEMFEED, $groupCode, $ids);
$wmurl = Functions::getWMFeedURL(WALKMANAGER, $groupCode, $ids);

// Define the properties to validate against the feed
$gwemproperties = array("id", "description", "walkLeader", "groupCode" );
$wmproperties = array("id", "description", "group_code" );

// Determine the use of the cache
$useCacheStore = Functions::determineUse(USECACHE, $useCache);
$useGWEM = Functions::determineUse(USEGWEM, $useGWEM);
$useWM = Functions::determineUse(USEWM, $useWM);

// Now you need to ensure you can convert the source into a matching GWEM Feed
$walks = Functions::getWalkInformation($walkfile, $gwemurl, $wmurl, $gwemproperties, $wmproperties, $useCacheStore, $useGWEM, $useWM);

// Filter the information based on the details provided
foreach ($walks as $i=>$walk)
{
    // Validate the correct date
    if (!Functions::ValidDays($walks[$i], $days) ||
        !Functions::ValidDistance($walks[$i], $distance)) {
        unset($walks[$i]);
    }
}

// For the remaining items, Sort into date order
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


// Encode the information as JSON feed
$json = json_encode($walks);

header("Access-Control-Allow-Origin: *");
header("Content-type: application/json");
echo $json ;
return ;
