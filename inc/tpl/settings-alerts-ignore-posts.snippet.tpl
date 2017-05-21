
<tr>
    <td><em class="gddysec-monospace">%%GDDYSEC.IgnoreRules.WasIgnoredAt%%</em></td>

    <td><span class="gddysec-label-%%GDDYSEC.IgnoreRules.IsIgnoredClass%%">%%GDDYSEC.IgnoreRules.IsIgnored%%</span></td>

    <td>%%GDDYSEC.IgnoreRules.PostTypeTitle%%</td>

    <td class="td-with-button">
        <form action="%%GDDYSEC.URL.Settings%%#alerts" method="post">
            <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />
            <input type="hidden" name="gddysec_ignorerule" value="%%GDDYSEC.IgnoreRules.PostType%%" />
            <input type="hidden" name="gddysec_ignorerule_action" value="%%GDDYSEC.IgnoreRules.Action%%" />
            <button type="submit" class="button button-secondary">%%GDDYSEC.IgnoreRules.ButtonText%%</button>
        </form>
    </td>
</tr>
