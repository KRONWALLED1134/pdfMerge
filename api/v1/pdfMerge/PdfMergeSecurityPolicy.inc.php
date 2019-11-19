<?php
import('lib.pkp.classes.security.authorization.internal.ContextPolicy');
import('lib.pkp.classes.security.authorization.RoleBasedHandlerOperationPolicy');

class PdfMergeSecurityPolicy extends ContextPolicy {

	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $args array request parameters
	 * @param $roleAssignments array
	 * @param $submissionParameterName string the request parameter we
	 *  expect the submission id in.
	 * @param $permitDeclined boolean True iff declined reviews are permitted for viewing by reviewers
	 */
	function __construct($request, $args, $roleAssignments, $permitDeclined = false) {
		parent::__construct($request);

		$pdfMergeAccessPolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);

		if (isset($roleAssignments[ROLE_ID_MANAGER])) {
			$pdfMergeAccessPolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, ROLE_ID_MANAGER, $roleAssignments[ROLE_ID_MANAGER]));
		}

		$this->addPolicy($pdfMergeAccessPolicy);

		return $pdfMergeAccessPolicy;
	}
}
