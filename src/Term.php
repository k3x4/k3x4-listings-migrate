<?php

namespace k3x4\ListingsMigrate;

use WP_CLI;

class Term{

    public function __construct(){
        add_filter('wp_import_terms', [$this, 'migrateTerms'], 10, 1);
        add_filter('wp_import_term_meta', '__return_null', 10, 1);
    }

    public function migrateTerms($terms){
        $houseId = $this->insertTerm('Ακίνητο', 'feature_group');
        $autoId = $this->insertTerm('Αυτοκίνητο', 'feature_group');

        $featureMapGroup = [
            'ABS'                                   => $autoId,
            'Air Condition'                         => $autoId,
            'Air Condition'                         => $houseId,
            'Bluetooth'                             => $autoId,
            'CD Player'                             => $autoId,
            'DVD Player'                            => $autoId,
            'ESP'                                   => $autoId,
            'Immobilizer'                           => $autoId,
            'Ανεμιστήρας οροφής'                    => $houseId,
            'Αποθήκη'                               => $houseId,
            'Αυτόματος κλιματισμός'                 => $autoId,
            'Βεράντα'                               => $houseId,
            'Βιβλίο service'                        => $autoId,
            'Διπλά τζάμια'                          => $houseId,
            'Ενεργή Σύνδεση Ηλεκτρικού Ρεύματος'    => $houseId,
            'Ζάντες αλουμινίου'                     => $autoId,
            'Ηλεκτρικοί καθρέπτες'                  => $autoId,
            'Θέρμανση'                              => $houseId,
            'Κεντρικό κλείδωμα'                     => $autoId,
            'Μπάρμπεκιου'                           => $houseId,
            'Πάρκινγκ'                              => $houseId,
            'Πισίνα'                                => $houseId,
            'Πλοηγός'                               => $autoId,
            'Πόρτα ασφαλείας'                       => $houseId,
            'Προβολείς ομίχλης'                     => $autoId,
            'Σοφίτα'                                => $houseId,
            'Τζάκι'                                 => $houseId,
            'Υδραυλικό τιμόνι'                      => $autoId,
            'Υποβοήθηση φρένων'                     => $autoId,
        ];
        
        foreach($terms as $term){
            switch($term['term_taxonomy']):
                case 'pointfinderfeatures':
                    $postId = wp_insert_post([
                        'post_title'    => $term['term_name'],
                        'post_status'   => 'publish',
                        'post_type'     => 'feature',
                        'post_author'   => 1
                    ]);
                    if (!is_wp_error($postId)) {
                        wp_set_object_terms($postId, $featureMapGroup[ $term['term_name'] ], 'feature_group');
                    }
                    break;
                case 'pointfinderltypes':
                    if ( empty( $term['term_parent'] ) ) {
                        $parent = 0;
                    } else {
                        $parent = term_exists( $term['term_parent'], 'listing_category' );
                        if ( is_array( $parent ) ) $parent = $parent['term_id'];
                    }
                    $aaa = $this->insertTerm( $term['term_name'], 'listing_category', [
                        'parent' => $parent,
                        'slug'   => $term['slug']
                    ]);
                    break;
            endswitch;
        }
        
        delete_option('listing_category_children');
        return null;
    }

    public function insertTerm($term, $taxonomy, $args = []){
        $newTerm = wp_insert_term($term, $taxonomy, $args);

        if (is_wp_error($newTerm)) {
            WP_CLI::warning("Error term created (" . $term . "): " . $newTerm->get_error_message() . PHP_EOL);
            return null;
        }

        return $newTerm['term_id'];
    }

}