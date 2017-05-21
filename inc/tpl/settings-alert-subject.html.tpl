
<div class="gddysec-boxshadow">
    <h3>Alert Subject</h3>

    <div class="inside">
        <p>
            Format of the subject for the email alerts, by default the plugin will use the
            website name and the event identifier that is being reported, you can use this
            panel to include the IP address of that user that triggered the event and some
            additional data. You can create filters in your email client creating a custom
            email subject using the pseudo-tags shown below.
        </p>

        <form action="%%GDDYSEC.URL.Settings%%#notifications" method="post">
            <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />

            <ul class="gddysec-subject-formats">
                %%%GDDYSEC.Settings.Subject%%%

                <li>
                    <label>
                        <input type="radio" name="gddysec_email_subject" value="custom" %%GDDYSEC.Settings.CustomChecked%% />
                        <span>Custom format</span>
                        <input type="text" name="gddysec_custom_email_subject" value="%%GDDYSEC.Settings.CustomValue%%" />
                    </label>
                </li>
            </ul>

            <div class="gddysec-form-buttons">
                <button type="submit" class="button-primary">Save</button>
            </div>
        </form>
    </div>
</div>
