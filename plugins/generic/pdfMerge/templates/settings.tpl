<script>
	$(function() {ldelim}
		$('#pdfMergeSettings').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form
  class="pkp_form"
  id="pdfMergeSettings"
  method="POST"
  action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}"
>
	{csrf}

  {fbvFormArea}
		{fbvFormSection}
			{fbvElement
        type="text"
        id="converterUrl"
        value=$converterUrl
        label="plugins.generic.pdfMerge.converterUrl"
      }
		{/fbvFormSection}
  {/fbvFormArea}
	{fbvFormButtons submitText="common.save"}
</form>