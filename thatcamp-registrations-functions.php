<?php
require_once(ABSPATH . WPINC . '/registration.php');

/**
 * Adds a single registration entry. This is a motley function.
 * 
 * @param string The status of the registration record.
 **/
function thatcamp_registrations_add_registration($status = 'pending') {
    
    global $wpdb;
    $table = $wpdb->prefix . "thatcamp_registrations";
    
    $autoApprove = thatcamp_registrations_auto_approve_applications();

    $_POST = stripslashes_deep($_POST);
    
    // The user_id is set to the posted user ID, or null.
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;
    
    $applicant_info = array();
    
    // Array of applicant info fields. May set up an option in plugin so admins can modify this list.
    $applicant_fields = array(
        'first_name',
        'last_name',
        'user_email',
        'user_url',
        'description',
        'user_title',
        'user_organization',
        'user_twitter'
    );
    
    foreach ( $applicant_fields as $field) {
        $applicant_info[$field] = isset($_POST[$field]) ? $_POST[$field] : null;
    }
    
    // If the user_id is null, we don't have an authenticated user. So, we'll use the applicant_info
    if ( $user_id == null ) {

        // If there isn't a user_id, but a user exists with the posted email. Sneaky!
        if ( $user_id = email_exists($applicant_info['user_email'])) {
            thatcamp_registrations_process_user($user_id, $applicant_info);
        }
    }
    
    // If we're auto-approving applications
    if ($autoApprove) {
        // Set the status to approved
        $status = 'approved';
        
        // If we have the create user account option set
        if ( thatcamp_registrations_create_user_accounts() ) {
            // If there isn't a user_id or existing email in the users table, we'll create a user
            $user_id = thatcamp_registrations_process_user($user_id, $applicant_info);
        } 
    }
    
    $date = isset($_POST['date']) ? $_POST['date'] : null;
    $applicationText = isset($_POST['application_text']) ? $_POST['application_text'] : null;
    $bootcampSession = isset($_POST['bootcamp_session']) ? $_POST['bootcamp_session'] : null;
    $additionalInformation = isset($_POST['additional_information']) ? $_POST['additional_information'] : null;
    // Lets serialize the applicant_info before putting it in the database.
    $applicant_info = maybe_serialize($applicant_info);
    
    $wpdb->insert(
        $table, 
        array(
            'applicant_info'            => $applicant_info, 
            'application_text'          => $applicationText,
            'additional_information'    => $additionalInformation,
            'status'                    => $status,
            'date'                      => $date,
            'user_id'                   => $user_id
            )
        );
    return;
}

/**
 * Returns registration records based on type
 * 
 * @param array The parameters on which to retrieve registrations
 **/
function thatcamp_registrations_get_registrations($params = array()) {
    global $wpdb;
	$registrations_table = $wpdb->prefix . "thatcamp_registrations";
	
	$sql = "SELECT * FROM " . $registrations_table;
	
	if( isset($params['id']) && $id = $params['id']) {
	    $sql .= " WHERE id=".$params['id'];
	    if( isset($params['status']) && $status = $params['status']) {
	        $sql .= " AND status = CONVERT( _utf8 '$status' USING latin1 )";
	    }
	} elseif ( isset($params['status']) && $status = $params['status']) {
        $sql .= " WHERE status = CONVERT( _utf8 '$status' USING latin1 )";
    }
	
    // echo $sql; exit;
	$results = $wpdb->get_results($sql, OBJECT);
	return $results;
}

/**
 * Processes an array of registrations based on ID. Uses mainly in the admin.
 * 
 * @param array The IDs of the registration records.
 * @param string The status for the registration records.
 **/
function thatcamp_registrations_process_registrations($ids = array(), $status) {
    global $wpdb;
    $table = $wpdb->prefix . "thatcamp_registrations";
    
    $idArray = array();
    foreach ($ids as $id) {
        $idArray['id'] = $id;
    }
    
    if ($status && !empty($idArray)) {   
        $wpdb->update(
            $table,
            array('status' => $status),
            $idArray
            );
        if ($status == 'approved' && thatcamp_registrations_create_user_accounts()) {
            foreach ($ids as $id) {
                thatcamp_registrations_process_user(null, array(), $id);
            }
        }
    }
    
    return;
}

/**
 * Process a single registration based on ID. Uses mainly in the admin.
 * 
 * @param int The ID of the registration record.
 * @param string The status for the registration record.
 **/
function thatcamp_registrations_process_registration($id, $status) {
    if (isset($id) && isset($status)) {
        thatcamp_registrations_process_registrations(array($id), $status);
    }
    return;
}

/**
 * Processes a WP user when adding/updating a registration record. Only 
 * should be used if we're creating users with approved registrations.
 *
 * @param integer|null The User ID
 * @param array The array of user information.
 * @param $registrationId
 * @return integer The User ID
 **/
