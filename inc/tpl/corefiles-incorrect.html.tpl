
<div class="gddysec-boxshadow gddysec-integrity gddysec-integrity-incorrect">
    <div class="gddysec-integrity-header gddysec-clearfix">
        <div class="gddysec-column-left">
            <h2 class="gddysec-integrity-title">WordPress Integrity</h2>

            <p>
                We inspect your WordPress installation and look for modifications
                on the core files as provided by WordPress.org. Files located in
                the root directory, wp-admin and wp-includes will be compared against
                the files distributed with v%%GDDYSEC.Version%%; all files with
                inconsistencies will be listed here. Any changes might indicate a hack.
            </p>
        </div>

        <div class="gddysec-column-center">
            <img src="%%GDDYSEC.PluginURL%%/inc/images/checkcross.png" />
        </div>

        <div class="gddysec-column-right">
            <h2 class="gddysec-integrity-subtitle">Core WordPress Files Were Modified</h2>

            <p>
                We identified that some of your WordPress core files were modified.
                That might indicate a hack or a broken file on your installation.
            </p>
        </div>
    </div>

    <form action="%%GDDYSEC.URL.Home%%" method="post" class="gddysec-%%GDDYSEC.CoreFiles.BadVisibility%%">
        <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />

        <table class="wp-list-table widefat gddysec-table gddysec-corefiles">
            <thead>
                <tr>
                    <th colspan="5">WordPress Integrity (%%GDDYSEC.CoreFiles.ListCount%% files)</th>
                </tr>

                <tr>
                    <th class="manage-column column-cb check-column">
                        <label class="screen-reader-text" for="cb-select-all-1">Select All</label>
                        <input id="cb-select-all-1" type="checkbox">
                    </th>
                    <th width="20" class="manage-column">&nbsp;</th>
                    <th width="100" class="manage-column">File Size</th>
                    <th width="200" class="manage-column">Modified At</th>
                    <th class="manage-column">File Path</th>
                </tr>
            </thead>

            <tbody>
                %%%GDDYSEC.CoreFiles.List%%%
            </tbody>
        </table>

        <div class="gddysec-inline-alert-info">
            <p>
                Marking one or more files as fixed will force the plugin to ignore them during
                the next scan, very useful when you find false positives. Additionally you can
                restore the original content of the core files that appear as modified or deleted,
                this will tell the plugin to download a copy of the original files from the official
                <a href="https://core.svn.wordpress.org/tags/" target="_blank">WordPress repository</a>.
                Deleting a file is an irreversible action, be careful.
            </p>
        </div>

        <div class="gddysec-form-buttons">
            <p>
                <label>
                    <input type="hidden" name="gddysec_process_form" value="0" />
                    <input type="checkbox" name="gddysec_process_form" value="1" />
                    <span>I understand that this operation can not be reverted.</span>
                </label>
            </p>

            <div class="gddysec-input-group">
            <label>Action:</label>
                <select name="gddysec_integrity_action">
                    <option value="fixed">Mark as Fixed</option>
                    <option value="restore">Restore File</option>
                    <option value="delete">Delete File</option>
                </select>
                <button type="submit" class="button button-primary">Proceed</button>
            </div>
        </div>
    </form>
</div>
