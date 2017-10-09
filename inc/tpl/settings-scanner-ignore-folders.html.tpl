
<div class="gddysec-panel">
    <h3 class="gddysec-title">Ignore Files And Folders During The Scans</h3>

    <div class="inside">
        <p>Use this tool to select the files and/or folders that are too heavy for the scanner to process. These are usually folders with images, media files like videos and audios, backups and &mdash; in general &mdash; anything that is not code-related. Ignoring these files or folders will reduce the memory consumption of the PHP script.</p>

        <form action="%%GDDYSEC.URL.Settings%%#advanced" method="post">
            <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />

            <fieldset class="gddysec-clearfix">
                <label>Ignore a file or directory:</label>
                <input type="text" name="gddysec_ignorefolder" placeholder="e.g. /private/directory/" />
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
                    %%%GDDYSEC.IgnoreScan.List%%%
                </tbody>
            </table>

            <button type="submit" class="button button-primary">Unignore Selected Directories</button>
        </form>
    </div>
</div>
