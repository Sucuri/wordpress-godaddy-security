
<script type="text/javascript">
jQuery(function ($) {
    $('#gddysec-sitecheck-showmore').on('click', function (ev) {
        ev.preventDefault();

        $('.gddysec-sitecheck-details li.gddysec-hidden')
        .removeClass('gddysec-hidden');
    });
});
</script>

<div class="gddysec-clearfix gddysec-boxshadow gddysec-sitecheck-details">
    <div class="gddysec-pull-left">
        <ul>
            <li>
                <span class="gddysec-details-title">Website:</span>
                <span class="gddysec-details-value">%%GDDYSEC.SiteCheck.Website%%</span>
            </li>

            <li>
                <span class="gddysec-details-title">Domain Scanned:</span>
                <span class="gddysec-details-value">%%GDDYSEC.SiteCheck.Domain%%</span>
            </li>

            <li>
                <span class="gddysec-details-title">Site IP Address:</span>
                <span class="gddysec-details-value">%%GDDYSEC.SiteCheck.ServerAddress%%</span>
            </li>

            <li>
                <span class="gddysec-details-title">WordPress Version:</span>
                <span class="gddysec-details-value">%%GDDYSEC.SiteCheck.WPVersion%%</span>
            </li>

            <li>
                <span class="gddysec-details-title">PHP Version:</span>
                <span class="gddysec-details-value">%%GDDYSEC.SiteCheck.PHPVersion%%</span>
            </li>

            %%%GDDYSEC.SiteCheck.Additional%%%
        </ul>
    </div>

    <div class="gddysec-pull-right">
        <a href="#" id="gddysec-sitecheck-showmore" class="gddysec-sitecheck-showmore">
            <span class="gddysec-showmore-text">Show More Info</span>
            <span class="gddysec-showmore-arrow"></span>
        </a>
    </div>
</div>
