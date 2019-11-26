<?php
/**
 * @file plugins/generic/pdfMerge/PdfMergeGatewayPlugin.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PdfMergeGatewayPlugin
 * @ingroup plugins_generic_pdfMerge
 *
 * @brief Gateway component of pdfMerge plugin
 *
 */
import('lib.pkp.classes.plugins.GatewayPlugin');
class PdfMergeGatewayPlugin extends GatewayPlugin {
	/** @var PdfMergePlugin Parent plugin */
	protected $_parentPlugin;
	/**
	 * @param $parentPlugin PdfMergeGatewayPlugin
	 */
	public function __construct($parentPlugin) {
		parent::__construct();
		$this->_parentPlugin = $parentPlugin;
	}
	/**
	 * Hide this plugin from the management interface (it's subsidiary)
	 */
	public function getHideManagement() {
		return true;
	}
	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	public function getName() {
		return 'PdfMergeGatewayPlugin';
	}
	/**
	 * @copydoc Plugin::getDisplayName()
	 */
	public function getDisplayName() {
		return __('plugins.generic.pdfMerge.displayName');
	}
	/**
	 * @copydoc Plugin::getDescription()
	 */
	public function getDescription() {
		return __('plugins.generic.pdfMerge.description');
	}
	/**
	 * Override the builtin to get the correct plugin path.
	 * @return string
	 */
	public function getPluginPath() {
		return $this->_parentPlugin->getPluginPath();
	}
	/**
	 * Get whether or not this plugin is enabled. (Should always return true, as the
	 * parent plugin will take care of loading this one when needed)
	 * @param $contextId int Context ID (optional)
	 * @return boolean
	 */
	public function getEnabled($contextId = null) {
		return $this->_parentPlugin->getEnabled($contextId);
	}
	/**
	 * Handle fetch requests for this plugin.
	 * @param $args array Arguments.
	 * @param $request PKPRequest Request object.
	 */
	public function fetch($args, $request) {
		$request = Application::get()->getRequest();
		$journal = $request->getJournal();
		if (!$journal) return false;

		if (!$this->_parentPlugin->getEnabled($journal->getId())) return false;

        	$submissionId = array_shift($args);
		$stageId = array_shift($args);
		$reviewRoundId = array_shift($args);
		$userId = array_shift($args);

		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		$submissionFile = new SubmissionFile();

		$submissionFile->setRevision(1);
		$submissionFile->setSubmissionId($submissionId);
		$submissionFile->setFileType('application/pdf');
		$submissionFile->setGenreId(12);
		$submissionFile->setFileSize(filesize($filename));
		$submissionFile->setOriginalFileName($submissionId . '_MergedContents.pdf');

		$submissionFile->setFileStage(2);
		if ($stageId == "3") {
			$submissionFile->setFileStage(4);
		}

		$submissionFile->setViewable(1);
		$submissionFile->setSubmissionLocale('en_US');
		$submissionFile->setUploaderUserId($userId);

		$date_object = date("Y-m-d H:i:s");
		$submissionFile->setDateUploaded($date_object);
		$submissionFile->setDateModified($date_object);

		import('lib.pkp.classes.file.SubmissionFileManager');
		$submissionFileManager = new SubmissionFileManager(1, $submissionId);
		$sourceFile = $submissionFileManager->getBasePath() . $submissionFile->_fileStageToPath($submissionFile->getFileStage()) . '/converted/merged.pdf';
		$submissionFile = $submissionFileDao->insertObject($submissionFile, $sourceFile);
		
		if ($reviewRoundId != 0) {
			$reviewRoundDao = DAORegistry::getDAO('ReviewRoundDAO');
			$reviewRound = $reviewRoundDao->getById($reviewRoundId);
			$reviewFilesDao = DAORegistry::getDAO('ReviewFilesDAO');
			$reviewFilesDao->grant($reviewRoundId, $submissionFile->getFileId());
			$submissionFileDao->assignRevisionToReviewRound($submissionFile->getFileId(), $submissionFile->getRevision(), $reviewRound);
		}

		return true;
	}
}
