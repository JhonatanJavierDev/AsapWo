<?php
require_once 'asapwo_update.php';

class RealSynchronization
{
    public static function synchronizePage()
    {
        global $r_message, $wpdb, $wc_credentials;

        echo '<div class="awf-form-main-wrapper">';
        echo '<div class="awf-form-main-container">';
        echo '<h1>Update inventory now</h1>';
        echo '<form method="post" action="">';
        echo '<div class="awf-form-group">';
        echo '<input type="Submit" name="asapwo_perform_update" class="awf-form-submit-btn" value="Update products now">';
        echo '</div>';

        if (isset($_POST['asapwo_perform_update'])) {
            // Instantiate the AsapWoUpdate class
            $asapWoUpdate = new AsapWoUpdate();
        
            // Call the non-static method to perform the update and get the message
            $r_message = $asapWoUpdate->perform_update();
        
            // Display the message or perform other actions if needed
            echo '<div class="asapwo-message" style="font-size: 22px; color: green;">' . esc_html($r_message) . '</div>';
        }
        
        
        echo '</form>';
        echo '</div>';
        echo '</div>';
    }
}

