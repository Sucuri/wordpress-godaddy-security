
<div class="gddysec-panel">
    <h3 class="gddysec-title">Data Storage</h3>

    <div class="inside">
        <p>This is the directory where the plugin will store the security logs, the list of files marked as fixed in the core integrity tool, the cache for the malware scanner and 3rd-party plugin metadata. The plugin requires write permissions in this directory as well as the files contained in it. If you prefer to keep these files in a non-public directory <em>(one level up the document root)</em> please define a constant in the <em>"wp-config.php"</em> file named <em>"GDDYSEC_DATA_STORAGE"</em> with the absolute path to the new directory.</p>
    </div>

    <div class="gddysec-hstatus gddysec-hstatus-2">
        <span class="gddysec-monospace">%%GDDYSEC.Storage.Path%%</span>
    </div>

    <form action="%%GDDYSEC.URL.Settings%%#advanced" method="post">
        <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />
        <input type="hidden" name="gddysec_reset_storage" value="1" />

        <table class="wp-list-table widefat gddysec-table">
            <thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column">
                        <label class="screen-reader-text" for="cb-select-all-1">Select All</label>
                        <input id="cb-select-all-1" type="checkbox">
                    </td>
                    <th class="manage-column">File Path</th>
                    <th class="manage-column">File Size</th>
                    <th class="manage-column">Status</th>
                    <th class="manage-column">Writable</th>
                </tr>
            </thead>

            <tbody>
                %%%GDDYSEC.Storage.Files%%%
            </tbody>
        </table>

        <p>
            <button type="submit" class="button button-primary">Delete</button>
        </p>
    </form>
</div>