function thatcamp_registrations_process_user($userId = null, $userInfo = array(), $registrationId = null, $role = 'author') {
    global $wpdb;
    
    /**
     * If the Registration ID is set, it means we already have a registration 
     * record! Booyah. We'll use the user_id and application_info colums from 
     * that record to process the user.
     */
    if ($registration = thatcamp_registrations_get_registration_by_id($registrationId)) {        
        $userId = $registration->user_id;
        $userInfo = maybe_unserialize($registration->applicant_info);
    } else {
        // If we pass a User ID, we're probably dealing with an existing user.
        if ($userId && !is_user_member_of_blog($userId)) {
            add_existing_user_to_blog(array('user_id' => $userId, 'role' => $role));
        } else if ($userId = email_exists($userInfo['user_email'])) {
        	thatcamp_registrations_update_user_data($userId, $userInfo);
        } else { // We're probably dealing with a new user. Lets create one and associate it to our blog.
        	$randomPassword = wp_generate_password( 12, false );
        	$userEmail = $userInfo['user_email'];
        	$userId = wp_create_user( $userEmail, $randomPassword, $userEmail );
        	add_user_to_blog($wpdb->blogid, $userId, $role);
        	thatcamp_registrations_update_user_data($userId, $userInfo);
        }
    }
    
    return $userId;
}

/**
 * Updates the user data.
 *
 **/
function thatcamp_registrations_update_user_data($userId, $params) 
{
    if ( isset( $userId ) && $userData = get_userdata($userId) ) {
        foreach ($params as $key => $value) {
            update_user_meta( $userId, $key, $value );
        }
    } 
}

/**
 * Gets registration record by ID.
 *
 **/
function thatcamp_registrations_get_registration_by_id($id) 
{
    global $wpdb;
	$registrations_table = $wpdb->prefix . "thatcamp_registrations";
	$sql = "SELECT * from " . $registrations_table . " WHERE id = " .$id;
	return $wpdb->get_row($sql, OBJECT);
}

/**
 * Deletes a registration record by ID.
 *
 **/
function thatcamp_registrations_delete_registration($id)
{
    global $wpdb;
    $registrations_table = $wpdb->prefix . "thatcamp_registrations";
    if($id) {
        $wpdb->query("DELETE FROM " . $registrations_table . " WHERE id = '" . $id . "'");
    }
}

/**
 * Creates a user from a registration record.
 *
 * @param integer $registrationId 
 **/
function thatcamp_registrations_create_user($registrationId)
{
    if ($applicant = thatcamp_registrations_get_registration_applicant($registrationId)) {
        
        // if ( !is_int($applicant) ) {
            return $applicant;
        // }
    }
}

/**
 * Returns the value for thatcamp_registrations_options
 *
 * @uses get_option()
 * @return array The array of options
 **/
function thatcamp_registrations_options() 
{
    return get_option('thatcamp_registrations_options');
}

/**
 * Returns the value for a single THATCamp Registrations option.
 *
 * @uses thatcamp_registrations_options()
 * @param string The name of the option
 * @return string
 **/
function thatcamp_registrations_option($optionName) 
{
    if (isset($optionName)) {
        $options = thatcamp_registrations_options();
        return $options[$optionName];
    }
    return false;
}

function thatcamp_registrations_get_applicant_info($registrationId) 
{
    global $wpdb;
	$registrations_table = $wpdb->prefix . "thatcamp_registrations";
	$sql = "SELECT * from " . $registrations_table . " WHERE id = " .$registrationId;
	$record = $wpdb->get_row($sql, OBJECT);
	if (($record->user_id == 0 || $record->user_id == null) && !empty($record->applicant_info)) {
	    return (object) maybe_unserialize($record->applicant_info);
	} else {
	    return get_userdata($record->user_id);
	    
	}
}

function thatcamp_registrations_send_applicant_email($status = "pending")
{
    
}
 
/**
 * Checks the option to auto approve applications.
 *
 * @return boolean
 **/ 
function thatcamp_registrations_auto_approve_applications() 
{
    return (bool) thatcamp_registrations_option('auto_approve_applications');
}

/**
 * Checks the option to create user accounts upon application approval.
 *
 * @return boolean
 **/
function thatcamp_registrations_create_user_accounts() 
{
    return (bool) thatcamp_registrations_option('create_user_accounts');
}

/**
 * Checks to see if user authentication is required.
 *
 * @return boolean
 **/
function thatcamp_registrations_user_required() 
{
    return (bool) thatcamp_registrations_option('require_login');
}

/**
 * Checks if registration is open.
 *
 * @return boolean
 */
function thatcamp_registrations_registration_is_open()
{
    return (bool) thatcamp_registrations_option('open_registration');
}

/**
 * Generates a random string for a token
 **/
function thatcamp_registrations_generate_token()
{
    return sha1(microtime() . mt_rand(1, 100000));
}