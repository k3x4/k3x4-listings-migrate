<?php

namespace k3x4\ListingsMigrate;

class Tag{

    public function __construct(){
        add_filter('wp_import_tags', '__return_empty_array', 10, 1);
    }

}