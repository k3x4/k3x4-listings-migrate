<?php

namespace k3x4\ListingsMigrate;

use WP_CLI;

class WpCLI{

    public function __construct(){
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            WP_CLI::add_command( 'import-users', [$this, 'importUsers'] );
        }
    }

    public function importUsers( $args = array(), $assoc_args = array() ) {

        $arguments = wp_parse_args( $assoc_args, array(
            'file' => '',
        ) );
    
        if ( ! empty( $arguments['file'] ) ) {
    
            if(!file_exists($arguments['file'])){
                WP_CLI::error( 'Cannot find file.' );
                return;
            }

            $all_rows = [];
            $file = fopen($arguments['file'], "r");

            $header = fgetcsv($file);
            while ($row = fgetcsv($file)) {
                $all_rows[] = array_combine($header, $row);
            }
            var_dump($all_rows);
    
            WP_CLI::success( 'Updated post title successfully.' );
    
        } else {
            WP_CLI::error( 'Invalid arguments.' );
        }
    }

}