
%%%GDDYSEC.ModalWhenAPIRegistered%%%

%%%GDDYSEC.ModalForApiKeyRecovery%%%

<div class="gddysec-panel">
    <h3 class="gddysec-title">API Key</h3>

    <div class="inside">
        <p>An API key is required to prevent attackers from deleting audit logs that can help you investigate and recover after a hack, and allows the plugin to display statistics. By generating an API key, you agree that Sucuri will collect and store anonymous data about your website. We take your privacy seriously.</p>

        <div class="gddysec-inline-alert-error gddysec-%%GDDYSEC.InvalidDomainVisibility%%">
            <p>Your domain <code>%%GDDYSEC.CleanDomain%%</code> does not seems to have a DNS <code>A</code> record so it will be considered as <em>invalid</em> by the API interface when you request the generation of a new key. Adding <code>www</code> at the beginning of the domain name may fix this issue. If you do not understand what is this then send an email to our support team requesting the key.</p>
        </div>

        <div class="gddysec-%%GDDYSEC.APIKey.RecoverVisibility%%">
            <div class="gddysec-hstatus gddysec-hstatus-0">
                <div class="gddysec-monospace">API Key: %%GDDYSEC.APIKey%%</div>
                <form action="%%GDDYSEC.URL.Settings%%#general" method="post">
                    <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />
                    <button type="submit" name="gddysec_recover_key" class="button button-primary">Recover Via E-mail</button>
                </form>
            </div>

            <p>If you do not have access to the administrator email, you can reinstall the plugin. The API key is generated using an administrator email and the domain of the website.</p>
        </div>

        <div class="gddysec-hstatus gddysec-hstatus-1 gddysec-%%GDDYSEC.APIKey.RemoveVisibility%%">
            <div class="gddysec-monospace">API Key: %%GDDYSEC.APIKey%%</div>
            <form action="%%GDDYSEC.URL.Settings%%#general" method="post">
                <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />
                <button type="submit" name="gddysec_remove_api_key" class="button button-primary">Delete</button>
            </form>
        </div>
    </div>
</div>
