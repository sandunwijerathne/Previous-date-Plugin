<?php
/*
Plugin Name: Previous Date Plugin
Description: Modifies the post date and upload folder structure based on a defined previous date.
Version: 1.0
Author: Your Name
*/



// Add a new submenu under "Tools"
add_action('admin_menu', 'custom_settings_menu');
function custom_settings_menu() {
    add_submenu_page(
        'tools.php',          // Parent menu slug
        'Custom Settings',    // Page title
        'Custom Settings',    // Menu title
        'manage_options',     // Capability required to access the page
        'custom-settings',    // Menu slug
        'custom_settings_page' // Callback function to display the page content
    );
}

// Callback function to display the custom settings page
function custom_settings_page() {
    // Get the current value of PREVIOUS_DATE
    $previous_date = get_option('previous_date', '');

    // Save the updated value if submitted
    if (isset($_POST['save_date'])) {
        $previous_date = $_POST['previous_date'];
        update_option('previous_date', $previous_date);

        // Update the value in wp-config.php
        $wpConfigPath = ABSPATH . 'wp-config.php';
        $codeToAdd = "define('PREVIOUS_DATE', '{$previous_date}');";
        $wpConfigContent = file_get_contents($wpConfigPath);

        // Check if the code is already present to avoid duplication
        if (strpos($wpConfigContent, "define('PREVIOUS_DATE'") === false) {
            // Add the code at the end of the file
            $updatedContent = $wpConfigContent . "\n" . $codeToAdd;
        } else {
            // Update the existing code
            $updatedContent = preg_replace(
                "/define\('PREVIOUS_DATE', '.*?'\);/",
                $codeToAdd,
                $wpConfigContent
            );
        }

        // Write the updated content back to wp-config.php
        file_put_contents($wpConfigPath, $updatedContent);

        echo '<div class="notice notice-success"><p>Date saved successfully.</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>Custom Settings</h1>
        <form method="post" action="">
            <label for="previous_date">Previous Date:</label>
            <input type="date" id="previous_date" name="previous_date" value="<?php echo esc_attr($previous_date); ?>">
            <p class="description">Enter the previous date.</p>
            <p><input type="submit" name="save_date" class="button button-primary" value="Save Date"></p>
        </form>
    </div>
    <?php
}

add_action('admin_enqueue_scripts', 'enqueue_custom_settings_page');
function enqueue_custom_settings_page() {
    $screen = get_current_screen();
    if ($screen->id === 'tools_page_custom-settings') {
        wp_enqueue_style('wp-admin-custom', get_template_directory_uri() . '/custom-settings.css');
    }
}



/*
// Add a new submenu under "Tools"
add_action('admin_menu', 'custom_settings_menu');
function custom_settings_menu() {
    add_submenu_page(
        'tools.php',          // Parent menu slug
        'Custom Settings',    // Page title
        'Custom Settings',    // Menu title
        'manage_options',     // Capability required to access the page
        'custom-settings',    // Menu slug
        'custom_settings_page' // Callback function to display the page content
    );
}

// Callback function to display the custom settings page
function custom_settings_page() {
    // Get the current value of PREVIOUS_DATE
    $previous_date = get_option('previous_date', '2022-05-01');

    // Save the updated value if submitted
    if (isset($_POST['save_date'])) {
        $previous_date = $_POST['previous_date'];
        update_option('previous_date', $previous_date);

        // Update the value in wp-config.php
        $wpConfigPath = ABSPATH . 'wp-config.php';
        $wpConfigContent = file_get_contents($wpConfigPath);
        $updatedContent = preg_replace("/define\('PREVIOUS_DATE', '.*?'\);/i", "define('PREVIOUS_DATE', '{$previous_date}');", $wpConfigContent);

        if ($wpConfigContent !== $updatedContent) {
            // Write the updated content back to wp-config.php
            file_put_contents($wpConfigPath, $updatedContent);
        }

        echo '<div class="notice notice-success"><p>Date saved successfully.</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>Custom Settings</h1>
        <form method="post" action="">
            <label for="previous_date">Previous Date:</label>
            <input type="date" id="previous_date" name="previous_date" value="<?php echo esc_attr($previous_date); ?>">
            <p class="description">Enter the previous date.</p>
            <p><input type="submit" name="save_date" class="button button-primary" value="Save Date"></p>
        </form>
    </div>
    <?php
}

add_action('admin_enqueue_scripts', 'enqueue_custom_settings_page');
function enqueue_custom_settings_page() {
    $screen = get_current_screen();
    if ($screen->id === 'tools_page_custom-settings') {
        wp_enqueue_style('wp-admin-custom', get_template_directory_uri() . '/custom-settings.css');
    }
}

*/
// Function to modify the post's date when creating new pages or posts
function set_previous_date($data, $postarr) {
    // Get the post's type
    $post_type = $data['post_type'];

    // Check if it's a page or post and if it's a new one
    if (($post_type == 'page' || $post_type == 'post') && $postarr['ID'] == 0) {
        // Get the defined previous date
        $previous_date = constant('PREVIOUS_DATE');

        // Set the post's date to the previous date
        $data['post_date'] = $previous_date;
        $data['post_date_gmt'] = get_gmt_from_date($previous_date);
    }

    return $data;
}
add_filter('wp_insert_post_data', 'set_previous_date', 10, 2);

// Function to customize the upload folder structure
function custom_upload_dir($uploads) {
    // Get the defined previous date
    $previous_date = constant('PREVIOUS_DATE');

    // Extract the year and month from the previous date
    $previous_year_month = date('Y/m', strtotime($previous_date));

    // Get the current year and month
    $current_year_month = date('Y/m');

    // Replace the current year and month in the path with the previous year and month
    $uploads['subdir'] = str_replace($current_year_month, $previous_year_month, $uploads['subdir']);
    $uploads['path'] = $uploads['basedir'] . $uploads['subdir'];
    $uploads['url'] = $uploads['baseurl'] . $uploads['subdir'];

    return $uploads;
}
add_filter('upload_dir', 'custom_upload_dir');