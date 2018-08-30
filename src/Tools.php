<?php

namespace k3x4\ListingsMigrate;

class Tools{

    public static function csvToArray($file){
        $all_rows = [];
        $file = fopen($file, "r");

        $header = fgetcsv($file);
        while ($row = fgetcsv($file)) {
            $all_rows[] = array_combine($header, $row);
        }

        return $all_rows;
    }

}