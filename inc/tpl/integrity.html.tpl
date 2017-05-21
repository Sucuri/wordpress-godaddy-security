
<script type="text/javascript">
/* global jQuery */
/* jshint camelcase: false */
jQuery(document).ready(function ($) {
    $.post('%%GDDYSEC.AjaxURL.Dashboard%%', {
        action: 'gddysec_ajax',
        gddysec_page_nonce: '%%GDDYSEC.PageNonce%%',
        form_action: 'check_wordpress_integrity',
    }, function (data) {
        $('#gddysec-integrity-response').html(data);
    });
});
</script>

<div id="gddysec-integrity-response">
    <!-- Populated by JavaScript -->

    <div class="gddysec-panel gddysec-integrity gddysec-integrity-loading">
        <div class="gddysec-clearfix">
            <div class="gddysec-pull-left gddysec-integrity-left">
                <h2 class="gddysec-title">WordPress Integrity</h2>

                <p>We inspect your WordPress installation and look for modifications on the core files as provided by WordPress.org. Files located in the root directory, wp-admin and wp-includes will be compared against the files distributed with v%%GDDYSEC.WordPressVersion%%; all files with inconsistencies will be listed here. Any changes might indicate a hack.</p>
            </div>

            <div class="gddysec-pull-right gddysec-integrity-right">
                <div class="gddysec-integrity-missing">
                    <!-- Missing data; display loading message -->
                </div>
            </div>
        </div>

        <p>Loading...</p>
    </div>
</div>
