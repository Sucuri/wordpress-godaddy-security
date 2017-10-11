
<div class="gddysec-panel">
    <h3 class="gddysec-title">Reset Security Logs and Settings</h3>

    <div class="inside">
        <p>This action will trigger the deactivation / uninstallation process of the plugin. All local security logs, hardening and settings will be deleted. Notice that the security logs stored in the API service will not be deleted, this is to prevent tampering from a malicious user. You can request a new API key if you want to start from scratch.</p>

        <form action="%%GDDYSEC.URL.Settings%%#general" method="post">
            <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />
            <p>
                <label>
                    <input type="hidden" name="gddysec_process_form" value="0" />
                    <input type="checkbox" name="gddysec_process_form" value="1" />
                    <span>I understand that this operation can not be reverted.</span>
                </label>
            </p>
            <button type="submit" name="gddysec_reset_options" class="button button-primary">Submit</button>
        </form>
    </div>
</div>
