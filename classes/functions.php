<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of functions
 *
 * @author Chris
 */
class Functions {
    const TIMEFORMAT = "Y-m-d\TH:i:s";

    public static function determineUse($default, $usersetting) {
        // Calculate if we should be using this value, check the user supplied value first
        if ($usersetting != null)
        {
            // IF the user has stated to use this then override and ensure you go to the source. 
            $useValid = $usersetting == "0" ? FALSE : TRUE ;
        }
        else {
            // If the value has not been specified, use the value we have defaulted to. 
            $useValid = $default==="1" ? TRUE : FALSE;
        }
        return $useValid ;
    }

    public static function ValidDistance($walk, $distance)
    {
        $isValidDistance = TRUE ;
        if ($distance != null)
        {
            $distanceMiles = (float)$walk->distanceMiles;
            $parts = explode("-", $distance);
            $distanceMin = (float) $parts[0];
            $distanceMax = (float) $parts[1];
            $isValidDistance = ($distanceMiles >= $distanceMin) && ($distanceMiles <= $distanceMax) ? TRUE : FALSE ;
        }
        return $isValidDistance;
    }

    public static function ValidDays($walk, $days)
    {
        $found = TRUE;
        if ($days != null)
        {
            //Ensure the days are all in lowercase
            $validdays = strtolower($days);
            $walkDate = DateTime::createFromFormat(self::TIMEFORMAT, $walk->date);
            $dayofweek = strtolower($walkDate->format('l'));
            $found = (strpos($validdays, $dayofweek) === false) ? FALSE : TRUE ;
        }
        return $found;
    }

    public static function updateCacheFile($walkfile, $walks)
    {
        $json = json_encode($walks);
        $written = file_put_contents($walkfile, $json);
        if ($written === false)
        {
            $failure = "Failed to write inforation to cache";
        }
        return $written;
    }

    public static function getWalkInformation($walkfile, $gwemurl, $wmurl, $gwemproperties, $wmproperties, $useCacheStore, $useGWEM, $useWM)
    {
        if ($useCacheStore)
        {
            if (file_exists($walkfile))
            {
                $walkInfo = new WalksFile($walkfile);
                $walks = $walkInfo->allWalks();
    
            }
            else // Cache File does not exist, so we still need to read from source
            {
                $walks = array();
                if ($useGWEM) {
                    $walks = Functions::getGWEMJsonFeed($gwemurl, $gwemproperties);
                }
                if ($useWM) {
                    $wm_walks = Functions::getWMJsonFeed($wmurl, $wmproperties);
                    $gwem_walks = Functions::convertWMWalks($wm_walks);
                    foreach ($gwem_walks as $walk) { array_push($walks, $walk); }
                    $written = Functions::updateCacheFile("cache/walkmanager.json", $gwem_walks);
                }
                // Write the cache file out first, before returning the data
                $written = Functions::updateCacheFile($walkfile, $walks);
            }    
        }
        else
        {
            $walks = array();
            if ($useGWEM) {
                $walks = Functions::getGWEMJsonFeed($gwemurl, $gwemproperties);
            }
            if ($useWM) {
                $wm_walks = Functions::getWMJsonFeed($wmurl, $wmproperties);
                $gwem_walks = Functions::convertWMWalks($wm_walks);
                foreach ($gwem_walks as $walk) { array_push($walks, $walk); }
                $written = Functions::updateCacheFile("cache/walkmanager.json", $gwem_walks);

            }
        // Write the cache file out first, before returning the data
        $written = Functions::updateCacheFile($walkfile, $walks);
        }

        return $walks;
    }

    public static function convertWMWalks($wm_walks)
    {
        $walks = array();
        foreach($wm_walks as $wm_walk)
        {
            try {
                $gwem_walk = new GwemWalk($wm_walk);
                array_push($walks, $gwem_walk);    
            }
            catch (Error $e)
            {
                // Ensure the error is logged and 
                wm_error($e, $wm_walk, null, false);
                continue;
            }
        }
        // Return the number of walks found
        return $walks;
    }

    public static function wm_log($msg)
    {
        $logfile = $_SERVER['DOCUMENT_ROOT'] . "/wm_log.txt" ;
        // Log the Message but prepend the date and time. 
        error_log(date(SELF::TIMEFORMAT) . " - " . $msg . "\n", 3, $logfile);
    }

