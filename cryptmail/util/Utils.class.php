<?php
namespace cryptmail\util;

/**
 * Created on 09.08.2012
 * @author Tobias Marstaller 
 */

abstract class Utils
{
    
    const DATE_PHPSTD = -13242;
    const DATE_MYSQLSTD = 1352512;
    const ASCENDING = -12512;
    const DESCENDING = 241241;
    
    /*
        Datumsformat:
        D - Tag    S: Sekunde      f: Stundenformat (am oder pm)
        M - Monat  m: Minute
        Y - Jahr   H: Stunde
    */
    public static function dateFormatToRegex($format, $formatTo = Utils::DATE_PHPSTD) {
        $pos = array("D" => -1, "M" => -1, "Y" => -1, "S" => -1, "m" => -1, "H" => -1,
            "f" => -1);
        $delimeter = array("!", "/", "#", "`", "&", "§");
        $format = trim($format);
        $j = strlen($format);
        $regex_search = "^";
        $pointer = 1;
        for ($i = 0;$i < $j;$i++)
        {
            switch ($format[$i])
            {
                case "D": // Tage
                    $regex_search .= "([0-2]?\d|3[0|1])";
                    $pos["D"] = $pointer++;
                break;
                case "M": // Monate
                    $regex_search .= "(\d|1[0-2])";
                    $pos["M"] = $pointer++;
                break;
                case "Y": // Jahre
                    $regex_search .= "(\d{4})";
                    $pos["Y"] = $pointer++;
                break;
                case "S": // Sekunden
                    $regex_search .= "([0-5]?\d)";
                    $pos["S"] = $pointer++;
                break;
                case "m": // Minuten
                    $regex_search .= "([0-5]?\d)";
                    $pos["m"] = $pointer++;
                break;
                case "H": // Stunden
                    $regex_search .= "([0|1]?\d|2[0-4])";
                    $pos["H"] = $pointer++;
                break;
                case "f": // Stunden im 12H Format
                    $regex_search .= "(am|pm)";
                    $pos["f"] = $pointer++;
                break;
                case " ": case "\t": case "\n":
                    $regex_search .= "\s";
                break;
                default:
                    $_pos = array_search($format[$i], $delimeter);
                    if ($_pos !== FALSE) {
                        unset($delimeter[$_pos]);
                        $delimeter = array_merge(array(), $delimeter);
                    }
                    $regex_search .= "\\" . $format[$i];
                break;
            }
        }
        if (count($delimeter) == 0)
        {
            throw new \Exception("The dateformat contains too much different ".
            "special chars; no more possible regex delimeters left.");
        }
        $regex_search = $delimeter[0] . $regex_search . "$" . $delimeter[0];
        $regex_replace = '';
        switch ($formatTo)
        {
            case Utils::DATE_PHPSTD:
            default:
                $regex_replace = '$' . $pos["M"] . '/$' . $pos["D"]. "/";
                if ($pos["Y"] == -1)
                {
                    $regex_replace .= date("Y");
                }
                else
                {
                    $regex_replace .= '$' . $pos["Y"];
                }
                $regex_replace .= ' $'.$pos["H"].':$'.$pos["m"];
                if ($pos["S"] != -1)
                {
                    $regex_replace .= ':$' . $pos["S"];
                }
                if ($pos["f"] != -1)
                {
                    $regex_replace .= '$' . $pos["f"];
                }
            break;
            case Utils::DATE_MYSQLSTD:
                if ($pos["Y"] == -1)
                {
                    $regex_replace .= date("Y");
                }
                else
                {
                    $regex_replace .= $pos["Y"];
                }
                $regex_replace = '-$' . $pos["M"] . '-$' . $pos["D"];
            break;
        }
        return array($regex_search, $regex_replace);
    }
    
    /**
     * @author Tobias Marstaller
     * @return void
     * @param int[mixed] array The array to be sorted (pass-by-reference)
     * @throws \cryptmail\general\ArgumentException
     * Sorts $array ascending and preserves the array-keys (unlike sort()) 
     * This method uses uasort()
     */
    public static function arraySort(array &$array, $order = Utils::ASCENDING)
    {
        uasort($array, function($a, $b) {
            if ($a == $b)
			{
                return 0;
            }
            return ($a < $b) ? -1 : 1;
        });
        if ($order == Utils::DESCENDING)
        {
            $array = array_reverse($array, true);
        }
    }
}  
?>
