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
    		add_menu_page('THATCamp Registrations', 'TC Registrations', 'manage-options', dirname(__FILE__) . '/class-admin-main.php', array( $this, 'display'));
    	}
    }

    function display() {
    ?>
        <div class="wrap">
            
            <h2>THATCamp Registrations</h2>

            <form action="" method="post">
                
            </form>
    
        </div>
    <?php
    }
}

endif; // class exists

$thatcamp_registrations_admin_main = new Thatcamp_Registrations_Admin_Main();