<?php

/**
 * Class to handle 'WooCommerce Credentials' functionality
 */
class AsapWoWooCommerce {
    /**
     * Render the 'WooCommerce Credentials' page
     */
    public static function render_page() {
        global $r_message;
        ?>
        <div class="awf-form-main-wrapper">
            <div class="awf-form-main-container">
                <h1>Connect WooCommerce</h1>
                <form method="post" action="">
                    <?php self::render_form_fields(); ?>
                    <div class="awf-form-group">
                        <input type="submit" name="asapwo_wo_save" class="awf-form-submit-btn" value="Connect account">
                    </div>
                    <?php if (isset($_POST['asapwo_wo_save'])) {echo '<div class="asapwo-message">' . esc_html($r_message) . '</div>';}?>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Render form fields for WooCommerce connection
     */
    private static function render_form_fields() {
        ?>
        <div class="awf-form-group">
            <label for="asapwo_wo_key" class="awf-form-text-label">Enter your Consumer Key*</label>
            <input type="text" class="awf-form-control" name="asapwo_wo_key" value="<?php echo esc_attr(get_option('asapwo_wo_key')); ?>">
            <?php self::render_required_field_error(); ?>
        </div>
        <div class="awf-form-group">
            <label for="asapwo_wo_secret" class="awf-form-text-label">Enter your Consumer Secret*</label>
            <input type="text" class="awf-form-control" name="asapwo_wo_secret" value="<?php echo esc_attr(get_option('asapwo_wo_secret')); ?>">
            <?php self::render_required_field_error(); ?>
        </div>
        <?php
    }

    /**
     * Render error for required fields
     */
    private static function render_required_field_error() {
        ?>
        <div class="awf-form-error">This Field Required*</div>
        <?php
    }

    /**
     * Save WooCommerce settings to the database
     */
    public static function save_settings() {
        global $wpdb, $r_message;

        if (isset($_POST['asapwo_wo_save'])) {
            $table_name = $wpdb->prefix . 'asapwo_api';

            // Check if the table exists, if not, create it
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
                self::create_api_table();
            }

            // Set a unique key for the replace operation, assuming 'name' is unique
            $data = array(
                'name'   => 'woocommerce',
                'api_key'    => sanitize_text_field($_POST['asapwo_wo_key']),
                'api_secret' => sanitize_text_field($_POST['asapwo_wo_secret']),
            );

            // Replace the data in the table based on the 'name' field
            $wpdb->replace($table_name, $data);

            // Check if the row was updated or inserted
            if ($wpdb->rows_affected > 0) {
                $r_message = "You have updated your WooCommerce credentials";
            } else {
                $r_message = "You have connected your WooCommerce account";
            }
        }
    }

    /**
     * Create the API settings table in the database
     */
    private static function create_api_table() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'asapwo_api';

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            api_key VARCHAR(255) NOT NULL,
            api_secret VARCHAR(255) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

// Hook to save WooCommerce settings on admin initialization
add_action('admin_init', ['AsapWoWooCommerce', 'save_settings']);
