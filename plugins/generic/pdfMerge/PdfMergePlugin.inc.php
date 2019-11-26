<?php
import('lib.pkp.classes.plugins.GenericPlugin');
import('lib.pkp.classes.submission.SubmissionFile');

class SubmissionFilePdfMerge extends SubmissionFile
{
	var $genreName;

	function setGenreName($genreId)
	{
		$genreDao = DAORegistry::getDAO('GenreDAO');
		$genre = $genreDao->getById($genreId);
		$this->genreName = $genre->getLocalizedName();
	}

	function getGenreName()
	{
		return $this->genreName;
	}
}

class PdfMergePlugin extends GenericPlugin
{
	/**
	 * @copydoc GenericPlugin::register()
	 */
	public function register($category, $path, $mainContextId = NULL)
	{
		$this->addLocaleData();
		if (parent::register($category, $path, $mainContextId)) {
			if ($this->getEnabled()) {
				HookRegistry::register('PdfMerge::Show', array($this, 'pdfMergeCallback'), HOOK_SEQUENCE_LATE);
				$this->_registerTemplateResource();

				$this->import('PdfMergeGatewayPlugin');
				PluginRegistry::register('gateways', new PdfMergeGatewayPlugin($this), $this->getPluginPath());
			}
			return true;
		}
		return false;
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName()
	{
		return __('plugins.generic.PdfMergePlugin.displayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription()
	{
		return __('plugins.generic.PdfMergePlugin.description');
	}

	function loadSubmissionFiles($params)
	{
		$submissionId = $params['submissionId'];
		$stageId = $params['stageId'];
		$sql = "SELECT file_id, revision, date_uploaded, submission_id, original_file_name, genre_id FROM submission_files WHERE submission_id = " . $submissionId;

		// If stage is 3 (Review) we need to select files from file_stage 4
		if ($stageId == "1") {
			$sql .= " AND file_stage = '2'";
		} else if ($stageId == "3") {
			$sql .= " AND file_stage = '4'";
		}

		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		$files = $submissionFileDao->retrieve($sql);

		$fileList = array();
		while (!$files->EOF) {
			$row = $files->getRowAssoc(false);

			$file = new SubmissionFilePdfMerge();
			$file->setName($row['original_file_name'], AppLocale::getLocale());

			$genreId = $row['genre_id'];
			$fileId = $row['file_id'];
			$revision = $row['revision'];
			$date_uploaded = $row['date_uploaded'];

			$file->setGenreId($genreId);
			$file->setGenreName($genreId);
			$file->setFileId($fileId);
			$file->setSubmissionId($submissionId);
			$file->setRevision($revision);
			$file->setDateUploaded($date_uploaded);

			array_push($fileList, $file);

			$files->MoveNext();
		}
		$files->Close();

		return $fileList;
	}

	function pdfMergeCallback($hookName, $args)
	{
		$params = &$args[0];
		$templateMgr = &$args[1];
		$output = &$args[2];

		$request = $this->getRequest();
		$context = $request->getContext();

		$fileList = $this->loadSubmissionFiles($params);
		$dropdownValues = array();
		for ($i = 1; $i <= count($fileList); $i++) {
			array_push($dropdownValues, $i);
		}

		$sessionManager = SessionManager::getManager();
		$session = $sessionManager->getUserSession();

		$userId = $session->getUserId();

		$templateMgr->assign(array(
			'submissionId' => $params['submissionId'],
			'stageId' => $params['stageId'],
			'fileList' => $fileList,
			'dropdownValues' => $dropdownValues,
			'isUploaded' => false,
			'userId' => $userId
		));

		$output = $templateMgr->display($this->getTemplateResource('block.tpl'));
		return false;
	}
}
