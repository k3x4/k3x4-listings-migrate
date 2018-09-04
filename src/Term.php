<?php

namespace k3x4\ListingsMigrate;

use WP_CLI;

class Term{

    public function __construct(){
        add_filter('wp_import_terms', [$this, 'migrateTerms'], 10, 1);
        add_filter('wp_import_term_meta', '__return_empty_array', 10, 1);
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
                    $this->insertTerm( $term['term_name'], 'listing_category', [
                        'parent' => $parent,
                        'slug'   => $term['slug']
                    ]);
                    break;
            endswitch;
        }
        delete_option('listing_category_children');

        $this->mapCustomFields();

        return null;
    }

    public function mapCustomFields(){
        $customFields = [
            'Τύπος'         => [
                'oldField'  => 'webbupointfinder_item_field_proptype',
                'type'      => 'select',
                'options'   => [
                    'values' => [
                        '1' => 'Studio / Γκαρσονιέρα',
                        '2' => 'Διαμέρισμα',
                        '3' => 'Μεζονέτα',
                        '4' => 'Μονοκατοικία',
                    ],
                    'label' => 'Τύπος',
                    'default' => '1'
                ]
            ],
            'Τιμή'          => [
                'oldField'  => 'webbupointfinder_item_field283070149872418420000',
                'type'      => 'number',
                'options'   => [
                    'label' => 'Τιμή',
                    'placeholder' => 'Τιμή',
                    'prefix' => null,
                    'suffix' => '€',
                    'default' => null
                ]
            ],
            'Όροφος'        => [
                'oldField'  => 'webbupointfinder_item_field70165663575622040000',
                'type'      => 'select',
                'options'   => [
                    'values' => [
                        '1' => 'Υπόγειο',
                        '2' => 'Ημιυπόγειο',
                        '3' => 'Ισόγειο',
                        '4' => 'Ημιώροφος',
                        '5' => '1ος',
                        '6' => '2ος',
                        '7' => '3ος',
                        '8' => '4ος',
                        '9' => '5ος',
                        '10' => '6ος',
                        '11' => '7ος',
                        '12' => '8ος',
                        '13' => '9ος',
                        '14' => '10ος',
                    ],
                    'label' => 'Όροφος',
                    'default' => null
                ]
            ],
            'Τετρ. Μέτρα'   => [
                'oldField'  => 'webbupointfinder_item_field287084981110235630000',
                'type'      => 'number',
                'options'   => [
                    'label' => 'Τετρ. Μέτρα',
                    'placeholder' => 'Τετρ. Μέτρα',
                    'prefix' => null,
                    'suffix' => 'τμ',
                    'default' => null
                ]
            ],
            'Δωμάτια'       => [
                'oldField'  => 'webbupointfinder_item_field930250379806436500000',
                'type'      => 'number',
                'options'   => [
                    'label' => 'Δωμάτια',
                    'placeholder' => 'Δωμάτια',
                    'prefix' => null,
                    'suffix' => null,
                    'default' => null
                ]
            ],
            'Θέρμανση'      => [
                'oldField'  => 'webbupointfinder_item_field377217164104565400000',
                'type'      => 'select',
                'options'   => [
                    'values' => [
                        '1' => 'Χωρίς Θέρμανση',
                        '2' => 'Με κλιματιστικά',
                        '3' => 'Αυτόνομη θέρμανση',
                        '4' => 'Κεντρική θέρμανση',
                        '5' => 'Θέρμανση με χρήση υγραερίου',
                        '6' => 'Σόμπα',
                        '7' => 'Θερμοσυσσωρευτής',
                    ],
                    'label' => 'Θέρμανση',
                    'default' => '1'
                ]
            ],
            'Διεύθυνση'     => [
                'oldField'  => 'webbupointfinder_item_field454305059116910000000',
                'type'      => 'text',
                'options'   => [
                    'label' => 'Διεύθυνση',
                    'prefix' => null,
                    'suffix' => null,
                    'placeholder' => 'Διεύθυνση',
                    'default' => null,
                ]
            ]
        ];

        $fieldGroupId = $this->insertTerm('Ακίνητο', 'field_group');

        $attachCategories = [
            'Ενοικίαση γραφείου',
            'Ενοικίαση Επαγγελματικού Χώρου',
            'Ενοικίαση Κατοικίας',
            'Πώληση Επαγγελματικού Χώρου',
            'Πώληση Κατοικίας'
        ];
        
        $categories = get_terms([
            'taxonomy' => 'listing_category',
            'name' => $attachCategories,
            'hide_empty' => false,
        ]);
        
        $categories = array_map(function($item){
            return $item->slug;
        }, $categories);

        add_term_meta($fieldGroupId, 'field_group_categories', $categories);

        foreach($customFields as $fieldTitle => $fieldArray){
            //$groupSlug = 
            $fieldId = $groupSlug . '_' . $fieldSlug;
            $meta_input = [
                'field_type' => $fieldArray['type'],
                'field_id' => $fieldId,
                'field_label' => $fieldArray['options']['label'],
                'field_default' => $fieldArray['options']['default']
            ];
            if(isset($fieldArray['options']['values'])){
                $options = '';
                foreach($fieldArray['options']['values'] as $key => $value){
                    $options .= $key . '|' . $value . PHP_EOL;
                }
                $meta_input['field_options'] = $options;
            }
            $fieldId = wp_insert_post([
                'post_title'    => $fieldTitle,
                'post_status'   => 'publish',
                'post_type'     => 'field',
                'post_author'   => 1,
                'meta_input' => $meta_input
            ]);
            wp_set_object_terms($fieldId, $fieldGroupId, 'field_group');
        }


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