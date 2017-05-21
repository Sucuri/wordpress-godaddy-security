
<div class="gddysec-panel">
    <h3 class="gddysec-title">Password Guessing Brute Force Attacks</h3>

    <div class="inside">
        <p><a href="https://kb.sucuri.net/definitions/attacks/brute-force/password-guessing" target="_blank" rel="noopener">Password guessing brute force attacks</a> are very common against web sites and web servers. They are one of the most common vectors used to compromise web sites. The process is very simple and the attackers basically try multiple combinations of usernames and passwords until they find one that works. Once they get in, they can compromise the web site with malware, spam , phishing or anything else they want.</p>

        <form action="%%GDDYSEC.URL.Settings%%#alerts" method="post">
            <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />
            <fieldset class="gddysec-clearfix">
                <label>Consider Brute-Force Attack After:</label>
                <select name="gddysec_maximum_failed_logins">
                    %%%GDDYSEC.Alerts.BruteForce%%%
                </select>
                <button type="submit" class="button button-primary">Submit</button>
            </fieldset>
        </form>
    </div>
</div>
