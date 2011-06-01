<?php

if ( !class_exists( 'Thatcamp_Registrations_Public_Registration' ) ) :

class Thatcamp_Registrations_Public_Registration {
    
    private $options;
    private $current_user;
    
    function thatcamp_registrations_public_registration() {
        add_shortcode('thatcamp-registration', array($this, 'shortcode'));  
        $this->options = get_option('thatcamp_registrations_options');  
        $this->current_user = wp_get_current_user();
    }
    
    function shortcode($attr) {
        if (thatcamp_registrations_option('open_registration') == 1) {
            return $this->display_registration();
        } else {
            return 'Registration is closed.';
        }
    }
    
    /**
     * Displays the registration information on the public site.
     *
     * @todo - Refactor most of the logic for checking whether to display the
     * user and application forms.
     **/
    function display_registration() {
        $alerts = array();
        // Define some alerts if there are errors with the form.
        if ( !empty($_POST) ) {
            // Application text is required.
            if ( empty( $_POST['application_text']) ) {
                $alerts['application_text'] = __('You must add application text.', 'thatcamp-registrations');
            }
            
            // User email is required.
            if (!is_user_logged_in() && empty( $_POST['user_email'] )) {
                $alerts['user_email'] = __('You must add an email address.', 'thatcamp-registrations');
            }
            
            $userEmail = is_user_logged_in() ? $this->current_user->user_email : @$_POST['user_email'];

            if ($existingApp = thatcamp_registrations_get_registration_by_applicant_email($userEmail)) {
                $alerts['existing_application'] = __('Your have already registered with that email address.','thatcamp-registrations');
            }
            
        }
        
        // If user registration is required, and the user isn't logged in.
        if ( thatcamp_registrations_user_required() && !is_user_logged_in() ) {
            echo '<div>You must have a user account to complete your application. Please <a href="<?php echo wp_login_url( get_permalink() ); ?>" title="Login">log in</a>.</div>';
        }
        // If the currently authenticated user has submitted an application.
        elseif (is_user_logged_in() && $existingApp = thatcamp_registrations_get_registration_by_user_id($this->current_user->ID)) {
            echo '<div>'.__('Your have already registered!','thatcamp-registrations').'</div>';
            
        }
        elseif ((!empty($_POST)) && empty($alerts)) {
            thatcamp_registrations_add_registration();
            echo '<p>Your registration has been saved.</p>';
        }
        else {
            
            if (!empty($alerts)) {
                foreach ($alerts as $alert) {
                    echo '<p style="background:#fc0; padding: 4px;">'.$alert.'</p>';
                }
            }
            
            echo '<form method="post" action="">';
            
            $this->_application_form();
            
            // If user login is not required, display the user info form.
            if ( !thatcamp_registrations_user_required() && !is_user_logged_in()) {
                $this->_user_info_form();
            } elseif (is_user_logged_in()) {
                echo '<input type="hidden" name="user_id" value="'. $this->current_user->ID .'" />';
                echo '<input type="hidden" name="user_email" value="'. $this->current_user->user_email .'" />';
            }
            
            echo '<input type="submit" name="thatcamp_registrations_save_registration" value="'. __('Submit Application', 'thatcamp-registrations') .'" />';
            echo '</form>';
        }
    }
    
    function _user_info_form() {
    ?>
    <fieldset>
        <legend>Personal Information</legend>
        <div>
            <label for="first_name"><?php _e('First Name'); ?></label><br/>
            <input type="text" name="first_name" value="<?php echo $this->current_user->first_name; ?>" />
        </div>
        <div>
            <label for="last_name"><?php _e('Last Name'); ?></label><br/>
            <input type="text" name="last_name" value="<?php echo @$this->current_user->last_name; ?>" />
        </div>
        <div>
            <label for="user_email"><?php _e('Email'); ?></label><br/>
            <input type="text" name="user_email" value="<?php echo @$this->current_user->user_email; ?>" />
        </div>
        <div>
            <label for="user_url"><?php _e('Website'); ?></label><br/>
            <p class="explanation"><?php _e('Example: thatcamp.org'); ?></p>
            <input type="text" name="user_url" value="<?php echo @$this->current_user->user_url; ?>" />
        </div>
        <div>
            <label for="user_title"><?php _e('Position/Job Title', 'thatcamp-registrations'); ?></label><br/>
            <p class="explanation"><?php _e('Examples: Assistant Professor, Instructional Technologist, Archivist, Software Engineer, Graduate student', 'thatcamp-registrations'); ?></p>
            <input type="text" name="user_title" value="<?php echo @$this->current_user->user_title; ?>" />
        </div>
        <div>
            <label for="user_organization"><?php _e('Organization', 'thatcamp-registrations'); ?></label><br />
            <p class="explanation"><?php _e('Examples: George Mason University, New York Public Library, Automattic', 'thatcamp-registrations'); ?></p>
            <input type="text" name="organization" value="<?php echo @$this->current_user->user_organization; ?>" />
        </div>
        <div>
            <label for="user_twitter"><?php _e('Twitter Screenname', 'thatcamp-registrations'); ?></label><br/>
            <p class="explanation"><?php _e('Example: @thatcamp', 'thatcamp-registrations'); ?></p>
            <input type="text" name="user_twitter" value="<?php echo @$this->current_user->user_twitter; ?>" />
        </div>
        <div>
            <label for="description"><?php _e('Bio'); ?></label><br/>
            <p class="explanation"><?php _e('Tell us a little about yourself: your background with the humanities and/or technology, your research or professional interests, your opinion of Nicholas Carr or Slavoj Zizek, your best score at Galaga, and so forth', 'thatcamp-registrations'); ?></p>
            <textarea cols="45" rows="8" name="description"><?php echo @$this->current_user->description; ?></textarea>
        </div>
    </fieldset>
    <?php
    }
    
    function _application_form() {
    ?>
    <fieldset>
        <legend>Application Information</legend>
    <div>
        <label for="application_text"><?php _e('Why do you want to come to THATCamp?', 'thatcamp-registrations'); ?>*</label><br />
        <p class="explanation">
        <?php _e('In a few sentences, no more than a couple of paragraphs, please 
        tell us why you want to come to THATCamp. You might tell us what task 
        you want to accomplish, what problem you want to solve, what new 
        perspective you want to understand, what issue you want to discuss, or 
        what skill you want to learn. Remember, though: no paper proposals! 
        THATCamp is for working and talking with others, not for presenting to 
        a silent audience', 'thatcamp-registrations'); ?>
        </p>
        <textarea cols="45" rows="8" name="application_text"><?php echo @$_POST['application_text']; ?></textarea>
    </div>
    <input type="hidden" name="date" value="<?php echo current_time('mysql'); ?>">
    
    </fieldset>
    <?php
    }
}

endif; // class exists

$thatcamp_registrations_public_registration = new Thatcamp_Registrations_Public_Registration();