
%%%GDDYSEC.AuditLog.Date%%%

<div class="gddysec-clearfix gddysec-auditlog-entry">
    <div class="gddysec-pull-left gddysec-auditlog-entry-time">
        <span>%%GDDYSEC.AuditLog.Time%%</span>
    </div>

    <div class="gddysec-pull-left gddysec-auditlog-entry-event">
        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="15.5px" height="18.5px" class="gddysec-pull-left gddysec-auditlog-%%GDDYSEC.AuditLog.Event%%"">
            <path fill-rule="evenodd" stroke="rgb(0, 0, 0)" stroke-width="1px" stroke-linecap="butt" stroke-linejoin="miter" d="M9.845,4.505 L14.481,7.098 L13.639,11.471 L8.498,11.503 L9.845,4.505 Z" />
            <path fill-rule="evenodd" stroke="rgb(0, 0, 0)" stroke-width="1px" stroke-linecap="butt" stroke-linejoin="miter" d="M3.500,1.500 L10.500,3.750 L10.500,9.375 L3.500,10.500 L3.500,1.500 Z" />
            <path class="flag-bar" fill-rule="evenodd" stroke="rgb(0, 0, 0)" stroke-width="1px" stroke-linecap="butt" stroke-linejoin="miter" fill="rgb(255, 255, 255)" d="M1.500,1.500 L3.500,1.500 L3.500,16.500 L1.500,16.500 L1.500,1.500 Z" />
        </svg>
    </div>

    <div class="gddysec-pull-left gddysec-auditlog-entry-message">
        <div class="gddysec-auditlog-entry-title">
            <strong>%%GDDYSEC.AuditLog.Username%%</strong>
            <span>%%GDDYSEC.AuditLog.Message%%</span>
        </div>

        <div class="gddysec-auditlog-entry-extra">
            %%%GDDYSEC.AuditLog.Extra%%%
        </div>
    </div>

    <div class="gddysec-pull-right gddysec-auditlog-entry-address">
        <span>IP: %%GDDYSEC.AuditLog.Address%%</span>
    </div>
</div>
