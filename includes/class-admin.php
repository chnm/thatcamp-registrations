<?php

if ( !class_exists( 'Thatcamp_Registrations_Admin_Main' ) ) :

class Thatcamp_Registrations_Admin_Main {

	function thatcamp_registrations_admin_main () {
		add_action( 'admin_init', array ( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}
	
	function init() {
	    do_action( 'thatcamp_registrations_admin_init' );
	}
	
    function admin_menu() {
    	if ( function_exists( 'add_menu_page' ) ) {
    		add_menu_page(__('THATCamp Registrations'), __('TC Registrations'), 'manage-options', 'thatcamp-registrations', array($this, 'registrations_display'));
    		add_submenu_page( 'thatcamp-registrations', 'Settings for THATCamp Registrations', 'Settings', 'manage_options', 'thatcamp-registrations-settings', array($this, 'settings_display'));
    	}
    }
    
    function save_settings() {
        
        /*
        Needs to save settings for:
        
        1. Create/use user accounts for registrations
        2. Require authentication before registering
        3. Approve applications up to X? - If set to any number other than 0, system will automatically approve applications up that number.
        5. Email to send for
            a. Accepted applications
            b. Rejected applications
            c. Post-application submission
        
        */
        
    }

    function registrations_display() {
    ?>
        <div class="wrap">
            
            <h2><?php echo _e('THATCamp Registrations'); ?></h2>
            
            <?php 
            /* 
            Get list of applications. Sort by:
            
            1. All applications
            2. Pending applications
            3. Approved applications
            4. Rejected applications.
            
            List needs a bulk action to change status of checked applications.
            
            */ ?>
        </div>
    <?php
    }
    
    function settings_display() {
    ?>
        <div class="wrap">

            <h2><?php echo _e('Settings for THATCamp Registrations'); ?></h2>

            <?php 
            /* 
            Get list of applications. Sort by:

            1. All applications
            2. Pending applications
            3. Approved applications
            4. Rejected applications.

            List needs a bulk action to change status of checked applications.

            */ ?>
        </div>
    <?php 
    }
}

endif; // class exists

$thatcamp_registrations_admin_main = new Thatcamp_Registrations_Admin_Main();