
<div class="gddysec-panel">
    <h3 class="gddysec-title">WordPress Integrity (False/Positives)</h3>

    <div class="inside">
        <p>Since the scanner doesn't reads the files during the execution of the integrity check, it is possible to find false/positives. Files listed here have been marked as false/positives and will be ignored by the scanner in subsequent scans.</p>

        <form action="%%GDDYSEC.URL.Settings%%#scanner" method="post">
            <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />
            <input type="hidden" name="gddysec_reset_integrity_cache" value="1" />

            <table class="wp-list-table widefat gddysec-table">
                <thead>
                    <tr>
                        <td id="cb" class="manage-column column-cb check-column">
                            <label class="screen-reader-text" for="cb-select-all-1">Select All</label>
                            <input id="cb-select-all-1" type="checkbox">
                        </td>
                        <th>Reason</th>
                        <th>Ignored At</th>
                        <th>File Path</th>
                    </tr>
                </thead>

                <tbody>
                    %%%GDDYSEC.IgnoredFiles%%%

                    <tr class="gddysec-%%GDDYSEC.NoFilesVisibility%%">
                        <td colspan="4">
                            <em>no data available</em>
                        </td>
                    </tr>
                </tbody>
            </table>

            <p>
                <button type="submit" class="button button-primary">Stop Ignoring the Selected Files</button>
            </p>
        </form>
    </div>
</div>
