
<div class="gddysec-panel">
    <h3 class="gddysec-title">Reverse Proxy</h3>

    <div class="inside">
        <p>The event monitor uses the API address of the origin of the request to track the actions, the plugin uses two methods to retrieve this: the main method uses the global server variable <em>Remote-Addr</em> available in most modern web servers, an alternative method uses custom HTTP headers <em>(which are unsafe by default)</em>. You should not worry about this option unless you know what a reverse proxy is. Services like the <a href="https://www.godaddy.com/help/set-up-my-web-application-firewall-waf-and-cdn-26813" target="_blank" rel="noopener">Web Application Firewall</a> &mdash; once active &mdash; forces the network traffic to pass through them to filter any security threat that may affect the original server. A side effect of this is that the real IP address is no longer available in the global server variable <em>REMOTE-ADDR</em> but in a custom HTTP header with a name provided by the service.</p>

        <div class="gddysec-hstatus gddysec-hstatus-2">
            <span>Reverse Proxy &mdash; %%GDDYSEC.ReverseProxyStatus%%</span>

            <form action="%%GDDYSEC.URL.Settings%%#advanced" method="post">
                <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />
                <input type="hidden" name="gddysec_revproxy" value="%%GDDYSEC.ReverseProxySwitchValue%%" />
                <button type="submit" class="button button-primary">%%GDDYSEC.ReverseProxySwitchText%%</button>
            </form>
        </div>
    </div>
</div>
