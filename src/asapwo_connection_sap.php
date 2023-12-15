<?php
/**
 * Class to handle 'Connect SAP' functionality
 */
class AsapWoConnectSap {
    /**
     * Render the 'Connect SAP' page
     */
    public static function render_page() {
        global $r_message;
        ?>
        <div class="awf-form-main-wrapper">
            <div class="awf-form-main-container">
                <h1>Connect your SAP account</h1>
                <form method="post" action="">
                    <?php self::render_form_fields(); ?>
                    <div class="awf-form-group">
                        <input type="Submit" name="asapwo_save_settings" class="awf-form-submit-btn" value="Connect account">
                    </div>
                    <?php if (isset($_POST['asapwo_save_settings'])) {echo '<div class="asapwo-message">' . esc_html($r_message) . '</div>';}?>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Render form fields for SAP connection
     */
    private static function render_form_fields() {
        ?>
        <div class="awf-form-group">
            <label for="asapwo_server_companydb" class="awf-form-text-label">Enter your SAP Server Database name*</label>
            <input type="text" class="awf-form-control" name="asapwo_companydb" value="<?php echo esc_attr(get_option('asapwo_companydb')); ?>">
        </div>
        <div class="awf-form-group">
            <label for="asapwo_server_ip" class="awf-form-text-label">Enter your SAP Server ip*</label>
            <input type="text" class="awf-form-control" name="asapwo_server_ip" value="<?php echo esc_attr(get_option('asapwo_server_ip')); ?>">
        </div>
        <div class="awf-form-group">
            <label for="asapwo_server_port" class="awf-form-text-label">Enter your SAP Server port*</label>
            <input type="text" class="awf-form-control" name="asapwo_port" value="<?php echo esc_attr(get_option('asapwo_port')); ?>">
        </div>
        <div class="awf-form-group">
            <label for="asapwo_server_user" class="awf-form-text-label">Enter your SAP Username*</label>
            <input type="text" class="awf-form-control" name="asapwo_username" value="<?php echo esc_attr(get_option('asapwo_username')); ?>">
        </div>
        <div class="awf-form-group">
            <label for="asapwo_server_password" class="awf-form-text-label">Enter your SAP Password*</label>
            <input type="password" class="awf-form-control" name="asapwo_password" value="<?php echo esc_attr(get_option('asapwo_password')); ?>">
        </div>
        <?php
    }

    /**
     * Save SAP settings to the database
     */
    public static function save_settings() {
        global $wpdb, $r_message;

        if (isset($_POST['asapwo_save_settings'])) {
            $table_name = $wpdb->prefix . 'asapwo_settings';

            // Check if the table exists, if not, create it
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
                self::create_settings_table();
            }

            // Set a unique key for the replace operation, assuming 'name' is unique
            $data = array(
                'name'       => 'asapwo',
                'server_ip'  => sanitize_text_field($_POST['asapwo_server_ip']),
                'server_port' => intval($_POST['asapwo_port']),
                'username'   => sanitize_text_field($_POST['asapwo_username']),
                'password'   => sanitize_text_field($_POST['asapwo_password']),
                'companydb'  => sanitize_text_field($_POST['asapwo_companydb']),
            );

            // Replace the data in the table based on the 'name' field
            $wpdb->replace($table_name, $data);

            // Check if the row was updated or inserted
            if ($wpdb->rows_affected > 0) {
                $r_message = "You have updated your SAP account data";
            } else {
                $r_message = "You have connected your SAP account";
            }
        }
    }

    /**
     * Create the settings table in the database
     */
    private static function create_settings_table() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'asapwo_settings';

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            server_ip VARCHAR(255) NOT NULL,
            server_port INT NOT NULL,
            username VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
            companydb VARCHAR(255) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

// Hook to save settings on admin initialization
add_action('admin_init', ['AsapWoConnectSap', 'save_settings']);
