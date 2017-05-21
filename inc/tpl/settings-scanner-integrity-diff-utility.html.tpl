
<div class="gddysec-panel">
    <h3 class="gddysec-title">WordPress Integrity Diff Utility</h3>

    <div class="inside">
        <p>If your server allows the execution of system commands, you can configure the plugin to use the <a href="https://en.wikipedia.org/wiki/Diff_utility" target="_blank" rel="noopener">Unix Diff Utility</a> to compare the actual content of the file installed in the website and the original file provided by WordPress. This will show the differences between both files and then you can act upon the information provided.</p>

        <div class="gddysec-hstatus gddysec-hstatus-%%GDDYSEC.DiffUtility.StatusNum%%">
            <span>WordPress Integrity Diff Utility &mdash; %%GDDYSEC.DiffUtility.Status%%</span>

            <form action="%%GDDYSEC.URL.Settings%%#scanner" method="post">
                <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />
                <input type="hidden" name="gddysec_diff_utility" value="%%GDDYSEC.DiffUtility.SwitchValue%%" />
                <button type="submit" class="button button-primary">%%GDDYSEC.DiffUtility.SwitchText%%</button>
            </form>
        </div>
    </div>
</div>
