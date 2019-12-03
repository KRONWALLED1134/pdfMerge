<?php
import('lib.pkp.classes.form.Form');
class PdfMergeSettingsForm extends Form
{
  public $plugin;

  public function __construct($plugin)
  {
    parent::__construct($plugin->getTemplateResource('settings.tpl'));
    $this->plugin = $plugin;

    $this->addCheck(new FormValidatorPost($this));
    $this->addCheck(new FormValidatorCSRF($this));
  }

  public function initData()
  {
    $contextId = Application::get()->getRequest()->getContext()->getId();
    $this->setData('converterUrl', $this->plugin->getSetting($contextId, 'converterUrl'));
    parent::initData();
  }

  public function readInputData()
  {
    $this->readUserVars(['converterUrl']);
    parent::readInputData();
  }

  public function fetch($request, $template = null, $display = false)
  {
    $templateMgr = TemplateManager::getManager($request);
    $templateMgr->assign('pluginName', $this->plugin->getName());

    return parent::fetch($request, $template, $display);
  }

  public function execute()
  {
    $contextId = Application::get()->getRequest()->getContext()->getId();
    $this->plugin->updateSetting($contextId, 'converterUrl', $this->getData('converterUrl'));

    // Tell the user that the save was successful.
    import('classes.notification.NotificationManager');
    $notificationMgr = new NotificationManager();
    $notificationMgr->createTrivialNotification(
      Application::get()->getRequest()->getUser()->getId(),
      NOTIFICATION_TYPE_SUCCESS,
      ['contents' => __('common.changesSaved')]
    );

    return parent::execute();
  }
}
