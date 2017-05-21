
<div class="gddysec-panel">
    <h3 class="gddysec-title">Malware Scan Target</h3>

    <div class="inside">
        <p>The remote malware scanner provided by the plugin is powered by <a href="https://sitecheck.sucuri.net/" target="_blank" rel="noopener">Sucuri SiteCheck</a>, a service that takes a publicly accessible URL and scans it for malicious code. If your website is not visible to the Internet, for example, if it is hosted in a local development environment or a restricted network, the scanner will not be able to work on it. Additionally, if the website was installed in a non-standard directory the scanner will report a "404 Not Found" error. You can use this option to change the URL that will be scanned.</p>

        <div class="gddysec-hstatus gddysec-hstatus-2">
            <span>Malware Scan Target &mdash; <a href="https://sitecheck.sucuri.net/results/%%GDDYSEC.SiteCheck.Target%%" target="_blank" rel="noopener">https://sitecheck.sucuri.net/results/%%GDDYSEC.SiteCheck.Target%%</a></span>
        </div>

        <form action="%%GDDYSEC.URL.Settings%%#apiservice" method="post">
            <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />
            <fieldset class="gddysec-clearfix">
                <label>Malware Scan Target:</label>
                <input type="text" name="gddysec_sitecheck_target" />
                <button type="submit" class="button button-primary">Submit</button>
            </fieldset>
        </form>
    </div>
</div>