    public static function wm_error(Error $e, $wm_walk, $msg, $die = false)
    {
        $logfile = $_SERVER['DOCUMENT_ROOT'] . "/wm_error_log.txt" ;
        if ($wm_walk) {
            // Error was associated to a walk so log the walk details
            error_log("\n\nError Converting Walks Manager Feed (walk id - " . $wm_walk->id . "): " . $e, 3, $logfile);
        }
        else {
            // Error raised was not associated to a walk, log the message provided
            error_log("\n\n" . $msg . "\n" . $e, 3, $logfile);
        }
        if ($die)
        {
            error_log("\n Exiting service as error has requested to die. ", 3, $logfile);
            die();
        }
    }

    public static function getWalkFileName($base, $groupCode)
    {
        $filename = $base ;

        if ($groupCode != null)
        {
            $filename = $filename . '-group-' . $groupCode ;
        }
        $filename = $filename . '.json';

        return $filename;
    }

    public static function ValidateWMURLParameters($urlOpts)
    {
/*
        $urlOpts->date_start = $date_start;
        $urlOpts->date_end = $date_end;
        $urlOpts->groupCode = $groupCode;
        $urlOpts->days = $days;
        $urlOpts->limit = $limit;
*/
        try 
        {
            if ($urlOpts->date_start == null)
            {
                $now = date("Y-m-d");
                $urlOpts->date_start = $now;
            }
            if ($urlOpts->date_end == null)
            {
                // Add 6 months of walks
                $date=date_create($urlOpts->date_start);
                date_add($date,date_interval_create_from_date_string("12 months"));
                $urlOpts->date_end = date_format($date, "Y-m-d");
            }
            // Default to 1000 records
            if ($urlOpts->limit == null) { $urlOpts->limit = 1000; }
            if ($urlOpts->days == null) { $urlOpts->days = "monday,tuesday,wednesday,thursday,friday,saturday,sunday"; }
            // Force to lowercase
            $days = strtolower($days) ;
        }
        catch (Error $e)
        {
            Functions::wm_error($e, null, "Failed to default URL Parameters", true);
        }
            
        return true;
    }

    public static function getGWEMFeedURL($base, $urlOpts)
    {
        $url = $base ;
        if ($urlOpts->ids != null)
        {
            $url = $url . '/' . $urlOpts->ids;
        }
        else
        {
            if ($urlOpts->groupCode != null)
            {
                $url = $url . '?groups=' . $urlOpts->groupCode ;
            }                
        }
        return $url ;
    }

