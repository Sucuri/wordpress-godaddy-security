
<div class="gddysec-panel">
    <h3 class="gddysec-title">API Service Communication</h3>

    <div class="inside">
        <p>Once the API key is generate the plugin will communicate with a remote API service that will act as a safe data storage for the audit logs generated when the website triggers certain events that the plugin monitors. If the website is hacked the attacker will not have access to these logs and that way you can investigate what was modified <em>(for malware infaction)</em> and/or how the malicious person was able to gain access to the website.</p>

        <div class="gddysec-inline-alert-error gddysec-%%GDDYSEC.ApiStatus.ErrorVisibility%%">
            <p>Disabling the API service communication will stop the event monitoring, consider to enable the <a href="%%GDDYSEC.URL.Settings%%#advanced">Log Exporter</a> to keep the monitoring working while the HTTP requests are ignored, otherwise an attacker may execute an action that will not be registered in the security logs and you will not have a way to investigate the attack in the future.</p>
        </div>

        <div class="gddysec-hstatus gddysec-hstatus-%%GDDYSEC.ApiStatus.StatusNum%%">
            <span>API Service Communication &mdash; %%GDDYSEC.ApiStatus.Status%% &mdash;</span>
            <span class="gddysec-monospace">%%GDDYSEC.ApiStatus.ServiceURL%%</span>
            <form action="%%GDDYSEC.URL.Settings%%#advanced" method="post">
                <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />
                <input type="hidden" name="gddysec_api_service" value="%%GDDYSEC.ApiStatus.SwitchValue%%" />
                <button type="submit" class="button button-primary">%%GDDYSEC.ApiStatus.SwitchText%%</button>
            </form>
        </div>
    </div>
</div>
