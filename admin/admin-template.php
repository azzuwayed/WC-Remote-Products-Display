<div class="wrap woorpd-settings-wrap">
    <header>
        <h2><i class="dashicons dashicons-admin-generic"></i> WooCommerce Remote Products Display</h2>
    </header>

    <nav id="woorpd-tabs">
        <button class="tablink" data-tab="API Connection" onclick="openTab('API Connection', this)">
            <i class="dashicons dashicons-admin-links"></i> API Connection
        </button>
        <button class="tablink" data-tab="Display" onclick="openTab('Display', this)">
            <i class="dashicons dashicons-admin-appearance"></i> Display Settings
        </button>
        <button class="tablink" data-tab="Debug" onclick="openTab('Debug', this)">
            <i class="dashicons dashicons-admin-tools"></i> Debug Settings
        </button>
    </nav>

    <section id="API Connection" class="tabcontent active-content">
        <!-- Form for API Connection Settings -->
        <form id="api-connection-settings-form">
            <div class="form-group">
                <label for="woorpd_api_woo_url">Remote WooCommerce URL:</label>
                <input type="text" id="woorpd_api_woo_url" name="woorpd_api_woo_url" value="<?php echo esc_attr(get_option('woorpd_api_woo_url')); ?>" required>
                <div class="error-feedback" data-for="woorpd_api_woo_url"></div>
                <div class="server-error-feedback" data-for="woorpd_api_woo_url"></div> <!-- Server-side Error Message -->
            </div>

            <div class="form-group">
                <label for="woorpd_api_woo_ck">Consumer Key:</label>
                <input type="text" id="woorpd_api_woo_ck" name="woorpd_api_woo_ck" value="<?php echo esc_attr(get_option('woorpd_api_woo_ck')); ?>" required>
                <div class="error-feedback" data-for="woorpd_api_woo_ck"></div>
                <div class="server-error-feedback" data-for="woorpd_api_woo_ck"></div> <!-- Server-side Error Message -->
            </div>

            <div class="form-group">
                <label for="woorpd_api_woo_cs">Consumer Secret:</label>
                <input type="text" id="woorpd_api_woo_cs" name="woorpd_api_woo_cs" value="<?php echo esc_attr(get_option('woorpd_api_woo_cs')); ?>" required>
                <div class="error-feedback" data-for="woorpd_api_woo_cs"></div>
                <div class="server-error-feedback" data-for="woorpd_api_woo_cs"></div> <!-- Server-side Error Message -->
            </div>

            <input type="hidden" name="nonce" value="<?php echo $nonce; ?>">

            <div class="woorpd-save-container">
                <button type="submit" class="save-btn">
                    <span class="btn-label">Save</span>
                    <span class="dashicons dashicons-update save-spinner"></span>
                    <span class="dashicons dashicons-yes save-success"></span>
                </button>
                <div class="woorpd-right-buttons">
                    <button type="button" class="right-btn" id="woorpd-reset-settings-button"><span class="red-color">Reset Settings</span></button>
                </div>
            </div>

            <div><span class="global-error-message"></span></div>
        </form>

    </section>

    <section id="Display" class="tabcontent">
        <form id="display-settings-form">
            <fieldset>
                <legend>Product Card Elements</legend>
                <p class="description">Select which product elements you would like to display on the card.</p>
                <!-- Image Checkbox -->
                <label for="woorpd_display_image" class="woordp-mn-top">
                    <input type="checkbox" id="woorpd_display_image" name="woorpd_display_image" value="1" <?php checked(1, get_option('woorpd_display_image'), true); ?>>
                    Image
                </label>
                <!-- Name Checkbox -->
                <label for="woorpd_display_name" class="woordp-mn-top">
                    <input type="checkbox" id="woorpd_display_name" name="woorpd_display_name" value="1" <?php checked(1, get_option('woorpd_display_name'), true); ?>>
                    Name
                </label>
                <!-- Category Checkbox -->
                <label for="woorpd_display_category" class="woordp-mn-top">
                    <input type="checkbox" id="woorpd_display_category" name="woorpd_display_category" value="1" <?php checked(1, get_option('woorpd_display_category'), true); ?>>
                    Category
                </label>
                <!-- Price Checkbox -->
                <label for="woorpd_display_price" class="woordp-mn-top">
                    <input type="checkbox" id="woorpd_display_price" name="woorpd_display_price" value="1" <?php checked(1, get_option('woorpd_display_price'), true); ?>>
                    Price
                </label>
                <!-- Description Checkbox -->
                <label for="woorpd_display_description" class="woordp-mn-top">
                    <input type="checkbox" id="woorpd_display_description" name="woorpd_display_description" value="1" <?php checked(1, get_option('woorpd_display_description'), true); ?>>
                    Description
                </label>
                <!-- Button Checkbox -->
                <label for="woorpd_display_button" class="woordp-mn-top">
                    <input type="checkbox" id="woorpd_display_button" name="woorpd_display_button" value="1" <?php checked(1, get_option('woorpd_display_button'), true); ?>>
                    Button
                </label>
            </fieldset>
            <fieldset>
            <legend>Global Display Settings</legend>
            
            <label class="woordp-mn-top">
            Count limit
            <p class="description">How many products to display per shortcode. This limit must be equal or greater than any shortcode.</p>
            <input type="text" name="woorpd_display_count_limit" value="<?php echo esc_attr(get_option('woorpd_display_count_limit')); ?>">
            </label>
            <hr>

            <!-- Filtered Categories Checkbox -->
            <label for="woorpd_display_filtered_categories" class="woordp-mn-top">
                <input type="checkbox" id="woorpd_display_filtered_categories" name="woorpd_display_filtered_categories" value="1" <?php checked(1, get_option('woorpd_display_filtered_categories'), true); ?>>
                Filtered categories
            </label>
            
            <label>
            <p class="description red-color">When enabled, "Category IDs" becomes mandatory in global or shortcodes, otherwise you will get an error.</p>
            </label>

            <!-- This part about Category IDs needs development -->
            <label class="woordp-mn-top">
            Category IDs
            <p class="description">Use comma-separated IDs of the categories you want to include exclusivly. "Filtered categories" must be enabled.</p>    
            <input type="text" name="woorpd_display_filtered_categories_ids" value="<?php echo esc_attr(get_option('woorpd_display_filtered_categories_ids')); ?>">
            </label>
            <!-- This part about Category IDs needs development -->

                <hr>
                <legend class="woordp-mn-top">Instructions:</legend>
                <p class="description">&#x2022; You can override glabal settings with shortcode attributes.</p>
                <p class="description">&#x2022; Attributes are optional, and will default to global settings if not set.</p>
                
                <p class="description">Example: <b>[woordp count_limit="6"]</b> will display a maximum of 6 products.</p>
                <p class="description">Example: <b>[woordp filtered_categories="1,2,3"]</b> will display products from these categories.</p>
                <p class="description">Example: <b>[woordp count_limit="6" filtered_categories="1,2,3"] will apply both.</b></p>
            </fieldset>

            <div class="woorpd-save-container">
                <button type="submit" class="save-btn">
                    <span class="btn-label">Save</span>
                    <span class="dashicons dashicons-update save-spinner"></span>
                    <span class="dashicons dashicons-yes save-success"></span>
                </button>
            </div>


        </form>
    </section>

    <section id="Debug" class="tabcontent">
        <!-- Form for Advanced Settings -->
        <form id="debug-settings-form">
            <!-- 1. Label heading and description -->
            <label>
                Advanced Option 1:
                <p class="description">Description for Advanced Option 1.</p>
                <input type="text" name="advanced_option1">
            </label>

            <!-- 2. Dropdown list with three options with Label heading and description -->
            <label>
                Select Option:
                <p class="description">Choose one of the options from the dropdown.</p>
                <select name="dropdown_option">
                    <option value="option1">Option 1</option>
                    <option value="option2">Option 2</option>
                    <option value="option3">Option 3</option>
                </select>
            </label>

            <!-- 3. Multiple select box with five options with Label heading and description -->
            <label>
                Multiple Select:
                <p class="description">Hold down the Ctrl (Windows) or Command (Mac) button to select multiple options.</p>
                <select multiple name="multiple_select[]" size="5">
                    <option value="multi_option1">Multi Option 1</option>
                    <option value="multi_option2">Multi Option 2</option>
                    <option value="multi_option3">Multi Option 3</option>
                    <option value="multi_option4">Multi Option 4</option>
                    <option value="multi_option5">Multi Option 5</option>
                    <option value="multi_option5">Multi Option 5</option>
                    <option value="multi_option5">Multi Option 5</option>
                    <option value="multi_option5">Multi Option 5</option>
                    <option value="multi_option5">Multi Option 5</option>
                    <option value="multi_option5">Multi Option 5</option>
                    <option value="multi_option5">Multi Option 5</option>
                    <option value="multi_option5">Multi Option 5</option>
                </select>
            </label>

            <!-- 4. Input field with Label heading and description -->
            <label>
                Advanced Input:
                <p class="description">Enter the value for the advanced input field.</p>
                <input type="text" name="advanced_input">
            </label>

            <!-- 5. Grouped checkboxes with Label heading and description -->
            <fieldset>
                <legend>Checkbox Group:</legend>
                <p class="description">Select one or more checkboxes from the group.</p>
                <label><input type="checkbox" name="checkbox_group[]" value="checkbox1"> Checkbox 1</label>
                <label><input type="checkbox" name="checkbox_group[]" value="checkbox2"> Checkbox 2</label>
                <label><input type="checkbox" name="checkbox_group[]" value="checkbox3"> Checkbox 3</label>
                <label><input type="checkbox" name="checkbox_group[]" value="checkbox4"> Checkbox 4</label>
                <label><input type="checkbox" name="checkbox_group[]" value="checkbox5"> Checkbox 5</label>
            </fieldset>

            <!-- Save button and another placeholder button -->
            <div class="woorpd-save-container">
                <button type="submit" class="save-btn">
                    <span class="btn-label">Save</span>
                    <span class="dashicons dashicons-update save-spinner"></span>
                    <span class="dashicons dashicons-yes save-success"></span>
                </button>
                <div class="woorpd-right-buttons">
                    <button type="button" class="right-btn"><span class="green-color">Flush Cache</span></button>
                </div>
            </div>
        </form>
    </section>

</div>