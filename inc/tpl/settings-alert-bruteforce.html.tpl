
<div class="gddysec-boxshadow">
    <h3>Password Guessing Brute Force Attacks</h3>

    <div class="inside">
        <p>
            Password guessing brute force attacks are very common against web sites and web
            servers. They are one of the most common vectors used to compromise web sites.
            The process is very simple and the attackers basically try multiple combinations
            of usernames and passwords until they find one that works. Once they get in,
            they can compromise the web site with malware, spam , phishing or anything else
            they want.
        </p>

        <p>
            More info at <a href="https://kb.sucuri.net/definitions/attacks/brute-force/password-guessing"
            target="_blank">Sucuri KB &mdash; Password Guessing Brute Force Attacks</a>.
        </p>

        <div class="gddysec-inline-alert-warning">
            <p>This option overrides the <em>"Alerts Per Hour"</em> setting.</p>
        </div>

        <form action="%%GDDYSEC.URL.Settings%%#notifications" method="post">
            <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />

            <div class="gddysec-input-group">
                <label>Consider Brute-Force Attack After:</label>
                <select name="gddysec_maximum_failed_logins">
                    %%%GDDYSEC.Settings.BruteForce%%%
                </select>
                <button type="submit" class="button-primary">Save</button>
            </div>
        </form>
    </div>
</div>
