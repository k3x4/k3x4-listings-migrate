<?php

namespace k3x4\ListingsMigrate;

use WP_CLI;

class Post{

    public function __construct(){
        add_filter('wp_import_posts', [$this, 'filterPosts'], 10, 1);
        //add_filter('wp_import_post_data_raw', [$this, 'migratePost'], 10, 1);
        add_filter('wp_import_post_terms', [$this, 'mapCategory'], 10, 3);
        add_filter('wp_import_post_comments', '__return_empty_array', 10, 3);
        add_filter('wp_import_post_meta', [$this, 'mapListingMeta'], 10, 3);
        add_filter('import_post_meta_key', [$this, 'allowedMeta'], 10, 3);

        add_action('import_end', [$this, 'importEndActions']);
    }

    public function filterPosts($posts){
        $filter = [
            'listing',
            'attachment'
        ];
        $posts = array_filter($posts, function($post) use ($filter){
            return in_array($post['post_type'], $filter);
        });

        return $posts;
    }

    public function migratePost($post){
        return $post;
    }

    public function mapCategory($terms, $post_id, $post){
        if(!$terms){
            return $terms;
        }

        $this->importFeatures($terms, $post_id);

        $filter = [
            'pointfinderltypes',
        ];
        $terms = array_filter($terms, function($term) use ($filter){
            return in_array($term['domain'], $filter);
        });
        $terms = array_values($terms);

        $map = [
            'pointfinderltypes' => 'listing_category'
        ];

        // MAP LISTING CATEGORY
        array_walk($terms, function(&$item, $key) use ($map){
            if($key == 'domain' && $item){
                $domain = $item['domain'];
                $item['domain'] = $map[$domain];
            }
        });

        return $terms;
    }

    public function importFeatures($terms, $post_id){
        $filter = [
            'pointfinderfeatures',
        ];
        $terms = array_filter($terms, function($term) use ($filter){
            return in_array($term['domain'], $filter);
        });
        $terms = array_values($terms);

        if(count($terms)){
            foreach($terms as $term){
                add_post_meta($post_id, 'feature_' . $term['slug'], 'on');
            }
        }
    }

    public function mapListingMeta($postmeta, $post_id, $post){
        $this->mapFeatures($postmeta, $post_id, $post);
        $this->mapCustomFields($postmeta, $post_id, $post);
        $this->mapGalleryImages($postmeta, $post_id, $post);
        $this->mapViews($postmeta, $post_id, $post);
        $this->mapCustomBoxes($postmeta, $post_id, $post);

        return $postmeta;
    }

    public function mapFeatures($postmeta, $post_id, $post){
        foreach($postmeta as $meta){
            $metaKey = $meta['key'];
            if(isset($oldFields[$metaKey])){
                add_post_meta($post_id, $oldFields[$metaKey], $meta['value']);
            }
        }
    }

    public function mapCustomFields($postmeta, $post_id, $post){
        $oldFields = get_option('old_fields_migrate');
        foreach($postmeta as $meta){
            $metaKey = $meta['key'];
            if(isset($oldFields[$metaKey])){
                add_post_meta($post_id, $oldFields[$metaKey], $meta['value']);
            }
        }
    }

    public function mapGalleryImages($postmeta, $post_id, $post){
        $images = [];
        foreach($postmeta as $meta){
            if($meta['key'] == 'webbupointfinder_item_images'){
                $image_attributes = wp_get_attachment_image_src($meta['value']);
                if($image_attributes){
                    $images[$meta['value']] = current($image_attributes);
                }
            }
        }
        if(!empty($images)){
            $images = array_reverse($images, true);
            add_post_meta($post_id, 'listing-gallery-images', $images);
        }
    }

    public function mapViews($postmeta, $post_id, $post){
        foreach($postmeta as $meta){
            if($meta['key'] == 'views'){
                add_post_meta($post_id, 'listing-views', $meta['value']);
                break;
            }
        }
    }

    public function mapCustomBoxes($postmeta, $post_id, $post){
        $customBoxes = [
            'webbupointfinder_item_custombox1',
            'webbupointfinder_item_custombox2',
            'webbupointfinder_item_custombox3',
            'webbupointfinder_item_custombox4',
        ];
        foreach($postmeta as $meta){
            if(in_array($meta['key'], $customBoxes)){
                $parts = explode('_', $meta['key']);
                add_post_meta($post_id, 'listing-' . end($parts), $meta['value']);
                break;
            }
        }
    }

    public function allowedMeta($meta_key, $post_id, $post){
        $filter = [
            '_edit_last',
            '_thumbnail_id'
        ];

        if(in_array($meta_key, $filter)){
            return $meta_key;
        }

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

    public function importEndActions(){
        $users = new User();
        $users->changeUsersLogin();

        delete_option('old_fields_migrate');
    }

}