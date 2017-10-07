
<div class="gddysec-panel gddysec-integrity gddysec-integrity-incorrect">
    <div class="gddysec-clearfix">
        <div class="gddysec-pull-left gddysec-integrity-left">
            <h2 class="gddysec-title">WordPress Integrity</h2>

            <p>We inspect your WordPress installation and look for modifications on the core files as provided by WordPress.org. Files located in the root directory, wp-admin and wp-includes will be compared against the files distributed with v%%GDDYSEC.WordPressVersion%%; all files with inconsistencies will be listed here. Any changes might indicate a hack.</p>
        </div>

        <div class="gddysec-pull-right gddysec-integrity-right">
            <h2 class="gddysec-subtitle">Core WordPress Files Were Modified</h2>

            <p>We have not identified additional files, deleted files, or relevant changes to the core files in your WordPress installation. If you are experiencing other malware issues, we suggest our <a href="https://www.godaddy.com/web-security/malware-removal" target="_blank" rel="noopener">malware removal service</a>.</p>

            <p><a href="%%GDDYSEC.URL.Settings%%#scanner">Review False/Positives</a></p>
        </div>
    </div>

    %%%GDDYSEC.SiteCheck.Details%%%

    %%%GDDYSEC.Integrity.DiffUtility%%%

    <form action="%%GDDYSEC.URL.Dashboard%%" method="post" class="gddysec-%%GDDYSEC.Integrity.BadVisibility%%">
        <input type="hidden" name="gddysec_page_nonce" value="%%GDDYSEC.PageNonce%%" />

        <table class="wp-list-table widefat gddysec-table gddysec-integrity-table">
            <thead>
                <tr>
                    <th colspan="5">
                        <span>WordPress Integrity (%%GDDYSEC.Integrity.ListCount%%)</span>

                        <span class="gddysec-tooltip gddysec-hidden" content="The Unix Diff Utility is enabled. You can click the files in the table to see the differences detected by the scanner. If you consider the differences to be harmless you can mark the file as fixed, otherwise it is adviced to restore the original content immediately.">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="14" height="14">
                                <path fill="#000000" d="m6.998315,0.033333c-3.846307,0 -6.964982,
                                3.118675 -6.964982,6.964982s3.118675,6.965574 6.964982,6.965574s6.965574,
                                -3.119267 6.965574,-6.965574s-3.119267,-6.964982 -6.965574,-6.964982zm1.449957,
                                10.794779c-0.358509,0.141517 -0.643901,0.248833 -0.857945,0.32313c-0.213455,
                                0.074296 -0.461699,0.111444 -0.744143,0.111444c-0.433985,0 -0.771855,
                                -0.106137 -1.012434,-0.317823s-0.360279,-0.479978 -0.360279,-0.806055c0,
                                -0.126776 0.008845,-0.256499 0.026534,-0.388581c0.018281,-0.132082 0.047174,
                                -0.280675 0.086679,-0.447547l0.448727,-1.584988c0.039507,-0.152131 0.073707,
                                -0.296596 0.100831,-0.431036c0.027123,-0.135621 0.040097,-0.260037 0.040097,
                                -0.37325c0,-0.201661 -0.041865,-0.343178 -0.125008,-0.422782c-0.08432,
                                -0.079603 -0.242937,-0.11852 -0.479388,-0.11852c-0.115572,0 -0.234682,
                                0.0171 -0.35674,0.05307c-0.120879,0.037148 -0.225837,0.070758 -0.311926,
                                0.103779l0.118521,-0.488235c0.293647,-0.119699 0.574911,-0.222299 0.843204,
                                -0.307209c0.268291,-0.086089 0.521842,-0.128543 0.760652,-0.128543c0.431036,
                                0 0.7636,0.104959 0.997693,0.312517c0.232913,0.208147 0.350253,0.478797 0.350253,
                                0.811363c0,0.068989 -0.008255,0.190458 -0.024174,0.363815c-0.015921,
                                0.173947 -0.045994,0.332565 -0.089628,0.478209l-0.446368,1.580269c-0.036558,
                                0.126776 -0.068988,0.271831 -0.098472,0.433985c-0.028893,0.162156 -0.043043,
                                0.285983 -0.043043,0.369123c0,0.209916 0.046582,0.353202 0.140926,
                                0.429268c0.093164,0.076064 0.256498,0.114392 0.487643,0.114392c0.109086,
                                0 0.231144,-0.019459 0.369124,-0.057197c0.136799,-0.037737 0.23586,
                                -0.071349 0.298364,-0.100241l-0.119699,0.487643zm-0.079014,-6.414247c-0.208148,
                                0.193407 -0.45875,0.290109 -0.751808,0.290109c-0.292469,0 -0.54484,
                                -0.096702 -0.754756,-0.290109c-0.208737,-0.193406 -0.314285,-0.428678 -0.314285,
                                -0.703457c0,-0.274188 0.106138,-0.51005 0.314285,-0.705225c0.208148,
                                -0.195175 0.462287,-0.293058 0.754756,-0.293058c0.293058,0 0.54425,
                                0.097293 0.751808,0.293058c0.208146,0.195175 0.312516,0.431036 0.312516,
                                0.705225c0,0.275368 -0.10437,0.510051 -0.312516,0.703457z">
                                </path>
                            </svg>
                        </span>
                    </th>
                </tr>

                <tr>
                    <td id="cb" class="manage-column column-cb check-column">
                        <label class="screen-reader-text" for="cb-select-all-1">Select All</label>
                        <input id="cb-select-all-1" type="checkbox">
                    </td>
                    <th width="20" class="manage-column">&nbsp;</th>
                    <th width="100" class="manage-column">File Size</th>
                    <th width="200" class="manage-column">Modified At</th>
                    <th class="manage-column">File Path</th>
                </tr>
            </thead>

            <tbody>
                %%%GDDYSEC.Integrity.List%%%
            </tbody>

            <tfoot>
                <tr>
                    <td colspan="5">
                        <span>Legends: </span>

                        <span class="gddysec-tooltip" content="Files that are not part of a normal WordPress installation.">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="15.5px" height="18.5px" class="gddysec-integrity-added">
                                <path fill-rule="evenodd" stroke="rgb(0, 0, 0)" stroke-width="1px" stroke-linecap="butt" stroke-linejoin="miter" d="M9.845,4.505 L14.481,7.098 L13.639,11.471 L8.498,11.503 L9.845,4.505 Z" />
                                <path fill-rule="evenodd" stroke="rgb(0, 0, 0)" stroke-width="1px" stroke-linecap="butt" stroke-linejoin="miter" d="M3.500,1.500 L10.500,3.750 L10.500,9.375 L3.500,10.500 L3.500,1.500 Z" />
                                <path class="flag-bar" fill-rule="evenodd" stroke="rgb(0, 0, 0)" stroke-width="1px" stroke-linecap="butt" stroke-linejoin="miter" fill="rgb(255, 255, 255)" d="M1.500,1.500 L3.500,1.500 L3.500,16.500 L1.500,16.500 L1.500,1.500 Z" />
                            </svg>
                        </span>

                        <span class="gddysec-tooltip" content="Files that are part of a normal WordPress installation but were modified in your website.">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="15.5px" height="18.5px" class="gddysec-integrity-modified">
                                <path fill-rule="evenodd" stroke="rgb(0, 0, 0)" stroke-width="1px" stroke-linecap="butt" stroke-linejoin="miter" d="M9.845,4.505 L14.481,7.098 L13.639,11.471 L8.498,11.503 L9.845,4.505 Z" />
                                <path fill-rule="evenodd" stroke="rgb(0, 0, 0)" stroke-width="1px" stroke-linecap="butt" stroke-linejoin="miter" d="M3.500,1.500 L10.500,3.750 L10.500,9.375 L3.500,10.500 L3.500,1.500 Z" />
                                <path class="flag-bar" fill-rule="evenodd" stroke="rgb(0, 0, 0)" stroke-width="1px" stroke-linecap="butt" stroke-linejoin="miter" fill="rgb(255, 255, 255)" d="M1.500,1.500 L3.500,1.500 L3.500,16.500 L1.500,16.500 L1.500,1.500 Z" />
                            </svg>
                        </span>

                        <span class="gddysec-tooltip" content="Files that are part of a normal WordPress installation but were deleted from your website.">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="15.5px" height="18.5px" class="gddysec-integrity-removed">
                                <path fill-rule="evenodd" stroke="rgb(0, 0, 0)" stroke-width="1px" stroke-linecap="butt" stroke-linejoin="miter" d="M9.845,4.505 L14.481,7.098 L13.639,11.471 L8.498,11.503 L9.845,4.505 Z" />
                                <path fill-rule="evenodd" stroke="rgb(0, 0, 0)" stroke-width="1px" stroke-linecap="butt" stroke-linejoin="miter" d="M3.500,1.500 L10.500,3.750 L10.500,9.375 L3.500,10.500 L3.500,1.500 Z" />
                                <path class="flag-bar" fill-rule="evenodd" stroke="rgb(0, 0, 0)" stroke-width="1px" stroke-linecap="butt" stroke-linejoin="miter" fill="rgb(255, 255, 255)" d="M1.500,1.500 L3.500,1.500 L3.500,16.500 L1.500,16.500 L1.500,1.500 Z" />
                            </svg>
                        </span>
                    </td>
                </tr>
            </tfoot>
        </table>

        <p>
            <label>
                <input type="hidden" name="gddysec_process_form" value="0" />
                <input type="checkbox" name="gddysec_process_form" value="1" />
                <span>I understand that this operation can not be reverted.</span>
            </label>
        </p>

        <fieldset class="gddysec-clearfix">
            <label>Action:</label>

            <select name="gddysec_integrity_action">
                <option value="fixed">Mark as Fixed</option>
                <option value="restore">Restore File</option>
                <option value="delete">Delete File</option>
            </select>

            <button type="submit" class="button button-primary">Submit</button>

            <span class="gddysec-tooltip" content="Marking one or more files as fixed will force the plugin to ignore them during the next scan, very useful when you find false positives. Additionally you can restore the original content of the core files that appear as modified or deleted, this will tell the plugin to download a copy of the original files from the official WordPress repository. Deleting a file is an irreversible action, be careful.">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="14" height="14">
                    <path fill="#000000" d="m6.998315,0.033333c-3.846307,0 -6.964982,
                    3.118675 -6.964982,6.964982s3.118675,6.965574 6.964982,6.965574s6.965574,
                    -3.119267 6.965574,-6.965574s-3.119267,-6.964982 -6.965574,-6.964982zm1.449957,
                    10.794779c-0.358509,0.141517 -0.643901,0.248833 -0.857945,0.32313c-0.213455,
                    0.074296 -0.461699,0.111444 -0.744143,0.111444c-0.433985,0 -0.771855,
                    -0.106137 -1.012434,-0.317823s-0.360279,-0.479978 -0.360279,-0.806055c0,
                    -0.126776 0.008845,-0.256499 0.026534,-0.388581c0.018281,-0.132082 0.047174,
                    -0.280675 0.086679,-0.447547l0.448727,-1.584988c0.039507,-0.152131 0.073707,
                    -0.296596 0.100831,-0.431036c0.027123,-0.135621 0.040097,-0.260037 0.040097,
                    -0.37325c0,-0.201661 -0.041865,-0.343178 -0.125008,-0.422782c-0.08432,
                    -0.079603 -0.242937,-0.11852 -0.479388,-0.11852c-0.115572,0 -0.234682,
                    0.0171 -0.35674,0.05307c-0.120879,0.037148 -0.225837,0.070758 -0.311926,
                    0.103779l0.118521,-0.488235c0.293647,-0.119699 0.574911,-0.222299 0.843204,
                    -0.307209c0.268291,-0.086089 0.521842,-0.128543 0.760652,-0.128543c0.431036,
                    0 0.7636,0.104959 0.997693,0.312517c0.232913,0.208147 0.350253,0.478797 0.350253,
                    0.811363c0,0.068989 -0.008255,0.190458 -0.024174,0.363815c-0.015921,
                    0.173947 -0.045994,0.332565 -0.089628,0.478209l-0.446368,1.580269c-0.036558,
                    0.126776 -0.068988,0.271831 -0.098472,0.433985c-0.028893,0.162156 -0.043043,
                    0.285983 -0.043043,0.369123c0,0.209916 0.046582,0.353202 0.140926,
                    0.429268c0.093164,0.076064 0.256498,0.114392 0.487643,0.114392c0.109086,
                    0 0.231144,-0.019459 0.369124,-0.057197c0.136799,-0.037737 0.23586,
                    -0.071349 0.298364,-0.100241l-0.119699,0.487643zm-0.079014,-6.414247c-0.208148,
                    0.193407 -0.45875,0.290109 -0.751808,0.290109c-0.292469,0 -0.54484,
                    -0.096702 -0.754756,-0.290109c-0.208737,-0.193406 -0.314285,-0.428678 -0.314285,
                    -0.703457c0,-0.274188 0.106138,-0.51005 0.314285,-0.705225c0.208148,
                    -0.195175 0.462287,-0.293058 0.754756,-0.293058c0.293058,0 0.54425,
                    0.097293 0.751808,0.293058c0.208146,0.195175 0.312516,0.431036 0.312516,
                    0.705225c0,0.275368 -0.10437,0.510051 -0.312516,0.703457z">
                    </path>
                </svg>
            </span>
        </fieldset>
    </form>
</div>
