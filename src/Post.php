<?php

namespace k3x4\ListingsMigrate;

use WP_CLI;

class Post{

    public function __construct(){
        add_filter('wp_import_posts', [$this, 'filterPosts'], 10, 1);
        //add_filter('wp_import_post_data_raw', [$this, 'migratePost'], 10, 1);
        add_filter('wp_import_post_terms', [$this, 'mapCategory'], 10, 3);
        add_filter('wp_import_post_comments', '__return_empty_array', 10, 3);
        //add_filter('wp_import_post_meta', [$this, 'mapListingMeta'], 10, 3);
        add_filter('import_post_meta_key', [$this, 'allowedMeta'], 10, 3);

        $users = new User();
        add_action('import_end', [$users, 'changeUsersLogin']);
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

        $filter = [
            'pointfinderltypes',
        ];
        $terms = array_filter($terms, function($term) use ($filter){
            return in_array($term['domain'], $filter);
        });

        $map = [
            'pointfinderltypes' => 'listing_category'
        ];

        //var_dump($terms);exit();

        array_walk($terms, function(&$item, $key) use ($map){
            if($key == 'domain' && $item){
                $domain = $item['domain'];
                $item['domain'] = $map[$domain];
            }
        });

        return $terms;
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

}