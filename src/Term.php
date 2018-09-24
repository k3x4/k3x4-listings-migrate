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
                        'post_name'     => $term['slug'],
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

        $this->attachGroupToCategories($houseId, 'feature_group_categories', [
            'Ενοικίαση γραφείου',
            'Ενοικίαση Επαγγελματικού Χώρου',
            'Ενοικίαση Κατοικίας',
            'Πώληση Επαγγελματικού Χώρου',
            'Πώληση Κατοικίας'
        ]);

        $this->attachGroupToCategories($autoId, 'feature_group_categories', [
            'Αυτοκίνητα',
            'Επαγγελματικά',
        ]);

        $this->mapCustomFields();

        return null;
    }

    public function mapCustomFields(){
        $customFields = [
            'Τύπος'         => [
                'oldField'  => 'webbupointfinder_item_field_proptype',
                'type'      => 'select',
                'id'        => 'real-estate_type',
                'icon'      => 'fa fa-home',
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
                'id'        => 'real-estate_price',
                'icon'      => 'fa fa-eur',
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
                'id'        => 'real-estate_floor',
                'icon'      => 'fa fa-sort',
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
                'id'        => 'real-estate_size',
                'icon'      => 'fa fa-arrows-alt',
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
                'id'        => 'real-estate_rooms',
                'icon'      => 'fa fa-building-o',
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
                'id'        => 'real-estate_heating',
                'icon'      => 'fa fa-fire',
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
                'id'        => 'real-estate_address',
                'icon'      => 'fa fa-map-marker',
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

        $this->attachGroupToCategories($fieldGroupId, 'field_group_categories', [
            'Ενοικίαση γραφείου',
            'Ενοικίαση Επαγγελματικού Χώρου',
            'Ενοικίαση Κατοικίας',
            'Πώληση Επαγγελματικού Χώρου',
            'Πώληση Κατοικίας'
        ]);

        $mapOldFields = [];
        foreach($customFields as $fieldTitle => $fieldArray){
            $meta_input = [
                'field_type' => $fieldArray['type'],
                'field_id' => $fieldArray['id'],
                'field_icon' => $fieldArray['icon'],
            ];
            if(isset($fieldArray['options']['values'])){
                $options = [];
                foreach($fieldArray['options']['values'] as $key => $value){
                    $options[] = $key . '|' . $value;
                }
                $meta_input['field_options'] = implode(PHP_EOL, $options);
            }
            $meta_input = $this->optionsFieldExists($fieldArray, 'label', $meta_input);
            $meta_input = $this->optionsFieldExists($fieldArray, 'default', $meta_input);
            $meta_input = $this->optionsFieldExists($fieldArray, 'placeholder', $meta_input);
            $meta_input = $this->optionsFieldExists($fieldArray, 'prefix', $meta_input);
            $meta_input = $this->optionsFieldExists($fieldArray, 'suffix', $meta_input);

            $fieldId = wp_insert_post([
                'post_title'    => $fieldTitle,
                'post_status'   => 'publish',
                'post_type'     => 'field',
                'post_author'   => 1,
                'meta_input' => $meta_input
            ]);
            wp_set_object_terms($fieldId, $fieldGroupId, 'field_group');

            $mapOldFields[$fieldArray['oldField']] = $fieldArray['id'];
        }

        add_option('old_fields_migrate', $mapOldFields);
    }

    public function attachGroupToCategories($fieldGroupId, $metaKey, $attachCategories){
        $categories = get_terms([
            'taxonomy' => 'listing_category',
            'name' => $attachCategories,
            'hide_empty' => false,
        ]);
        
        $categories = array_map(function($item){
            return $item->slug;
        }, $categories);

        add_term_meta($fieldGroupId, $metaKey, $categories);
    }

    public function optionsFieldExists($options, $name, $result){
        if(isset($options['options'][$name]) && $options['options'][$name]){
            $result['field_' . $name] = $options['options'][$name];
        }
        return $result;
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