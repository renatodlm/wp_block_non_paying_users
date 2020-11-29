<?php

/*
# WordPres - Block non-paying users
Description: <br>
This function is intended to block all WordPress users who have not made purchases on Woocommerce within a stipulated period.<br>

#Function Name: WordPres - Block non-paying users<br>
Function URL: https://github.com/rlmdev90/wp_block_non_paying_users<br>
Version: 1.0<br>

Author: Renato de Lima Marques<br>
Author URI: https://www.linkedin.com/in/rlmdev90/<br>

## This function depends on:

Wordpress - URL: https://wordpress.org/<br>
Woocommerce - plugin URL: https://wordpress.org/plugins/woocommerce/<br>
New User Approve - plugin - URL: https://br.wordpress.org/plugins/new-user-approve/<br>

 */

function wp_block_non_paying_users()
{
    //get current date
    $today = date("Ymd");

    //specific role
    $specific_role = 'customer';

    //get specific role 
    $users = get_users(['role__in' => [$specific_role]]);

    $initial_date = '2020-01-01';
    $final_date = '2020-12-31';
    $initial_date = date('Ymd', strtotime($initial_date));
    $final_date = date('Ymd', strtotime($final_date));

    //default in (New User Approve) plugin - URL: https://br.wordpress.org/plugins/new-user-approve/
    $status = 'deny';

    //checks if it has passed the date of the end of the event
    if ($today > $final_date) {

        foreach ($users as $user) {

            //get user registered date
            $date_user = $user->user_registered;
            $registrado = date('Ymd', strtotime($date_user));

            //checks whether the User Registration was during the current sales period
            if ($registrado >= $initial_date && $registrado <= $final_date) {

                //get orders completed and processing - Complete list: pending, processing, on-hold, completed, cancelled, refunded, failed
                $args = array(
                    'customer_id' => $user->ID,
                    'post_status' => array('completed', 'processing')
                );

                $orders = wc_get_orders($args); //get order list by current user

                if (count($orders) < 1) {

                    //get status - require plugin (New User Approve) - URL: https://br.wordpress.org/plugins/new-user-approve/
                    $do_update = apply_filters(
                        'new_user_approve_validate_status_update',
                        true,
                        $user->ID,
                        $status
                    );
                    //checks if user is block
                    if ($do_update) {
                        do_action('new_user_approve_' . $status . '_user', $user->ID);
                        do_action('new_user_approve_user_status_update', $user->ID, $status);
                        //blocked users - require plugin (New User Approve) - URL: https://br.wordpress.org/plugins/new-user-approve/
                    }
                }
            }
        }
    }
}
