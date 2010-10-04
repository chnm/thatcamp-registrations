<?php

if ( !class_exists( 'Thatcamp_Registrations_Admin' ) ) :

class Thatcamp_Registrations_Admin {

	function thatcamp_registrations_admin() {
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
            
            <?php 
            $options = get_option('thatcamp_registrations_options');
            if ( !isset($options)): ?>
            <div class="updated">
                <p><?php _e('You have not updated your THATCamp Registrations settings.'); ?> <a href="admin.php?page=thatcamp-registrations-settings"><?php _e('Update your settings.'); ?></a></p>
            </div>
            <?php endif; ?>
            
            <?php 
            /*
            $registrations = thatcamp_registrations_get_registrations(); 
            if ($registrations): 
                foreach ($registrations as $registration):
            ?>
            
            
            
            <?php 
                endwhile; 
            endif; 
            */
            ?>
        </div>
    <?php
    }
    
    function settings_display() {
        
        if ( isset($_POST['thatcamp_registrations_save_changes']) ) {
            
            $newOptions = array(
                'create_user_accounts'          =>  $_POST['create_user_accounts'],
                'require_login'                 =>  $_POST['require_login'],
                'auto_approve_applications'     =>  is_numeric($_POST['auto_approve_applications']) ? $_POST['auto_approve_applications'] : '0',
                'pending_application_email'     =>  $_POST['pending_application_email'],
                'accepted_application_email'    =>  $_POST['accepted_application_email'],
                'rejected_application_email'    =>  $_POST['rejected_application_email']
                );
            
            update_option('thatcamp_registrations_options', $newOptions);
                
        }
        
        $options = get_option('thatcamp_registrations_options');
    
    ?>
        <div class="wrap">

            <h2><?php echo _e('Settings for THATCamp Registrations', 'thatcamp-registrations'); ?></h2>
            
            <form action="" method="post">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="create_user_accounts"><?php _e( 'Create user accounts after registering?', 'thatcamp-registrations' ) ?></label></th>
                        <td>
                            <select name="create_user_accounts">
                                <option value="0"><?php _e('No'); ?> </option>
                                <option value="1"<?php if($options['create_user_accounts'] == 1) { echo ' selected="selected"';} ?>><?php _e('Yes'); ?> </option>
                            </select>
                            <p class="description"><?php _e('If &#8220;Yes&#8221; the Registration form will create a user account after an application has been approved, if one does not exist for the email associated with the application. If no, registrations will not be associated with WordPress users.', 'thatcamp-registrations'); ?></p>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><label for="require_login"><?php _e( 'Require login before registering?', 'thatcamp-registrations' ) ?></label></th>
                        <td>
                            <select name="require_login">
                                <option value="0"><?php _e('No'); ?> </option>
                                <option value="1"<?php if($options['require_login'] == 1) { echo ' selected="selected"';} ?>><?php _e('Yes'); ?> </option>
                            </select>
                            <p class="description"><?php _e('If &#8220;Yes&#8221; users will be required to log in before completing the registration form.'); ?></p>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><label for="auto_approve_applications"><?php _e('Automatically approve applications', 'thatcamp-registrations'); ?></label></th>
                        <td>
                            <input type="text" name="auto_approve_applications" value="<?php echo $options['auto_approve_applications']; ?>" />
                            <p class="description"><?php _e('If you wish THATCamp Registrations to automatically approve a certain number of applications, fill in that number here. If left blank, or set to 0, no applications will be automatically approved.', 'thatcamp-registrations'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="pending_application_email"><?php _e('Pending Application Email', 'thatcamp-registrations'); ?></label></th>
                        <td>
                            <textarea name="pending_application_email" rows="5" cols="50"><?php if( !empty($options['pending_application_email']) ) echo $options['pending_application_email']; ?></textarea>
                            
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="accepted_application_email"><?php _e('Accepted Application Email', 'thatcamp-registrations'); ?></label></th>
                        <td>
                            <textarea name="accepted_application_email" rows="5" cols="50"><?php if( !empty($options['accepted_application_email']) ) echo $options['accepted_application_email']; ?></textarea>
                            
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="reject_application_email"><?php _e('Rejected Application Email', 'thatcamp-registrations'); ?></label></th>
                        <td>
                            <textarea name="rejected_application_email" rows="5" cols="50"><?php if( !empty($options['rejected_application_email']) ) echo $options['rejected_application_email']; ?></textarea>
                            
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"></th>
                        <td>
                            <input type="submit" name="thatcamp_registrations_save_changes" class="button-primary" value="<?php _e('Save Changes'); ?>" />
                        </td>
                    </tr>
                </table>
                <br />
            </form>
        </div>
    <?php 
    }
}

endif; // class exists

$thatcamp_registrations_admin = new Thatcamp_Registrations_Admin();