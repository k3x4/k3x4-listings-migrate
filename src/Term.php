<?php

namespace k3x4\ListingsMigrate;

class Term{

    public function __construct(){
        add_filter('wp_import_terms', [$this, 'migrateTerms'], 10, 1);
    }

    public function migrateTerms($terms){
        $terms = array_map([$this, 'filterTaxonomies'], $terms);
        return $terms;
    }

    public function filterTaxonomies($item){
        if($term['term_taxonomy']){
            return null;
        }
    }

}