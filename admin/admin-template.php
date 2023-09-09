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
                <label for="api-woo-url">Remote WooCommerce URL:</label>
                <input type="text" id="api-woo-url" name="api-woo-url" value="<?php echo esc_attr(get_option('api-woo-url')); ?>" required>
                <div class="error-feedback" data-for="api-woo-url"></div>
            </div>

            <div class="form-group">
                <label for="api-woo-ck">Consumer Key:</label>
                <input type="text" id="api-woo-ck" name="api-woo-ck" value="<?php echo esc_attr(get_option('api-woo-ck')); ?>" required>
                <div class="error-feedback" data-for="api-woo-ck"></div>
            </div>

            <div class="form-group">
                <label for="api-woo-cs">Consumer Secret:</label>
                <input type="text" id="api-woo-cs" name="api-woo-cs" value="<?php echo esc_attr(get_option('api-woo-cs')); ?>" required>
                <div class="error-feedback" data-for="api-woo-cs"></div>
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
        <!-- Form for Display Settings -->
        <form id="advanced-settings-form">
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