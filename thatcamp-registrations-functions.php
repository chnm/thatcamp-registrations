<?php

/**
 * Returns registration records based on type
 * 
 * @param array The parameters on which to retrieve registrations
 **/
function thatcamp_registrations_get_registrations($params = array()) {
    global $wpdb;
	$registrations_table = $wpdb->prefix . "thatcamp_registrations";
	
	$sql = "SELECT * FROM " . $registrations_table;
	
	if( $id = $params['id']) {
	    $sql .= " WHERE id=".$params['id'];
	    if($status = $params['status']) {
	        $sql .= " AND status = CONVERT( _utf8 '$status' USING latin1 )";
	    }
	} elseif ($status = $params['status']) {
        $sql .= " WHERE status = CONVERT( _utf8 '$status' USING latin1 )";
    }
	
    // echo $sql; exit;
	$results = $wpdb->get_results($sql, OBJECT);
	return $results;
}

/**
 * Processes an array of registrations based on ID
 * 
 * @param array The IDs of the registration records.
 * @param string The status for the registration records.
 **/
function thatcamp_registrations_process_registrations($ids = array(), $status) {
    global $wpdb;
    
    if ($status) {
        foreach ($ids as $id) {
            $sql = "UPDATE " . $wpdb->prefix . "thatcamp_registrations SET status = '" . $status . "' WHERE id = '" . $id . "'";
            $wpdb->query($sql);
        }
    }
    
    return;
}

/**
 * Process a single registration based on ID
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
 * Adds a single registration entry
 * 
 * @param string The status of the registration record.
 **/
function thatcamp_registrations_add_registration($status = 'pending') {
    global $wpdb;
    
    $autoApprove = thatcamp_registrations_option('auto_approve_applications');
    
    if ($autoApprove !== 0) {
        $status = 'approved';
    }
    
    $applicationText = isset($_POST['application_text']) ? $_POST['application_text'] : null;
    $bootcampSession = isset($_POST['bootcamp_session']) ? $_POST['bootcamp_session'] : null;
    $applicationText = isset($_POST['additional_information']) ? $_POST['additional_information'] : null;

    $sql = "INSERT INTO " . $wpdb->prefix . "assignments SET application_text = '" . $applicationText . "', bootcamp_session = '" . $botcampSession . "', additional_information = '" . $additional_information . "', status = '" . $status. "'";
    $wpdb->query($sql);
    return;
}

function thatcamp_registrations_get_registration_by_id($id) 
{
    global $wpdb;
	$registrations_table = $wpdb->prefix . "thatcamp_registrations";
	$sql = "SELECT * from " . $registrations_table . " WHERE id = " .$id;
	return $wpdb->get_row($sql, OBJECT);
}

function thatcamp_registrations_delete_registration($id)
{
    global $wpdb;
    $registrations_table = $wpdb->prefix . "thatcamp_registrations";
    if($id) {
        $wpdb->query("DELETE FROM " . $registrations_table . " WHERE id = '" . $id . "'");
    }
}

function thatcamp_registrations_options() {
    return get_option('thatcamp_registrations_options');
}

function thatcamp_registrations_option($optionName) {
    if (isset($optionName)) {
        $options = thatcamp_registrations_options();
        return $options[$optionName];
    }
    return;
}