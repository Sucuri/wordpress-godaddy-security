
<div class="gddysec-boxshadow">
    <h3>Alert Events</h3>

    <div class="inside">
        <p>
            Configure the alert settings to your needs, and make sure to read the purpose of
            each option below otherwise you will end up enabling and/or disabling things
            that will affect your personal inbox. If you experience issues with one or more
            of these options revert them to their original state.
        </p>

        <div class="gddysec-inline-alert-warning">
            <p>
                Enabling the alerts for failed login attempts may become an indirect mail spam
                attack as you will receive tons of emails if your website is victim of a brute
                force attack. Disable this option and enable the brute force attack reports to
                get a summary of all the failed logins detected each hour.
            </p>
        </div>

        <form action="%%GDDYSEC.URL.Settings%%#notifications" method="post">
            <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />

            <table class="wp-list-table widefat gddysec-table gddysec-settings-notifications">
                <thead>
                    <tr>
                        <th class="manage-column column-cb check-column">
                            <label class="screen-reader-text" for="cb-select-all-1">Select All</label>
                            <input id="cb-select-all-1" type="checkbox">
                        </th>
                        <th class="manage-column">Event Description</th>
                    </tr>
                </thead>

                <tbody>
                    %%%GDDYSEC.Settings.Events%%%
                </tbody>
            </table>

            <div class="gddysec-form-buttons">
                <button type="submit" name="gddysec_save_alert_events" class="button-primary">Save</button>
            </div>
        </form>
    </div>
</div>
