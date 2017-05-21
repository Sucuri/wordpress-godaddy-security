
<script type="text/javascript">
jQuery(function ($) {
    $.post('%%GDDYSEC.AjaxURL.Home%%', {
        action: 'gddysec_ajax',
        gddysec_page_nonce: '%%GDDYSEC.PageNonce%%',
        form_action: 'get_core_files',
    }, function(data){
        $('#gddysec-corefiles-response').html(data);
    });
});
</script>

<div id="gddysec-corefiles-response">
    <!-- Populated by JavaScript -->
</div>
