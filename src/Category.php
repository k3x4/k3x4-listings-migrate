<?php

namespace k3x4\ListingsMigrate;

class Category{

    public function __construct(){
        add_filter('wp_import_categories', '__return_null', 10, 1);
    }

}