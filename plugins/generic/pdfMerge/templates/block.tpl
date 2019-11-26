{**
 * plugins/generic/pdfMerge/templates/block.tpl
 *
 * Copyright (c) Torben Richter
 * Distributed under the GNU GPL v2 or later. For full terms see the LICENSE file.
 *
 * PdfMerge modal dialogue
 *}
<div id="pdfMergeDiv" class="pkp_tab_actions" style="display: block;">
    <ul class="pkp_workflow_decisions">
        <li>
            <a href="#" id="pdfMerge-modal-open-button" title="Merge submission files"
                class="pkp_controllers_linkAction pkp_linkaction_externalReview pkp_linkaction_icon_">
                {translate key="plugins.pdfMerge.openDialogue"}
            </a>
        </li>
    </ul>
</div>
<div id="pdfMerge-modal" class="pkp_modal pkpModalWrapper" style="min-width: 100%;" tabindex="-1">
    <div class="pkp_modal_panel" role="dialog" aria-label="Merge manuscript files">
        <div class="header">{translate key="plugins.pdfMerge.modal.title"}</div><a href="#" id="pdfMerge-modal-close"
            class="close pkpModalCloseButton"><span class="pkp_screen_reader">{translate
                key="plugins.pdfMerge.modal.close"}</span></a>
        <div class="content">
            <div class="messageWrapper" style="margin-bottom: 10px;">
                <div id="fileListPanelSpinner" style="text-align: center; display: none;">
                    <span class="pkpSpinner" aria-hidden="true"></span>
                    <p
                        style="border: 2px solid #0f0; color: #31708f; background-color: #d9edf7; border-color: #bce8f1; padding-top: 2px; padding-bottom: 2px;">
                        {translate key="plugins.pdfMerge.modal.spinnerText"}</p>
                </div>
                <div id="successMessage"
                    style="text-align: center; display: none; border: 2px solid #0f0; color: #3c763d; background-color: #dff0d8; border-color: #d6e9c6; padding-top: 2px; padding-bottom: 2px;">
                    <p>{translate key="plugins.pdfMerge.modal.successMessage"}</p>
                </div>
                <div id="errorMessage"
                    style="text-align: center; display: none; border: 2px solid #f00; color: #a94442; background-color: #f2dede; border-color: #ebccd1; padding-top: 2px; padding-bottom: 2px;">
                    <p>{translate key="plugins.pdfMerge.modal.error.unknownError"}</p>
                </div>
                <div id="errorMessageForEmptyOrSingleValueList"
                    style="text-align: center; display: none; border: 2px solid #f00; color: #a94442; background-color: #f2dede; border-color: #ebccd1; padding-top: 2px; padding-bottom: 2px;">
                    <p>{translate key="plugins.pdfMerge.modal.error.listEmptyOrSingleValue"}</p>
                </div>
            </div>
            <div id="fileListPanel" class="pkpListPanel pkpListPanel--select">
                <div class="pkpListPanel__header -pkpClearfix">
                    <div class="pkpListPanel__title">{translate key="plugins.pdfMerge.modal.list.title"}</div>
                </div>
                <div class="pkpListPanel__body -pkpClearfix">
                    <list-panel-notice v-if="i18n.notice" :notice="i18n.notice" :type="noticeType" />
                    <select-list-panel-select-all v-if="showSelectAll" :label="i18n.selectAllLabel"
                        :checked="selectAllChecked" @toggle="toggleSelectAll" />
                    <div class="pkpListPanel__content">
                        <ul class="pkpListPanel__items" aria-live="polite">
                            {foreach from=$fileList item=file name=file}
                            <li class="pkpListPanelItem pkpListPanelItem--select">
                                <div class="pkpListPanelItem__selectItem">

                                    <input type="checkbox" id="checkForFile-{$smarty.foreach.file.iteration}">
                                </div>
                                <div class="pkpListPanelItem__item">
                                    <span>{$file->getGenreName()}</span>
                                    <span>{$file->getLocalizedName()}</span>
                                </div>
                                <div class="pkpListPanelItem--submission__link"
                                    style="position: absolute; top: 0; right: 0; width: 4rem; height: 100%; border-left: 1px solid #eee;">
                                    <select class="form-control" id="selectForFile-{$smarty.foreach.file.iteration}">
                                        {foreach from=$dropdownValues item=value name=dropdown}
                                        {if $smarty.foreach.file.iteration == $smarty.foreach.dropdown.iteration}
                                        <option value="{$value}" selected>{$value}</option>
                                        {else}
                                        <option value="{$value}">{$value}</option>
                                        {/if}
                                        {/foreach}
                                    </select>
                                </div>
                            </li>
                            {/foreach}
                        </ul>
                    </div>
                </div>
            </div>
            <div id="wizardButtons" class="modal_buttons">
                <button id="startMergeButton" class="pkp_button pkp_button_primary">{translate
                    key="plugins.pdfMerge.modal.mergeFiles"}</button>
                <button id="refreshButton" class="pkp_button pkp_button_primary" style="display: none;">{translate
                    key="plugins.pdfMerge.modal.refreshPage"}</button>
            </div>
        </div>
    </div>
