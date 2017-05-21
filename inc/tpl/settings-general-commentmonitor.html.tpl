
<div class="gddysec-boxshadow">
    <h3>User Comment Monitor</h3>

    <div class="inside">
        <p>
            User comments are the main source of spam in WordPress websites, this option
            enables the monitoring of data sent via the comment forms loaded in every page
            and post. Remember that the plugin sends this information to the Sucuri servers
            so if you do not agree with this you must keep this option disabled. Among the
            data included in the report for each comment there are identifiers of the post
            and user account <em>(usually null for guest comments)</em>, the IP address of
            the author, the email address of the author, the user-agent of the web browser
            used by the author to create the comment, the current date, the status which
            usually falls under the category of not approved, and the message itself.
        </p>

        <div class="gddysec-inline-alert-info">
            <p>
                We also use this information in an anonymous way to generate <a target="_blank"
                href="https://sucuri.net/security-reports/brute-force/">statistics</a> of usage
                that help us improve our service.
            </p>
        </div>

        <div class="gddysec-hstatus">
            <span>User Comment Monitor is %%GDDYSEC.CommentMonitorStatus%%</span>

            <form action="%%GDDYSEC.URL.Settings%%" method="post">
                <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />
                <input type="hidden" name="gddysec_comment_monitor" value="%%GDDYSEC.CommentMonitorSwitchValue%%" />
                <button type="submit" class="button-primary %%GDDYSEC.CommentMonitorSwitchCssClass%%">
                    %%GDDYSEC.CommentMonitorSwitchText%%
                </button>
            </form>
        </div>
    </div>
</div>
