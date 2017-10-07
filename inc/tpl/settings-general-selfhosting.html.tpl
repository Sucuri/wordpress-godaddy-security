
<div class="gddysec-panel">
    <h3 class="gddysec-title">Log Exporter</h3>

    <div class="inside">
        <p>This option allows you to export the WordPress audit logs to a local log file that can be read by a SIEM or any log analysis software <em>(we recommend OSSEC)</em>. That will give visibility from within WordPress to complement your log monitoring infrastructure. <b>NOTE:</b> Do not use a publicly accessible file, you must use a file at least one level up the document root to prevent leaks of information.</p>

        <div class="gddysec-hstatus gddysec-hstatus-2 gddysec-%%GDDYSEC.SelfHosting.DisabledVisibility%%">
            <span>Log Exporter &mdash; %%GDDYSEC.SelfHosting.Status%%</span>
        </div>

        <div class="gddysec-hstatus gddysec-hstatus-2 gddysec-monitor-fpath gddysec-%%GDDYSEC.SelfHosting.FpathVisibility%%">
            <span class="gddysec-monospace">%%GDDYSEC.SelfHosting.Fpath%%</span>
            <form action="%%GDDYSEC.URL.Settings%%#advanced" method="post">
                <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />
                <input type="hidden" name="gddysec_selfhosting_fpath" />
                <button type="submit" class="button button-primary">%%GDDYSEC.SelfHosting.SwitchText%%</button>
            </form>
        </div>

        <form action="%%GDDYSEC.URL.Settings%%#advanced" method="post">
            <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />
            <fieldset class="gddysec-clearfix">
                <label>File Path:</label>
                <input type="text" name="gddysec_selfhosting_fpath" />
                <button type="submit" class="button button-primary">Submit</button>
            </fieldset>
        </form>
    </div>
</div>
