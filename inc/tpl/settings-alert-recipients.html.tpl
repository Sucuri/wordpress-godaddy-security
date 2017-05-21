
<div class="gddysec-boxshadow">
    <h3>Alert Recipients</h3>

    <div class="inside">
        <p>
            By default the plugin will send email alerts to the email address of the
            original user account created during the installation process of your website,
            you can change this adding a new address below and then deleting the old entry.
            Additionally, you are allowed to send a copy of the same alerts to other email
            addresses.
        </p>

        <form action="%%GDDYSEC.URL.Settings%%#notifications" method="post">
            <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />

            <div class="gddysec-input-group">
                <label>E-mail Address:</label>
                <input type="text" name="gddysec_recipient" class="input-text" placeholder="e.g. user@example.com" />
                <button type="submit" name="gddysec_save_recipient" class="button-primary">Add Recipient</button>
            </div>

            <table class="wp-list-table widefat gddysec-table">
                <thead>
                    <tr>
                        <th class="manage-column column-cb check-column">
                            <label class="screen-reader-text" for="cb-select-all-1">Select All</label>
                            <input id="cb-select-all-1" type="checkbox">
                        </th>
                        <th class="manage-column">E-mail Address</th>
                    </tr>
                </thead>

                <tbody>
                    %%%GDDYSEC.Settings.Recipients%%%
                </tbody>
            </table>

            <div class="gddysec-form-buttons">
                <button type="submit" name="gddysec_delete_recipients" class="button-primary button-danger">Delete Selected</button>
                <button type="submit" name="gddysec_debug_email" value="1" class="button-primary">Test Alert Delivery</button>
            </div>
        </form>
    </div>
</div>
