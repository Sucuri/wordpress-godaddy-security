
<div class="gddysec-panel">
    <h3 class="gddysec-title">IP Address Discoverer</h3>

    <div class="inside">
        <p>IP address discoverer will use DNS lookups to automatically detect if the website is behind the <a href="https://www.godaddy.com/help/set-up-my-web-application-firewall-waf-and-cdn-26813" target="_blank" rel="noopener">Web Application Firewall</a> in which case will modify the global server variable <em>Remote-Addr</em> to set the real IP of the website's visitors. This check runs on every WordPress init action and that is why it may slow down your website as some hosting providers rely on slow DNS servers which makes the operation take more time than it should.</p>

        <div class="gddysec-hstatus gddysec-hstatus-2">
            <span>IP Address Discoverer &mdash; %%GDDYSEC.DnsLookupsStatus%%</span>

            <form action="%%GDDYSEC.URL.Settings%%" method="post">
                <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />
                <input type="hidden" name="gddysec_dns_lookups" value="%%GDDYSEC.DnsLookupsSwitchValue%%" />
                <button type="submit" class="button button-primary">%%GDDYSEC.DnsLookupsSwitchText%%</button>
            </form>
        </div>

        <form action="%%GDDYSEC.URL.Settings%%" method="post">
            <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />

            <fieldset class="gddysec-clearfix">
                <label>HTTP Header:</label>
                <select name="gddysec_addr_header">
                    %%%GDDYSEC.AddrHeaderOptions%%%
                </select>
                <button type="submit" class="button button-primary">Proceed</button>
            </fieldset>

            <div class="gddysec-hstatus gddysec-hstatus-2 gddysec-monospace">
                <div>Sucuri Firewall &mdash; %%GDDYSEC.IsUsingFirewall%%</div>
                <div>Website: %%GDDYSEC.WebsiteURL%%</div>
                <div>Top Level Domain: %%GDDYSEC.TopLevelDomain%%</div>
                <div>Hostname: %%GDDYSEC.WebsiteHostName%%</div>
                <div>IP Address (Hostname): %%GDDYSEC.WebsiteHostAddress%%</div>
                <div>IP Address (Username): %%GDDYSEC.RemoteAddress%% (%%GDDYSEC.RemoteAddressHeader%%)</div>
            </div>
        </form>
    </div>
</div>
