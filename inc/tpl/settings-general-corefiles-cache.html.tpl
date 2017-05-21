
<div class="gddysec-boxshadow">
    <h3>Core Integrity Checks - Marked As Fixed</h3>

    <div class="inside">
        <p>
            The scanner is prone to inconsistencies due to the diversity of configurations
            that a hosting provider may have in their servers, many of them add files in the
            document root of the websites with information associated to 3rd-party services
            that they offer or programs that they are running in their system. These files
            will be flagged by the plugin as <em>"added"</em> because they are not part of
            the official WordPress packages, but it is clear that they are false/positives.
            Some of these files are being ignored by the plugin to reduce the noise in the
            integrity checks, but there are many others that are not, you will have to
            select them and mark them as fixed if you believe they are harmless, this action
            will force the plugin to ignore them in future scans.
        </p>

        <div class="gddysec-hstatus">
            <span>Core Files Marked As Fixed: %%GDDYSEC.CoreFiles.CacheSize%% of data</span>
            <form action="%%GDDYSEC.URL.Settings%%#notifications" method="post">
                <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />
                <input type="hidden" name="gddysec_corefiles_cache" value="1" />
                <button type="submit" class="button-primary">Reset Cache</button>
            </form>
        </div>

        <table class="wp-list-table widefat gddysec-table gddysec-%%GDDYSEC.CoreFiles.TableVisibility%%">
            <thead>
                <tr>
                    <th>Reason</th>
                    <th>Ignored At</th>
                    <th>Line</th>
                </tr>
            </thead>

            <tbody>
                %%%GDDYSEC.CoreFiles.IgnoredFiles%%%
            </tbody>
        </table>
    </div>
</div>
