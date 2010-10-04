<?php

if ( !class_exists( 'Thatcamp_Registrations_Public_Registration' ) ) :

class Thatcamp_Registrations_Public_Registration {
    
    public $options;
    public $current_user;
    
    function thatcamp_registrations_public_registration() {
        add_shortcode('thatcamp-registration', array($this, 'shortcode'));  
        $this->options = get_option('thatcamp_registrations_options');  
        $this->current_user = wp_get_current_user();
    }
    
    //
    function shortcode($attr) {
        
        $html = '';
        
        // If we're processing $POST data without a problem, let's save the registration and send an email.
        if ( $this->check_form() ) {
            $html .= $this->save_registration();

        // Otherwise, let's build us a form
        } else {
            $html .= $this->display_registration();
        }
        
        return $html; 
    }
    
    // This should check the form for required POST data
    function check_form() {
        if ( !empty( $_POST['application_text'] ) ) {
            return true;
        }
        return false;
    }
    
    function auto_approve_applications() {
        return (bool) $this->options['auto_approve_applications'];
    }
    
    function create_user_accounts() {
        return (bool) $this->options['create_user_accounts'];
    }
    
    function user_required() {
        return (bool) $this->options['require_login'];
    }
    
    function save_registration() {
        
        
        
    }
    
    function user_info_form() {
    ?>
    <fieldset>
        <legend>Personal Information</legend>
        
        <label for="first_name"><?php _e('First Name'); ?></label>
        <input type="text" name="first_name" value="<?php echo $this->current_user->first_name; ?>" />
    
        <label for="last_name"><?php _e('Last Name'); ?></label>
        <input type="text" name="last_name" value="<?php echo $this->current_user->last_name; ?>" />
    
        <label for="email"><?php _e('Email'); ?></label>
        <input type="text" name="email" value="<?php echo $this->current_user->user_email; ?>" />
    
        <label for="website"><?php _e('Website'); ?></label>
        <input type="text" name="website" value="<?php echo $this->current_user->user_url; ?>" />
        
        <label for="title"><?php _e('Title', 'thatcamp-registrations'); ?></label>
        <input type="text" name="title" value="<?php echo $this->current_user->user_title; ?>" />
        
        <label for="organization"><?php _e('Organization/Institution', 'thatcamp-registrations'); ?></label>
        <input type="text" name="organization" value="<?php echo $this->current_user->user_organization; ?>" />
        
        <label for="twitter_screenname"><?php _e('Twitter Screenname', 'thatcamp-registrations'); ?></label>
        <input type="text" name="twitter_screenname" value="<?php echo $this->current_user->twitter_screenname; ?>" />
    
        <label for="bio"><?php _e('Bio'); ?></label>
        <textarea name="bio"><?php echo $this->current_user->description; ?></textarea>
        
        <input type="hidden" name="user_id" value="<?php echo $this->current_user->ID; ?>" />
        
    </fieldset>
    <?php
    }
    
    function application_form() {
    ?>
    <fieldset>
        <legend>Application Information</legend>
    
        <label for="application_text"><?php _e('Application', 'thatcamp-registrations'); ?></label>
        <textarea name="application_text"><?php echo @$_POST['application_text']; ?></textarea>
        
        <label for="bootcamp_session"><?php _e('Would you be willing to volunteer to teach a BootCamp session? If so, on what?', 'thatcamp-registrations'); ?></label>
        <textarea name="bootcamp_session"><?php echo @$_POST['bootcamp_session']; ?></textarea>
        
        <label for="additional_information"><?php _e('Additional Information', 'thatcamp-registrations'); ?></label>
        <textarea name="additional_information"><?php echo @$_POST['additional_information']; ?></textarea>
    </fieldset>
    <?php
    }
    
    function display_registration() {
        if ( isset($_POST) ) $this->save_registration();
        
    ?>
    <form method="post" action="">
    
    <?php if ( $this->user_required() && !$this->current_user): ?>
        <p>You must be logged in to complete your application.</p>        
    <?php else: ?>
        <?php echo $this->application_form(); ?>
        <?php if ( !$this->current_user ) echo $this->user_info_form(); ?>
    <?php endif; ?>
    
    <input type="submit" name="thatcamp_registrations_save_registration" value="<?php _e('Submit Application', 'thatcamp-registrations') ; ?>">
    </form>
    <?php
    }
}

endif; // class exists

$thatcamp_registrations_public_registration = new Thatcamp_Registrations_Public_Registration();