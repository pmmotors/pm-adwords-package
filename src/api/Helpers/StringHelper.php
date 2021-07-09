<?php

namespace PmAnalyticsPackage\api\Helpers;

use ForceUTF8\Encoding;

class StringHelper
{
    /**
     * To remove special chars
     */
    public static function normalizeString($string)
    {
        $string = preg_replace("/(™|®|©|&trade;|&reg;|&copy;|&#8482;|&#174;|&#169;|\\t|\\n)/", "", $string);
        $table = array(
            'Š' => 'S', 'š' => 's', 'Đ' => 'Dj', 'đ' => 'dj', 'Ž' => 'Z', 'ž' => 'z', 'Č' => 'C', 'č' => 'c', 'Ć' => 'C', 'ć' => 'c',
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
            'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O',
            'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e',
            'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o',
            'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'ý' => 'y', 'þ' => 'b',
            'ÿ' => 'y', 'Ŕ' => 'R', 'ŕ' => 'r',
        );

        $string =  strtr($string, $table);
        return Encoding::fixUTF8($string);
    }

    /**
     * @param haystack String to search in
     * @param needle String to search
     */
    public static function contains($haystack, $needle)
    {
        return strpos(strval($haystack), strval($needle)) !== false;
    }

    public static function csvStringToArray($csvString, $delimiter = ",")
    {
        //PARSE AND INSERT CSV ROWS
        $lines = explode(PHP_EOL, $csvString);
        $array = [];
        $header = str_getcsv($lines[0], $delimiter);
        unset($lines[0]); //removing header from csv

        foreach ($lines as $index => $line) {
            $newRow = array_combine($header, str_getcsv($line, $delimiter));

            if ($newRow) {
                $array[] = $newRow;
            }
            // if ($index > 15 ) break; //for dev speed
        }

        return $array;
    }
}
