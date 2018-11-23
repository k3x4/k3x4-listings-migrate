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
            }

            $importArray = Tools::csvToArray($arguments['file']);
            $user = new User();
            $user->importUsers($importArray);
    
            //WP_CLI::success( 'Updated post title successfully.' );
        } else {
            WP_CLI::error( 'Invalid arguments.' );
        }
    }

}