</div>
{literal}
<script>
    //# sourceURL=mergePDF.js
    binaries = [];
    $('body').click(function (e) {
        if (e.target.id === 'pdfMerge-modal-open-button' || e.target.id === 'pdfMerge-modal-close') {
            $('#pdfMerge-modal').toggleClass('is_visible');

            if (e.target.id === 'pdfMerge-modal-close') {
                $('#errorMessageForEmptyOrSingleValueList').hide();
                $('#errorMessage').hide();
                $('#successMessage').hide();
                $('#refreshButton').hide();
                $('#startMergeButton').show();
                $('#fileListPanel').show();
            }
        }
        else if (e.target.id === 'refreshButton') {
            window.location.reload(true);
        }
        else if (e.target.id === 'startMergeButton') {
            $('#errorMessageForEmptyOrSingleValueList').hide();
            $('#errorMessage').hide();
            $('#startMergeButton').hide();
            $('#fileListPanel').hide();
            $('#fileListPanelSpinner').show();

            var fileList = {/literal} {$fileList|json_encode}; {literal}
            var idList = [];

            fileList.forEach(function (file, index) {
                if ($('#checkForFile-' + (index + 1)).is(':checked')) {
                    idList.push({ 'order': parseInt($('#selectForFile-' + (index + 1)).val()), 'fileId': file._data.fileId, 'genreId': file._data.genreId, 'revision': file._data.revision, 'dateUploaded': file._data.dateUploaded, 'submissionId': {/literal} {$submissionId} {literal}, 'stageId': {/literal} {$stageId} {literal}, 'fileName': file._data.name.en_US })
                }
            });

            if (idList.length <= 1) {
                $('#errorMessageForEmptyOrSingleValueList').show();
                $('#startMergeButton').show();
                $('#fileListPanel').show();
                $('#fileListPanelSpinner').hide();
                return;
            }

            idList.sort(function (a, b) { return a.order - b.order });
            
            var basePath = window.location.protocol + "//" + window.location.host;
            // Remove port number from basePath.
            // TODO --> Make endpoint URLs for converter configurable..
            var url = new URL(basePath);
            url.port = '';
            basePath = url.toString();
            var ojsBasePath = window.location.protocol + "//" + window.location.host + "/" + window.location.pathname.split('/')[1];
            {/literal}
            endpoint = "/excli/gateway/plugin/PdfMergeGatewayPlugin/{$submissionId}/{$stageId}/0/{$userId}"

            {if isset($reviewRoundId)}
                endpoint = "/excli/gateway/plugin/PdfMergeGatewayPlugin/{$submissionId}/{$stageId}/{$reviewRoundId}/{$userId}"
            {/if}
            {literal}

            $.ajax({
                method: "POST",
                contentType: "application/json",
                url: basePath + "converter/convert",
                data: JSON.stringify({ 'files': idList, 'apiKey': 'b8b1457f-99b8-4979-9e30-5316859f5981' }),
                success: function (response) {
                    $.ajax({
                        method: "GET",
                        contentType: "application/json",
                        url: ojsBasePath + endpoint,
                        success: function(response) {
                            $('#successMessage').show();
                            $('#refreshButton').show();
                            $('#fileListPanelSpinner').hide();
                        },
                        error: function (response) {
                            $('#startMergeButton').show();
                        $('#errorMessage').show();
                        $('#fileListPanel').show();
                        $('#fileListPanelSpinner').hide();
                        }
                    })
                }
            })
        }
    });
</script>
{/literal}