    public static function getWMFeedURL($base, $urlOpts)
    {
        $url = $base ;
        if ($urlOpts->ids != null)
        {
            // walk id's have been specified so we need to return specific walks
            $url = $url . '&ids=' . $urlOpts->ids;
        }
        else
        { // There are no specific id's, so standard searching
            if ($urlOpts->groupCode != null)
            {
                $url = $url . '&groups=' . $urlOpts->groupCode ;
            }
            $url = $url . '&date=' . $urlOpts->date_start  . '&date_end=' . $urlOpts->date_end ;  
            $url = $url . '&limit=' . $urlOpts->limit;
            $url = $url . '&days=' . $urlOpts->days;  
        }
        Functions::wm_log($url);
        return $url ;
    }
    public static function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }

    public static function endsWith($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }

    public static function formatDateDiff($interval) {

        $doPlural = function($nb, $str) {
            return $nb > 1 ? $str . 's' : $str;
        }; // adds plurals

        $format = array();
        if ($interval->y !== 0) {
            $format[] = "%y " . $doPlural($interval->y, "year");
        }
        if ($interval->m !== 0) {
            $format[] = "%m " . $doPlural($interval->m, "month");
        }
        if ($interval->d !== 0) {
            $format[] = "%d " . $doPlural($interval->d, "day");
        }
        if ($interval->h !== 0) {
            $format[] = "%h " . $doPlural($interval->h, "hour");
        }
        if ($interval->i !== 0) {
            $format[] = "%i " . $doPlural($interval->i, "minute");
        }
        if ($interval->s !== 0) {
            if (!count($format)) {
                return "less than a minute ago";
            } else {
                $format[] = "%s " . $doPlural($interval->s, "second");
            }
        }

        // We use the two biggest parts
        if (count($format) > 1) {
            $format = array_shift($format) . " and " . array_shift($format);
        } else {
            $format = array_pop($format);
        }

        // Prepend 'since ' or whatever you like
        return $interval->format($format);
    }

    public static function getExtension($path) {
        $parts = explode(".", $path);
        if (count($parts) == 1) {
            return null;
        }
        return $parts[count($parts) - 1];
    }

    public static function deleteFolder($dir) {
        if (file_exists($dir)) {
            // delete folder and its contents
            foreach (glob($dir . '/*') as $file) {
                if (is_dir($file)) {
                    Functions::deleteFolder($file);
                } else {
                    unlink($file);
                }
            } rmdir($dir);
        }
    }

    public static function errorEmail($feed, $error) {
        require_once 'classes/phpmailer/src/PHPMailer.php';
        require_once 'classes/phpmailer/src/SMTP.php';
        require_once 'classes/phpmailer/src/Exception.php';
        date_default_timezone_set('Europe/London');
        $domain = "theramblers.org.uk";
        // Create a new PHPMailer instance
        $mailer = new PHPMailer\PHPMailer\PHPMailer;

        $mailer->setFrom("admin@" . $domain, $domain);
        $mailer->addAddress(NOTIFY, 'Web Master');
        $mailer->isHTML(true);
        $mailer->Subject = "Ramblers Feed Error";
        $mailer->Body = "<p>Feed error found while running: " . TASK . "</p>".
                "<p>Feed: ".$feed. "</p>"
                . "<p>Error: ". $error . "</p>";
        // $mailer->send();
        echo "Error message sent" . BR;
        echo "Task: " . TASK . BR;
        echo "Feed: " . $feed . BR;
        echo "Error: " . $error . BR;
    }

    public static function checkJsonFileProperties($json, $properties) {
        $errors = 0;
        foreach ($json as $item) {
            $ok = self::checkJsonProperties($item, $properties);
            $errors+=$ok;
        }
        return $errors;
    }

    public static function checkJsonProperties($item, $properties) {
        foreach ($properties as $value) {
            if (!self::checkJsonProperty($item, $value)) {
                return 1;
            }
        }

        return 0;
    }

    private static function checkJsonProperty($item, $property) {
        if (property_exists($item, $property)) {
            return true;
        }
        return false;
    }

    public static function getGWEMJsonFeed($feedurl, $properties) {
        //echo "Feed: " . $feedurl.BR;
        $json = file_get_contents($feedurl);
        if ($json === false) {
            self::errorEmail($feedurl, "Unable to read feed: file_get_contents failed");
            die();
        } else {
            if (!functions::startsWith("$json", "[{") && !functions::startsWith("$json", "{")) {
                self::errorEmail($feedurl, "JSON code does not start with [{ or {");
                die();
            }
        }
        if (functions::startsWith("$json","{"))
            {
                // this is a single value return. Temporarily put in an array;
                $json = "[" . $json . "]";
            }
        //echo "---- Feed read"  .BR;
        $items = json_decode($json);
        if (json_last_error() == JSON_ERROR_NONE) {

            if (functions::checkJsonFileProperties($items, $properties) > 0) {
                self::errorEmail($feedurl, "Expected properties not found in JSON feed");
                die();
            }
        } else {
            self::errorEmail($feedurl, "Error when decoding JSON feed");
            die();
        }
        //echo "---- JSON processed".BR;
        return $items;
    }

    public static function getWMJsonFeed($feedurl, $properties) {
        $json = file_get_contents($feedurl);
        if ($json === false) {
            self::errorEmail($feedurl, "Unable to read feed: file_get_contents failed");
            die();
        } else {
            if (!functions::startsWith("$json", "{")) {
                self::errorEmail($feedurl, "JSON code does not start with {");
                die();
            }
        }
        $items = json_decode($json);
        if (json_last_error() == JSON_ERROR_NONE) {

            if (functions::checkJsonFileProperties($items->data, $properties) > 0) {
                self::errorEmail($feedurl, "Expected properties not found in JSON feed");
                die();
            }
        } else {
            self::errorEmail($feedurl, "Error when decoding JSON feed");
            die();
        }
        return $items->data;
    }

    public static function findSite($sites, $code) {
        foreach ($sites as $site) {
            if ($site->code == $code) {
                return $site;
            }
        }
        return null;
    }

}
