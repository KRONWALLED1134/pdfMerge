<?php
import('lib.pkp.classes.handler.APIHandler');
class PdfMergeHandler extends APIHandler
{
	public function __construct()
	{
		$this->_handlerPath = 'pdfMerge';
		$roles = array(ROLE_ID_MANAGER);
		$this->_endpoints = [
			'GET' => [
				[
					'pattern' => $this->getEndpointPattern() . '/insert/{submissionId}/{stageId}/{reviewRoundId}/{userId}',
					'handler' => [$this, 'insertCopyAndDelete'],
					'roles' => $roles
				],
				[
					'pattern' => $this->getEndpointPattern() . '/insert/{submissionId}/{stageId}/{userId}',
					'handler' => [$this, 'insertCopyAndDelete'],
					'roles' => $roles
				]
			],
			'POST' => [
				[
					'pattern' => 'insert/{submissionId}/{userId}',
					'handler' => [$this, 'insertCopyAndDelete'],
					'roles' => $roles
				],
			],
		];
		parent::__construct();
	}

	function authorize($request, &$args, $roleAssignments)
	{
		$routeName = null;
		$slimRequest = $this->getSlimRequest();

		if (!is_null($slimRequest) && ($route = $slimRequest->getAttribute('route'))) {
			$routeName = $route->getName();
		}

		if ($routeName === 'insertCopyAndDelete') {
			import('api.v1.pdfMerge.PdfMergeSecurityPolicy');
			$this->addPolicy(new PdfMergeSecurityPolicy($request, $args, $roleAssignments));
		}

		return parent::authorize($request, $args, $roleAssignments);
	}

	function insertCopyAndDelete($request, $response, $args)
	{
		$submissionId = $args['submissionId'];
		$stageId = $args['stageId'];
		$userId = $args['userId'];
		$reviewRoundId = 0;

		if (isset($args['reviewRoundId'])) {
			$reviewRoundId = $args['reviewRoundId'];
		}

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

		// TODO --> Add contextId via param
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


		$data = array('status' => true);
		return $response->withJson($data, 200);
	}
}
