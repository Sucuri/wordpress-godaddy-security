
<p>
    An API Key is required to activate additional tools. The keys are free and you can
    virtually generate an unlimited number of them as long as the domain name and email
    address are different. The key is used to communicate with the API Service that
    powers the audit logs and integrity checks.
</p>

<p>
    <b>NOTE:</b> Depending on the content of your website, sensitive information may be
    sent to the API Service without previous notice. Please do not generate the API Key
    if you do not agree on sending this data to our servers.
</p>

<div class="gddysec-inline-alert-info">
    <p><b>Need help?</b> Contact our support team at <a href="mailto:info@sucuri.net">info@sucuri.net</a>.</p>
</div>

<form action="%%GDDYSEC.URL.Settings%%" method="post" class="gddysec-setup-form">
    <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />
    <input type="hidden" name="gddysec_plugin_api_key" value="1" />

    <div class="gddysec-setup-form-details">
        <div class="gddysec-form-field">
            <label>Website:</label>
            <span class="gddysec-monospace">%%GDDYSEC.CleanDomain%%</span>
        </div>

        <div class="gddysec-form-field">
            <label>Admin E-mail:</label>
            <select name="gddysec_setup_user">
                %%%GDDYSEC.AdminEmails%%%
            </select>
        </div>
    </div>

    <div class="gddysec-setup-form-buttons">
        <button type="submit" class="button button-hero button-primary">Proceed</button>
        <button type="button" class="button button-hero button-secondary gddysec-modal-close">Cancel</button>
    </div>
</form>
