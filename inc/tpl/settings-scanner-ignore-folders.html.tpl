
<div class="gddysec-panel">
    <h3 class="gddysec-title">Ignore Files And Folders During The Scans</h3>

    <div class="inside">
        <p>Use this tool to select the files and/or folders that are too heavy for the scanner to process. These are usually folders with images, media files like videos and audios, backups and &mdash; in general &mdash; anything that is not code-related. Ignoring these files or folders will reduce the memory consumption of the PHP script.</p>

        <script type="text/javascript">
        /* global jQuery */
        /* jshint camelcase: false */
        jQuery(document).ready(function ($) {
            $('.gddysec-ignorescanning tbody').html(
                '<tr><td colspan="3"><span>Loading...</span></td></tr>'
            );
            $.post('%%GDDYSEC.AjaxURL.Dashboard%%', {
                action: 'gddysec_ajax',
                gddysec_page_nonce: '%%GDDYSEC.PageNonce%%',
                form_action: 'get_ignored_files',
            }, function (data) {
                $('.gddysec-ignorescanning tbody').html(data);
            });
        });
        </script>

        <form action="%%GDDYSEC.URL.Settings%%#advanced" method="post">
            <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />
            <input type="hidden" name="gddysec_ignorescanning_action" value="ignore" />

            <fieldset class="gddysec-clearfix">
                <label>Ignore One Single File:</label>
                <input type="text" name="gddysec_ignorescanning_file" placeholder="e.g. /private/cert.crt" />
                <button type="submit" class="button button-primary">Submit</button>
            </fieldset>
        </form>

        <hr>

        <form action="%%GDDYSEC.URL.Settings%%#advanced" method="post">
            <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />

            <table class="wp-list-table widefat gddysec-table gddysec-ignorescanning">
                <thead>
                    <td id="cb" class="manage-column column-cb check-column">
                        <label class="screen-reader-text" for="cb-select-all-1">Select All</label>
                        <input id="cb-select-all-1" type="checkbox">
                    </td>
                    <th class="manage-column">File Path</th>
                    <th class="manage-column">Status</th>
                </thead>

                <tbody>
                </tbody>
            </table>

            <div class="gddysec-recipient-form">
                <label>
                    <select name="gddysec_ignorescanning_action">
                        <option value="">Action</option>
                        <option value="ignore">Ignore</option>
                        <option value="unignore">Unignore</option>
                    </select>
                </label>

                <button type="submit" class="button button-primary">Submit</button>
            </div>
        </form>
    </div>
</div>
