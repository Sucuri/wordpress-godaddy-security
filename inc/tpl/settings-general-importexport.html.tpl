
<div class="gddysec-panel">
    <h3 class="gddysec-title">Import &amp; Export Settings</h3>

    <div class="inside">
        <form action="%%GDDYSEC.URL.Settings%%" method="post">
            <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />

            <p>Copy the JSON-encoded data from the box below, go to your other websites and click the <em>"Import"</em> button in the settings page. The plugin will start using the same settings from this website. Notice that some options are omitted as they contain values specific to this website. To import the settings from another website into this one, replace the JSON-encoded data in the box below with the JSON-encoded data exported from the other website, then click the button <em>"Import"</em>. Notice that some options will not be imported to reduce the security risk of writing arbitrary data into the disk.</p>

            <textarea name="gddysec_settings" class="gddysec-full-textarea gddysec-monospace">%%GDDYSEC.Export%%</textarea>

            <p>
                <label>
                    <input type="hidden" name="gddysec_process_form" value="0" />
                    <input type="checkbox" name="gddysec_process_form" value="1" />
                    <span>I understand that this operation can not be reverted.</span>
                </label>
            </p>

            <button type="submit" name="gddysec_import" class="button button-primary">Submit</button>
        </form>
    </div>
</div>
