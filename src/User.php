<?php

namespace k3x4\ListingsMigrate;

use WP_CLI;

class User
{

    public function importUsers($users)
    {
        $users = $this->sortImportArrayById($users);
        add_filter('sanitize_user', [$this, 'nonStrictLogin'], 10, 3);
        $this->importUsersToDatabase($users);
        add_action( 'after_setup_theme', function() {
            add_action('import_end', [$this, 'changeUsersLogin']);
        } );
    }

    public function nonStrictLogin($username, $raw_username, $strict) {
        if( !$strict )
            return $username;

        return sanitize_user(stripslashes($raw_username), false);
    }

    public function sortImportArrayById($array)
    {
        usort($array, function ($a, $b) {
            $d1 = intval($a['id']);
            $d2 = intval($b['id']);
            if ($d1 == $d2) {
                return 0;
            }
            return ($d1 < $d2) ? -1 : 1;
        });

        return $array;
    }

    public function importUsersToDatabase($users)
    {
        $count = 0;
        $this->userPasswordsMap = [];

        foreach ($users as $user) {
            // array_walk($user, function(&$item, $key){
            //     $item = mb_convert_encoding($item, 'UTF-8', mb_detect_encoding($item, 'UTF-8', true));
            // });

            $userdata = [
                'user_login' => $user['user_login'],
                'user_pass' => '12345678',
                'user_email' => $user['user_email'],
                'display_name' => $user['user_email'],
            ];

            $user_id = wp_insert_user($userdata);

            if (is_wp_error($user_id)) {
                WP_CLI::warning("Error user created (" . $user['user_login'] . "): " . $user_id->get_error_message() . PHP_EOL);
                continue;
            }

            $userdata = [
                'ID' => $user_id,
                'user_login' => $user['user_login'],
                'user_pass' => $user['user_pass'],
                'user_nicename' => 'user' . $user_id,
                'user_email' => $user['user_email'],
                'display_name' => $user['user_email'],
                'nickname' => $user['user_email'],
                'user_registered' => $user['user_registered'],
            ];

            wp_insert_user($userdata);
            $count++;
        }

        WP_CLI::success('Insert ' . $count . ' users');
    }

    public function changeUsersLogin(){
        global $wpdb;
        $tablename = $wpdb->prefix . "users";

        $users = get_users();
        foreach($users as $user){
            $wpdb->update($tablename, ['user_login' => $user->user_email], ['ID' => $user->user_id]);
        }
    }

}
