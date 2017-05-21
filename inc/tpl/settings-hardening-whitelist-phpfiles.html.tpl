
<div class="gddysec-panel">
    <h3 class="gddysec-title">Whitelist Blocked PHP Files</h3>

    <div class="inside">
        <p>After you apply the hardening in either the includes, content, and/or upload directories the plugin will add a rule in the access control file to deny access to any PHP file located in these folders, this is a good precaution in case that an attacker is able to upload a shell script; with a few exceptions the <em>"index.php"</em> is the only one that should be publicly accessible, however many theme/plugin developers decide to use these folders to process some operations, in this case applying the hardening <strong>may break</strong> their functionality.</p>

        <form action="%%GDDYSEC.URL.Settings%%#hardening" method="post">
            <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />
            <fieldset class="gddysec-clearfix">
                <label>File Path:</label>
                <input type="text" name="gddysec_hardening_whitelist" placeholder="e.g. wp-tinymce.php" />
                <select name="gddysec_hardening_folder">
                    <option value="wp-includes">wp-includes</option>
                    <option value="wp-content">wp-content</option>
                    <option value="wp-content/uploads">wp-content/uploads</option>
                </select>
                <button type="submit" class="button button-primary">Submit</button>
            </fieldset>
        </form>

        <hr>

        <form action="%%GDDYSEC.URL.Settings%%#hardening" method="post">
            <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />

            <table class="wp-list-table widefat gddysec-table gddysec-hardening-whitelist-table">
                <thead>
                    <td id="cb" class="manage-column column-cb check-column">
                        <label class="screen-reader-text" for="cb-select-all-1">Select All</label>
                        <input id="cb-select-all-1" type="checkbox">
                    </td>
                    <th class="manage-column">File Path</th>
                    <th class="manage-column">Directory</th>
                    <th class="manage-column">Pattern</th>
                </thead>

                <tbody>
                    %%%GDDYSEC.HardeningWhitelist.List%%%

                    <tr class="gddysec-%%GDDYSEC.HardeningWhitelist.NoItemsVisibility%%">
                        <td colspan="4">
                            <em>no data available</em>
                        </td>
                    </tr>
                </tbody>
            </table>

            <button type="submit" class="button button-primary">Delete</button>
        </form>
    </div>
</div>