
<div class="gddysec-panel">
    <h3 class="gddysec-title">Post-Type Alerts</h3>

    <div class="inside">
        <div class="gddysec-inline-alert-error gddysec-%%GDDYSEC.PostTypes.ErrorVisibility%%">
            <p>It seems that you disabled the email alerts for <b>new site content</b>, this panel is intended to provide a way to ignore specific events in your site and with that the alerts reported to your email. Since you have deactivated the <b>new site content</b> alerts, this panel will be disabled too.</p>
        </div>

        <p>This is a list of registered <a href="https://codex.wordpress.org/Post_Types" target="_blank" rel="noopener">Post Types</a>. You will receive an email alert when a custom page or post associated to any of these types is created or updated. If you don't want to receive one or more of these alerts, feel free to uncheck the boxes in the table below. If you are receiving alerts for post types that are not listed in this table, it may be because there is an add-on that that is generating a custom post-type on runtime, you will have to find out by yourself what is the unique ID of that post-type and type it in the form below. The plugin will do its best to ignore these alerts as long as the unique ID is valid.</p>

        <form action="%%GDDYSEC.URL.Settings%%#general" method="post">
            <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />
            <input type="hidden" name="gddysec_ignorerule_action" value="add">

            <fieldset class="gddysec-clearfix">
                <label>Stop Alerts For This Post-Type:</label>
                <input type="text" name="gddysec_ignorerule" placeholder="e.g. unique_post_type_id" />
                <button type="submit" class="button button-primary">Submit</button>
            </fieldset>
        </form>

        <hr>

        <button class="button button-primary gddysec-show-section" section="gddysec-ignorerules" on="Show Post-Types Table" off="Hide Post-Types Table">Show Post-Types Table</button>

        <div class="gddysec-hidden" id="gddysec-ignorerules">
            <hr>

            <form action="%%GDDYSEC.URL.Settings%%#general" method="post">
                <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />
                <input type="hidden" name="gddysec_ignorerule_action" value="batch">

                <table class="wp-list-table widefat gddysec-table gddysec-settings-ignorerules">
                    <thead>
                        <tr>
                            <td id="cb" class="manage-column column-cb check-column">
                                <label class="screen-reader-text" for="cb-select-all-1">Select All</label>
                                <input id="cb-select-all-1" type="checkbox">
                            </td>
                            <th class="manage-column">Post Type</th>
                            <th class="manage-column">Post Type ID</th>
                            <th class="manage-column">Ignored At (optional)</th>
                        </tr>
                    </thead>

                    <tbody>
                        %%%GDDYSEC.PostTypes.List%%%
                    </tbody>
                </table>

                <button type="submit" class="button button-primary">Submit</button>
            </form>
        </div>
    </div>
</div>
