
<div class="gddysec-panel">
    <h3 class="gddysec-title">Post-Type Alerts</h3>

    <div class="inside">
        <div class="gddysec-inline-alert-error gddysec-%%GDDYSEC.IgnoreRules.ErrorVisibility%%">
            <p>It seems that you disabled the email alerts for <b>new site content</b>, this panel is intended to provide a way to ignore specific events in your site and with that the alerts reported to your email. Since you have deactivated the <b>new site content</b> alerts, this panel will be disabled too.</p>
        </div>

        <p>This is a list of registered <a href="https://codex.wordpress.org/Post_Types" target="_blank" rel="noopener">Post Types</a>. You will receive an email alert when a custom page or post associated to any of these types is created or updated. Some of these are created by WordPress but the majority are created by 3rd-party plugins and themes to extend functionality from WordPress. If you don't want to receive alerts for certain posts you can stop them from here.</p>

        <p>If you are receiving alerts for post types that are not listed here it may be because the theme or plugin that is making these changes is registering the custom post-type on runtime, in this case our plugin will not be able to detect these changes and consequently you will not be able to ignore those alerts. However, if you know the unique identifier of the post-type you can type it in the form bellow and our plugin will do its best to skip the alerts associated to that.</p>

        <form action="%%GDDYSEC.URL.Settings%%#alerts" method="post">
            <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />
            <input type="hidden" name="gddysec_ignorerule_action" value="add">

            <fieldset class="gddysec-clearfix">
                <label>Stop Alerts For This Post-Type:</label>
                <input type="text" name="gddysec_ignorerule" placeholder="e.g. unique_post_type_id" />
                <button type="submit" class="button button-primary">Submit</button>
            </fieldset>
        </form>

        <hr>

        <table class="wp-list-table widefat gddysec-table gddysec-settings-ignorerules">
            <thead>
                <tr>
                    <th>Ignored At</th>
                    <th>Ignored</th>
                    <th>Post-Type</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>

            <tbody>
                %%%GDDYSEC.IgnoreRules.PostTypes%%%
            </tbody>
        </table>
    </div>
</div>
