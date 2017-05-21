
<div class="gddysec-panel">
    <h3 class="gddysec-title">Security Alerts</h3>

    <div class="inside">
        <div class="gddysec-inline-alert-error gddysec-%%GDDYSEC.Alerts.NoAlertsVisibility%%">
            <p>You have installed a plugin or theme that is not fully compatible with our plugin, some of the security alerts (like the successful and failed logins) will not be sent to you. To prevent an infinite loop while detecting these changes in the website and sending the email alerts via a custom SMTP plugin, we have decided to stop any attempt to send the emails to prevent fatal errors.</p>
        </div>

        <form action="%%GDDYSEC.URL.Settings%%#alerts" method="post">
            <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />

            <table class="wp-list-table widefat gddysec-table gddysec-settings-alerts">
                <thead>
                    <tr>
                        <td id="cb" class="manage-column column-cb check-column">
                            <label class="screen-reader-text" for="cb-select-all-1">Select All</label>
                            <input id="cb-select-all-1" type="checkbox">
                        </td>
                        <th class="manage-column">Event</th>
                    </tr>
                </thead>

                <tbody>
                    %%%GDDYSEC.Alerts.Events%%%
                </tbody>
            </table>

            <div class="gddysec-recipient-form">
                <button type="submit" name="gddysec_save_alert_events" class="button button-primary">Submit</button>
            </div>
        </form>
    </div>
</div>
