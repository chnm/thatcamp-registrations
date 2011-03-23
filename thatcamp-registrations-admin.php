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
    		add_menu_page(__('THATCamp Registrations', 'thatcamp-registrations'), __('TC Registrations', 'thatcamp-registrations'), 'manage-options', 'thatcamp-registrations', array($this, 'registrations_display'));
    		add_submenu_page( 'thatcamp-registrations', __('Settings for THATCamp Registrations', 'thatcamp-registrations'), __('Settings', 'thatcamp-registrations'), 'manage_options', 'thatcamp-registrations-settings', array($this, 'settings_display'));
    	}
    }

    /**
     * Displays various panels for the admin registration. If there is an ID set
     * in the URL, it will display a single registration record based on that ID.
     * Otherwise, it will display all the registration records in a table.
     */
    function registrations_display() {
        // if id is set in the URL, we need to view the application with that ID.
        if ( $id = @$_GET['id'] ) {
            $registration = thatcamp_registrations_get_registration_by_id($id);
            $applicant = thatcamp_registrations_get_applicant_info($registration);
            $applicantUser = 0; 
            if (($userId = email_exists($applicant->user_email)) && is_user_member_of_blog($userId)) {
                $applicantUser = 1;
            }
            
            if (isset($_POST['update_status'])) {
    			thatcamp_registrations_process_registration($_GET['id'], $_POST['status']);		    
    			if (isset($_POST['user_account']) && $_POST['user_account'] == 1) {
    			    thatcamp_registrations_process_user($id);
    			}
                wp_redirect( get_admin_url() . 'admin.php?page=thatcamp-registrations&applicant_saved=1' );
    		}
        }                 
    ?>
    <style type="text/css" media="screen">
        #thatcamp-registrations-panel {
            background: #fff;
        	margin: 25px 15px 25px 15px;
        	padding: 20px;
        	-moz-border-radius: 6px;
        	-webkit-border-radius: 6px;
        	border-radius: 6px;
        	-moz-box-shadow: #ddd 0 -1px 10px;
        	-webkit-box-shadow: #ddd 0 -1px 10px;
        	-khtml-box-shadow: #ddd 0 -1px 10px;
        	box-shadow: #ddd 0 -1px 10px;
        	color: #555;
        	overflow: hidden;
        	
        }
        
        #thatcamp-registrations-applicant-info th,
        #thatcamp-registrations-applicant-info td {
            border-bottom: 1px dotted #ddd;
            line-height: 2em;
        }
        #thatcamp-registrations-applicant-info th {
            width: 20%;
        }
        #thatcamp-registrations-list-link {
            display:block;
            float:right;
            width: 20%;
            background: #eee;
            color: #333;
            text-decoration:none;
            text-align:center;
            padding: 10px 20px;
            border:1px solid #ddd;
            -moz-border-radius: 6px;
        	-webkit-border-radius: 6px;
        	border-radius: 6px;
        }
        #thatcamp-registrations-list-link:link,
        #thatcamp-registrations-list-link:visited {
            color: #21759B;
        }
        #thatcamp-registrations-list-link:hover,
        #thatcamp-registrations-list-link:active {
            color: #D54E21;
            background: #f9f9f9;
        }
    </style>
        <div class="wrap">
            <h2><?php echo _e('THATCamp Registrations'); ?></h2>
            <?php if ($id): ?>
            <div id="thatcamp-registrations-panel">
                <a id="thatcamp-registrations-list-link" href="admin.php?page=thatcamp-registrations&amp;noheader=true">Back to registrations list</a>

                <h3>Application from <?php echo $applicant->first_name; ?> <?php echo $applicant->last_name; ?> (<?php echo $applicant->user_email; ?>)</h3>
                <h4><?php _e( 'Application Status', 'thatcamp-registrations' ) ?></h4>
                
                <form action="admin.php?page=thatcamp-registrations&amp;id=<?php echo $id; ?>&amp;noheader=true" method="post">
                    <label for="user_account">Is Blog User?</label>
                    <select name="user_account">
                        <option value="0">No</option>
                        <option value="1"<?php if($applicantUser == 1) { echo ' selected="selected"';} ?>>Yes</option>
                    </select>
                    <p class="description"><?php _e('Applicant is a user?', 'thatcamp-registrations'); ?></p>

                    <select name="status">
                        <option value="pending"<?php if($registration->status == "pending") { echo ' selected="selected"';} ?>><?php _e('Pending', 'thatcamp-registrations'); ?> </option>
                        <option value="approved"<?php if($registration->status == "approved") { echo ' selected="selected"';} ?>><?php _e('Approved', 'thatcamp-registrations'); ?> </option>
                        <option value="rejected"<?php if($registration->status == "rejected") { echo ' selected="selected"';} ?>><?php _e('Rejected', 'thatcamp-registrations'); ?> </option>
                    </select>
                
                    <p class="description"><?php _e('The status of this application.', 'thatcamp-registrations'); ?></p>

                    <label for="bootcamp"><input type="checkbox" name="bootcamp" value="1" <?php if($registration->bootcamp) { echo ' checked="checked"';} ?>>Interested in Bootcamp?</label>
                    
                    <input type="submit" name="update_status" value="Update Status">

                </form>

                <h4>Application Text</h4>
                <?php echo $registration->application_text; ?>
                <h4>Additional Information?</h4>
                <?php echo $registration->additional_information; ?>
            </div>
            <?php
            // Otherwise, we need to view the list of applications.
            else:
            
            ?>
            
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
            if ( empty($options)): ?>
            <div class="updated">
                <p><?php _e('You have not updated your THATCamp Registrations settings.'); ?> <a href="admin.php?page=thatcamp-registrations-settings"><?php _e('Update your settings.'); ?></a></p>
            </div>
            <?php endif; ?>
            
            <?php 
            
            $registrations = thatcamp_registrations_get_registrations(); 
            if ($registrations): ?>
            
                <p>There are <?php echo count($registrations); ?> total registrations.</p>
                <form action="" method="post">
                
                <table class="widefat fixed" cellspacing="0">
                <thead>
                <tr class="thead">
                    <th>Applicant Name</th>
                    <th>Applicant Email</th>
                    <th>Bootcamp?</th>
                    <th>Status</th>
                    <th>View</th>
                </tr>
                </thead>

                <tfoot>
                <tr class="thead">
                    <th>Applicant Name</th>
                    <th>Applicant Email</th>
                    <th>Bootcamp?</th>
                    <th>Status</th>
                    <th>View</th>
                </tr>
                </tfoot>

                <tbody id="users" class="list:user user-list">
                <?php foreach ( $registrations as $registration ): ?>
                    <tr>
                        <?php $applicant = thatcamp_registrations_get_applicant_info($registration); ?>                      
                        <td><?php echo $applicant->first_name; ?> <?php echo $applicant->last_name; ?></td>
                        <td><?php echo $applicant->user_email; ?></td>
                        <td><?php echo $registration->bootcamp ? 'Yes' : 'No'; ?></td>
                        <td><?php echo ucwords($registration->status); ?></td>
                        <td><a href="admin.php?page=thatcamp-registrations&amp;id=<?php echo $registration->id; ?>">View Full Application</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                </table>
                </form>
                <?php else: ?>
                    <p>You don't have any registrations yet.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php
    }
    
    function settings_display() {
        
        if ( isset($_POST['thatcamp_registrations_save_changes']) ) {
            
            $newOptions = array(
                'open_registration'             =>  $_POST['open_registration'],
                'create_user_accounts'          =>  $_POST['create_user_accounts'],
                'require_login'                 =>  $_POST['require_login'],
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
                        <th scope="row"><label for="open_registration"><?php _e( 'Open Registration?', 'thatcamp-registrations' ) ?></label></th>
                        <td>
                            <select name="open_registration">
                                <option value="0"><?php _e('No'); ?> </option>
                                <option value="1"<?php if($options['open_registration'] == 1) { echo ' selected="selected"';} ?>><?php _e('Yes'); ?> </option>
                            </select>
                            <p class="description"><?php _e('If &#8220;Yes&#8221; the registration form will be open.', 'thatcamp-registrations'); ?></p>
                        </td>
                    </tr>
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
                    
                    <?php /* Removing auto-approve field until this feature works. ?>
                    <tr valign="top">
                        <th scope="row"><label for="auto_approve_applications"><?php _e('Automatically approve applications', 'thatcamp-registrations'); ?></label></th>
                        <td>
                            <input type="text" name="auto_approve_applications" value="<?php echo $options['auto_approve_applications']; ?>" />
                            <p class="description"><?php _e('If you wish THATCamp Registrations to automatically approve a certain number of applications, fill in that number here. If left blank, or set to 0, no applications will be automatically approved.', 'thatcamp-registrations'); ?></p>
                        </td>
                    </tr>
                    <?php */ ?>
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