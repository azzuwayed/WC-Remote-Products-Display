<div class="wrap wcrpd-settings-wrap">
    <header>
        <h2><i class="dashicons dashicons-admin-generic"></i> <?php _e('WC Remote Products Display', 'wcrpd'); ?></h2>
    </header>

    <nav id="wcrpd-tabs">
        <button class="tablink" data-tab="API Connection" onclick="openTab('API Connection', this)">
            <i class="dashicons dashicons-admin-links"></i> <?php _e('API Connection', 'wcrpd'); ?>
        </button>
        <button class="tablink" data-tab="Display" onclick="openTab('Display', this)">
            <i class="dashicons dashicons-admin-appearance"></i> <?php _e('Display Settings', 'wcrpd'); ?>
        </button>
        <button class="tablink" data-tab="Debug" onclick="openTab('Debug', this)">
            <i class="dashicons dashicons-admin-tools"></i> <?php _e('Debug Settings', 'wcrpd'); ?>
        </button>
    </nav>

    <section id="API Connection" class="tabcontent active-content">
        <form id="api-connection-settings-form" class="wcrpd-mn-top" method="post" action="">
            <input type="hidden" name="form_id" value="api-connection-settings-form">
            <div class="form-group">
                <label for="wcrpd_api_woo_url"><?php _e('Remote WooCommerce URL:', 'wcrpd'); ?></label>
                <input type="text" id="wcrpd_api_woo_url" name="wcrpd_api_woo_url" value="<?php echo esc_attr(get_option('wcrpd_api_woo_url', '')); ?>" required>
                <div class="error-feedback" data-for="wcrpd_api_woo_url"></div>
                <div class="server-error-feedback" data-for="wcrpd_api_woo_url"></div> <!-- Server-side Error Message -->
            </div>

            <div class="form-group">
                <label for="wcrpd_api_woo_url"><?php _e('Consumer Key:', 'wcrpd'); ?></label>
                <input type="text" id="wcrpd_api_woo_ck" name="wcrpd_api_woo_ck" value="<?php echo esc_attr(get_option('wcrpd_api_woo_ck', '')); ?>" required>
                <div class="error-feedback" data-for="wcrpd_api_woo_ck"></div>
                <div class="server-error-feedback" data-for="wcrpd_api_woo_ck"></div> <!-- Server-side Error Message -->
            </div>

            <div class="form-group">
                <label for="wcrpd_api_woo_url"><?php _e('Consumer Secret:', 'wcrpd'); ?></label>
                <input type="text" id="wcrpd_api_woo_cs" name="wcrpd_api_woo_cs" value="<?php echo esc_attr(get_option('wcrpd_api_woo_cs', '')); ?>" required>
                <div class="error-feedback" data-for="wcrpd_api_woo_cs"></div>
                <div class="server-error-feedback" data-for="wcrpd_api_woo_cs"></div> <!-- Server-side Error Message -->
            </div>

            <div class="form-group">
                <?php
                $isConnected = get_option('wcrpd_api_connection_status', false);
                if ($isConnected) {
                    echo '<span class="green-color">' . esc_html__('API Connection is successful', 'wcrpd') . '</span>';
                } else {
                    echo '<span class="green-red">' . esc_html__('API Connection failed', 'wcrpd') . '</span>';
                }
                ?>
            </div>



            <!-- Hidden field for nonce -->
            <input type="hidden" name="wcrpd_save_api_nonce" value="<?php echo $api_nonce; ?>">

            <div class="wcrpd-save-container">
                <input type="submit" name="save" value="<?php _e('Save Changes', 'wcrpd'); ?>" class="button button-primary" />
                <div class="wcrpd-right-buttons">
                </div>
            </div>
            <div><span class="global-error-message"></span></div>
        </form>
    </section>

    <section id="Display" class="tabcontent">
        <form id="display-settings-form" method="post" action="">
            <input type="hidden" name="form_id" value="display-settings-form">
            <fieldset>
                <legend><?php _e('Product Card Elements', 'wcrpd'); ?></legend>
                <p class="description"><strong> <?php _e('Choose which product elements you would like to display on the card.', 'wcrpd'); ?> </strong></p>
                <?php
                // Array of checkbox names and translatable labels
                $checkboxes = [
                    'wcrpd_display_image' => __('Image', 'wcrpd'),
                    'wcrpd_display_name' => __('Name', 'wcrpd'),
                    'wcrpd_display_category' => __('Category', 'wcrpd'),
                    'wcrpd_display_price' => __('Price', 'wcrpd'),
                    'wcrpd_display_description' => __('Description', 'wcrpd'),
                    'wcrpd_display_button' => __('Button', 'wcrpd'),
                ];

                // Loop to generate checkboxes
                foreach ($checkboxes as $name => $label) {
                    generate_checkbox($name, $label, get_option($name, ''));
                }
                ?>
            </fieldset>
            <fieldset>
                <legend><?php _e('Global Display Settings', 'wcrpd'); ?></legend>

                <label class="wcrpd-mn-top-5">
                    <?php _e('Count limit (beta)', 'wcrpd'); ?>
                    <p class="description">
                        <?php _e('How many products to display per shortcode. This limit must be equal or greater than any shortcode, and not greater than available products on the remote website, otherwise you will get an error.', 'wcrpd'); ?>

                    </p>
                    <input type="text" name="wcrpd_display_count_limit" value="<?php echo esc_attr(get_option('wcrpd_display_count_limit', '')); ?>">
                </label>
                <hr>

                <!-- Filtered Categories Checkbox -->
                <label class="wcrpd-mn-top">
                    <?php generate_checkbox('wcrpd_display_filtered_categories', __('Filtered categories (beta)', 'wcrpd'), get_option('wcrpd_display_filtered_categories', '')); ?>
                </label>
                <p class="description red-color"><?php _e('Warning: when this option is enabled, Category IDs become mandatory either in global or in shortcodes, otherwise you will get an error.', 'wcrpd'); ?></p>

                <label class="wcrpd-mn-top">
                    <?php _e('Fetched Categories', 'wcrpd'); ?>
                </label>
                <p class="description"><strong><?php _e('Here you should see the fetched categories from your remote website as [Category Name: Category ID]:', 'wcrpd'); ?></strong></p>
                <?php echo print_saved_categories(); ?>

                <label class="wcrpd-mn-top">
                    <?php _e('Category IDs', 'wcrpd'); ?>
                    <p class="description"><?php _e('Use comma-separated IDs of the categories you want to include exclusivly. "Filtered categories" option must be enabled for this to work.', 'wcrpd'); ?></p>
                    <input type="text" name="wcrpd_display_filtered_categories_ids" value="<?php echo esc_attr(get_option('wcrpd_display_filtered_categories_ids', '')); ?>">
                </label>

                <!-- This section needs development to fetch the categories in the mulitple options box 
                <label>
                    Multiple Select:
                    <p class="description">Hold down the Ctrl (Windows) or Command (Mac) button to select multiple options.</p>
                    <select multiple name="multiple_select[]" size="5">
                        <option value="multi_option1">Multi Option 1</option>
                        <option value="multi_option2">Multi Option 2</option>
                        <option value="multi_option3">Multi Option 3</option>
                        <option value="multi_option4">Multi Option 4</option>
                        <option value="multi_option5">Multi Option 5</option>
                    </select>
                </label>
                 This section needs development to fetch the categories in the mulitple options box -->

                <hr>
                <legend class="wcrpd-mn-top"><?php _e('Instructions:', 'wcrpd'); ?></legend>
                <p class="description">&#x2022; <?php _e('You can override global settings with shortcode attributes.', 'wcrpd'); ?></p>
                <p class="description">&#x2022; <?php _e('If attributes are not set, they will default to the global settings.', 'wcrpd'); ?></p>

                <p class="description"><?php _e('Example: <b>[wcrpd count_limit="6"]</b> will display a maximum of 6 products.', 'wcrpd'); ?></p>
                <p class="description"><?php _e('Example: <b>[wcrpd filtered_categories="1,2,3"]</b> will display products from these categories.', 'wcrpd'); ?></p>
                <p class="description"><?php _e('Example: <b>[wcrpd count_limit="6" filtered_categories="1,2,3"]</b> will apply both.', 'wcrpd'); ?></p>
            </fieldset>

            <!-- Hidden field for nonce -->
            <input type="hidden" name="wcrpd_save_display_nonce" value="<?php echo $display_nonce; ?>">

            <div class="wcrpd-save-container">
                <!-- Save Button -->
                <input type="submit" name="save" value="<?php _e('Save Changes', 'wcrpd'); ?>" class="button button-primary" />
                <div class="wcrpd-right-buttons">
                </div>
            </div>
            <div><span class="global-error-message"></span></div>
        </form>
    </section>

    <section id="Debug" class="tabcontent">
        <!-- Form for Advanced Settings -->
        <form id="debug-settings-form" method="post" action="">
            <input type="hidden" name="form_id" value="debug-settings-form">


            <fieldset>
                <legend><?php _e('Debug Mode', 'wcrpd'); ?></legend>
                <label>
                    <p class="description"><?php _e('Only enable on development environment. Wordpress debug must be enabled for this to work. The debug includes errors and trivial data, and will make frontend errors more detailed.', 'wcrpd'); ?></p>
                    <?php generate_checkbox('wcrpd_debug_enable_logging', __('Enabled', 'wcrpd'), get_option('wcrpd_debug_enable_logging', '')); ?>
                </label>
            </fieldset>

            <fieldset>
                <legend><?php _e('Advanced Settings (beta)', 'wcrpd'); ?></legend>
                <p class="description red-color strong"><?php _e('Warning: adjusting these settings without understanding their impact can result in poor performance.', 'wcrpd'); ?></p>

                <label class="wcrpd-mn-top">
                    <?php _e('Cache Duration', 'wcrpd'); ?>
                    <p class="description"><?php _e('Frequent API calls may overload the server. Long cache might serve outdated data. Cache is shared between users and saved in your database. (default is 6 hours = 21600 seconds)', 'wcrpd'); ?>
                    </p>
                    <input type="text" name="wcrpd_debug_cache_duration" value="<?php echo esc_attr(get_option('wcrpd_debug_cache_duration', '')); ?>">
                </label>
                <label class="wcrpd-mn-top">
                    <?php _e('Timeout Duration', 'wcrpd'); ?>
                    <p class="description"> <?php _e('Short timeouts may result in errors, while long timeouts can slow down the user experience. (default is 10 seconds)', 'wcrpd'); ?>
                    </p>
                    <input type="text" name="wcrpd_debug_timeout" value="<?php echo esc_attr(get_option('wcrpd_debug_timeout', '')); ?>">
                </label>
                <label class="wcrpd-mn-top">
                    <?php _e('Rate Limit', 'wcrpd'); ?>
                    <p class="description"><?php _e('Higher limits can exceed the API\'s allowance, leading to a ban. (default is 30 per minute)', 'wcrpd'); ?>
                    </p>
                    <input type="text" name="wcrpd_debug_rate_limit" value="<?php echo esc_attr(get_option('wcrpd_debug_rate_limit', '')); ?>">
                </label>
            </fieldset>

            <!-- Hidden field for nonce -->
            <input type="hidden" name="wcrpd_save_debug_nonce" value="<?php echo $debug_nonce; ?>">

            <div class="wcrpd-save-container">
                <input type="submit" name="save" value="<?php _e('Save Changes', 'wcrpd'); ?>" class="button button-primary" />
                <div class="wcrpd-right-buttons">
                    <input type="submit" name="reset" value="<?php _e('Reset Plugin', 'wcrpd'); ?>" class="button button-link-delete" />
                    <input type="submit" name="flush" value="<?php _e('Flush Cache', 'wcrpd'); ?>" class="button button-secondary" />
                </div>
            </div>
            <div><span class="global-error-message"></span></div>
        </form>
    </section>
</div>