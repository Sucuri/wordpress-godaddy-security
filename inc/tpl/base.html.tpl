
<div class="wrap gddysec-container">
    <h2 id="warnings_hook">
        <!-- Dynamically populated via JavaScript -->
    </h2>

    %%%GDDYSEC.GenerateAPIKey.Modal%%%

    <div class="gddysec-header gddysec-clearfix">
        <div class="gddysec-pull-left">
            <a href="%%GDDYSEC.URL.Dashboard%%" class="gddysec-logo gddysec-pull-left">
                <img src="%%GDDYSEC.PluginURL%%/inc/images/pluginlogo.png" alt="GoDaddy LLC" />
            </a>
            <span class="gddysec-subtitle gddysec-pull-left">Security</span>
            <span class="gddysec-version gddysec-pull-left">v%%GDDYSEC.PluginVersion%%</span>
        </div>

        <div class="gddysec-pull-right gddysec-navbar">
            <ul>
                <li class="gddysec-%%GDDYSEC.GenerateAPIKey.Visibility%%">
                    <a href="#" class="button button-primary gddysec-modal-button gddysec-register-site-button"
                    data-modalid="gddysec-register-site">Generate API Key</a>
                </li>

                <li><a href="%%GDDYSEC.URL.Dashboard%%" class="button button-primary">Dashboard</a></li>

                <li><a href="%%GDDYSEC.URL.Settings%%" class="button button-primary">Settings</a></li>
            </ul>
        </div>
    </div>

    <div class="gddysec-clearfix gddysec-content gddysec-%%GDDYSEC.PageStyleClass%%">
        %%%GDDYSEC.PageContent%%%
    </div>

    <div class="gddysec-clearfix gddysec-footer">
        <div>Copyright &copy; %%GDDYSEC.Year%% GoDaddy, LLC. All Rights Reserved.</div>
    </div>
</div>
