
<div class="gddysec-boxshadow gddysec-%%GDDYSEC.IgnoreRules.TableVisibility%%">
    <h3>Ignore Alerts</h3>

    <div class="inside">
        <p>
            This is a list of registered <a href="https://codex.wordpress.org/Post_Types"
            target="_blank">Post Types</a>, since you have enabled the <strong>email alerts
            for new or modified content</strong>, we will send you an alert if any of these
            <code>post-types</code> are created and/or updated. You may want to ignore some
            of them as some 3rd-party extensions create temporary data in the posts table
            to track changes in their own tools.
        </p>
    </div>

    <table class="wp-list-table widefat gddysec-table gddysec-settings-ignorerules">
        <thead>
            <tr>
                <th>&nbsp;</th>
                <th>Post Type</th>
                <th width="50">Ignored</th>
                <th>Ignored At</th>
                <th>&nbsp;</th>
            </tr>
        </thead>

        <tbody>
            %%%GDDYSEC.IgnoreRules.PostTypes%%%
        </tbody>
    </table>
</div>
