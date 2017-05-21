
<div class="gddysec-boxshadow">
    <h3>Core Integrity Checks - Language</h3>

    <div class="inside">
        <p>
            The information necessary to check the integrity of the core files is obtained
            from the official <a href="http://codex.wordpress.org/WordPress.org_API"
            target="_blank">WordPress API</a> using an endpoint that returns the checksums
            of all the files associated to a version number. By default the API returns the
            checksums for the English installation, and there is an optional parameter named
            locale that accepts a valid abbreviation for a supported language. If your website
            was not installed using the English package please choose the appropriate language
            below.
        </p>

        <p>
            <strong>Note:</strong> Not all the international language codes are supported by
            WordPress's API, you must expect incompatibilities with the results of the core
            integrity checks, if you see files that are being flagged as added even when they
            are part of the official releases, files that are being flagged as deleted even
            when they are part of the official releases, and/or files that are being flagged
            as modified even when their content has not been modified please consider to
            select the English locale, if the false positives are persistent then fill a
            ticket reporting the issue.
        </p>

        <form action="%%GDDYSEC.URL.Settings%%#notifications" method="post">
            <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />

            <div class="gddysec-input-group">
                <label>WordPress Locale:</label>
                <select name="gddysec_set_language">
                    %%%GDDYSEC.Integrity.LanguageDropdown%%%
                </select>
                <button type="submit" class="button-primary">Proceed</button>
            </div>
        </form>
    </div>
</div>
