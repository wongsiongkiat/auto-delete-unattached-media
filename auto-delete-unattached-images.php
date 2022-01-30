<?php
/*
Plugin Name: Auto Delete Unattached Media
Plugin URI:  https://wordpress.org/plugins/auto-delete-unattached-media/
Description: Automatically delete unattached media/images/attachments every minute in WordPress.
Version:     1.0
Author:      Wong Siong Kiat
Author URI:  https://github.com/wongsiongkiat/auto-delete-unattached-media/
License:     GPLv2 or later
License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
Text Domain: auto-delete-unattached-media
Requires at least: 4.9
Requires PHP: 5.6 or later

This program is free software; you can redistribute it and/or modify it under the terms of the GNU
General Public License version 2, as published by the Free Software Foundation. You may NOT assume
that you can use any other version of the GPL.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

if(!defined('ABSPATH'))
	die('Invalid request.');

// Action to take when plugin is activated or deactivated.
register_activation_hook(__FILE__, 'activate_adum_plugin');
register_deactivation_hook(__FILE__, 'deactivate_adum_plugin');

// Plugin activated.
function activate_adum_plugin() {
    if(!wp_get_schedule('auto_delete_unattached_media'))
        wp_schedule_event(time() + 60, 'delete_unattached_media_every_minute', 'auto_delete_unattached_media');
}

// Plugin deactivated.
function deactivate_adum_plugin() {
    wp_clear_scheduled_hook('auto_delete_unattached_media');
}

// Schedule to auto delete unattached media.
function delete_unattached_cron_schedule($schedules) {
	$schedules['delete_unattached_media_every_minute'] = array(
		'interval' => 60,
		'display'  => __('Auto Delete Unattached Media Every Minute'),
	);

	return $schedules;
}
add_filter('cron_schedules', 'delete_unattached_cron_schedule');

// Delete all unattached attachments.
function delete_unattached_media() {
    $args = array(
        'fields' => 'ids',
	'post_type' => 'attachment',
	'posts_per_page' => 10,
	'post_parent' => 0,
        'post_status' => 'any',
        'orderby' => 'date',
		'order' => 'DESC'
	);
	$unused_media = new WP_Query($args);

    if($unused_media->have_posts()) {
    	foreach($unused_media->posts as $unused_media_id)
            wp_delete_attachment($unused_media_id, true);

		wp_reset_postdata();
	}
}
add_action('auto_delete_unattached_media', 'delete_unattached_media');
