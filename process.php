<?php
require 'vendor/autoload.php';
require 'inc/backend/job_status.php';
include("inc/backend/session.php");

class Process
{
	/* Class constructor */
	function __construct()
	{
		global $session, $database;
		$_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
		$config = $database->getConfigs();

		/* User submitted login form */
		if (isset($_POST['sublogin'])) {
			$this->procLogin();
		}
		/* User submitted registration form */ else if (isset($_POST['subjoin'])) {
			$this->procRegister();
		}
		/* User submitted forgot password form */ else if (isset($_POST['subforgot'])) {
			$this->procForgotPass();
		}
		/* User submitted edit account form */ else if (isset($_POST['subedit'])) {
			$this->procEditAccount();
		}
		/* User new quote form */ else if (isset($_POST['subnewquote'])) {
			$this->procNewQuote();
		}
		/* User new job form */ else if (isset($_POST['subnewjob'])) {
			$this->procNewJob();
		}
		/* User update job details */ else if (isset($_POST['subupdatejob'])) {
			$this->logChangedJobDetails();
			$this->procUpdateJob();
		}
		/* Admin update job after completed job */ else if (isset($_POST['subadminupdatejob'])) {
			// var_dump($_POST['linesman-line-size-select']);
			$this->logChangedJobDetails();
			$this->procUpdateOperatorJobDetails();
			$this->procUpdateLinesmanJobsDetails();
			$this->procUpdateJob();

			$unified_id = $database->getJobDetails($_POST['job-id'])['unified_id'];
			// Notification
			$database->insertNewJobNotification(
				admin_user_levels,
				'job',
				$_POST['job-id'],
				'fa fa-fw fa-pen text-xsmooth',
				"Job JX-" . str_pad($unified_id, 6, "0", STR_PAD_LEFT) . " was admin updated"
			);
		}
		/* User update quote details */ else if (isset($_POST['subupdatequote'])) {
			$this->procUpdateQuote();
		}
		/* Copy a job */ else if (isset($_POST['subcopyjob'])) {
			$this->procCopyJob();
		}
		/* Undo a completed job */ else if (isset($_POST['subundocompletejob'])) {
			$this->procUndoCompletedJob();
		}
		/* Reinstate a job */ else if (isset($_POST['subreinstate'])) {
			$this->procReinstateJob();
		}
		/* Get job details */ else if (isset($_POST['getjobdetails'])) {
			$this->procGetJobDetails();
		}
		/* Get quote details */ else if (isset($_POST['getquotedetails'])) {
			$this->procGetQuoteDetails();
		}
		/* Send quote to client */ else if (isset($_POST['subsemailquote'])) {
			$this->procEmailQuote();
		}
		/* Decline quote from user side */ else if (isset($_POST['subuserdeclinequote'])) {
			$this->procUserDeclineQuote();
		}
		/* Decline quote from customer side */ else if (isset($_POST['subcustomerdeclinequote'])) {
			$this->procCustomerDeclineQuote();
		}
		/* Accept quote from customer side */ else if (isset($_POST['subcustomeracceptquote'])) {
			$this->procCustomerAcceptQuote();
		}
		/* Accept quote and create job from user side */ else if (isset($_POST['subuseracceptquoteandcreatejob'])) {
			$this->procUserAcceptQuoteAndCreateJob();
		}
		/* Create job from quote from user side */ else if (isset($_POST['subusercreatejobfromquote'])) {
			$this->procUserCreateJobFromQuote();
		}
		/* Mark notifications as read */ else if (isset($_POST['submarknotificationsasread'])) {
			$this->procMarkNotificationsAsRead();
		}

		/* Assign operator to job */ else if (isset($_POST['assignoperator'])) {
			$this->procAssignOperatorToJob();
		}
		/* Assign linesmen to job */ else if (isset($_POST['assignLinesmen'])) {
			$this->procAssignLinesmenToJob();
		}
		/* User cancelling job */ else if (isset($_POST['subusercanceljob'])) {
			$this->procUserCancelJob();
		}
		/* Mark site inspection as complete  */ else if (isset($_POST['subcompletesiteinspection'])) {
			$this->procCompleteSiteInspection();
		}
		/* Operator job updated */ else if (isset($_POST['suboperatorupdatejob'])) {
			$this->procOperatorUpdateJob();
		}

		/* Operator job updated */ else if (isset($_POST['subLinesmanUpdateJob'])) {
			$this->proLinesmanUpdateJob();
		}

		/* Linesman Job set to complete by linesman */ else if (isset($_POST['subCompleteLinesmanJob'])) {
			// header("Location: operator_main");
			$this->procCompleteLinesmanJob();
		}

		/* Job set to complete by operator */ else if (isset($_POST['first-concrete-mixer-arrival-time'])) {
			$this->procCompleteJob();
		}
		/* Job set to ready for invoicing */ else if (isset($_POST['jobreadyforinvoicing'])) {
			$this->procJobReadyForInvoicing();
		}
		/* Job set to invoiced */ else if (isset($_POST['jobinvoiced'])) {
			$this->procJobInvoiced();
		}
		/* Job invoice details update */ else if (isset($_POST['updateinvoicedetails'])) {
			$this->logChangedInvoiceDetails();
			$this->procUpdateJobInvoiceDetails();
		}
		/* Update a existing site inspection */ else if (isset($_POST['subupdatesiteinspection'])) {
			$this->procUpdateSiteInspection();
		}
		/**
		 * If the user is sending a job, one of the following if statements will be met
		 */
		/* Send job to operator */ else if (isset($_POST['subsendjobtooperator'])) {
			$this->procEmailJobToOperator();
		}
		/* Send job to operator */ else if (isset($_POST['subsendjobtolinesmen'])) {
			$this->procEmailJobToLinesmen();
		}
		/* Send job to supplier */ else if (isset($_POST['subsendjobtosupplier'])) {
			$this->procEmailJobToSupplier();
		}
		/* Send job to customer */ else if (isset($_POST['subsendjobtocustomer'])) {
			$this->procEmailJobToCustomer();
		}
		/* Send job to layer */ else if (isset($_POST['subsendjobtolayer'])) {
			$this->procEmailJobToLayer();
		}
		/* Send job to foreman */ else if (isset($_POST['subsendjobtoforeman'])) {
			$this->procEmailJobToForeman();
		}

		/*
			2024-09-11
			Switch job color
		*/ else if (isset($_POST['switchJobColor'])) {
			$this->procSwitchJobColor();
		}


		/**
		 * The only other reason user should be directed here
		 * is if he wants to logout, which means user is
		 * logged in currently.
		 */
		else if ($session->logged_in) {
			$this->procLogout();
		}
		/**
		 * Should not get here, which means user is viewing this page
		 * by mistake and therefore is redirected.
		 */
		else {
			header("Location: " . $config['WEB_ROOT'] . $config['home_page']);
		}
	}

	/**
	 * procLogin - Processes the user submitted login form, if errors
	 * are found, the user is redirected to correct the information,
	 * if not, the user is effectively logged in to the system.
	 */
	function procLogin()
	{
		global $session, $form;
		/* Login attempt */
		$retval = $session->login($_POST['user'], $_POST['pass'], isset($_POST['remember']));

		/* Login successful */
		if ($retval === true) {
			if ($session->userlevel == 1 || $session->userlevel == 4) header("Location: operator_main");
			else header("Location: jobs_panel");
		}
		/* Login failed */ else {
			$_SESSION['value_array'] = $_POST;
			$_SESSION['error_array'] = $form->getErrorArray();

			header("Location: " . 'index');
		}
	}

	/**
	 * procLogout - Simply attempts to log the user out of the system
	 * given that there is no logout form to process.
	 */
	function procLogout()
	{
		global $database, $session;
		$config = $database->getConfigs();
		$retval = $session->logout();
		header("Location: " . $config['home_page']);
	}

	/**
	 * procRegister - Processes the user submitted registration form,
	 * if errors are found, the user is redirected to correct the
	 * information, if not, the user is effectively registered with
	 * the system and an email is (optionally) sent to the newly
	 * created user.
	 */
	function procRegister()
	{
		global $database, $session, $form;
		$config = $database->getConfigs();

		/* Checks if registration is disabled */
		if ($config['ACCOUNT_ACTIVATION'] == 4) {
			$_SESSION['reguname']   = $_POST['email'];
			$_SESSION['regsuccess'] = 6;
			header("Location: " . $session->referrer);
		}

		/* Hidden form field captcha designed to catch out auto-fill spambots */
		if (!empty($_POST['killbill'])) {
			$retval = 2;
		} else {
			/* Registration attempt */

			$retval = $session->register($_POST['email'], $_POST['pass'], $_POST['conf_pass'], $_POST['firstname'], $_POST['lastname'], $_POST['phone'], 1);
		}

		/* Registration Successful */
		if ($retval == 0) {
			$_SESSION['reguname']   = $_POST['firstname'] . " " . $_POST['lastname'];
			$_SESSION['regsuccess'] = 0;
			header("Location: " . $session->referrer);
		}
		/* E-mail Activation */ else if ($retval == 3) {
			$_SESSION['reguname']   = $_POST['firstname'] . " " . $_POST['lastname'];
			$_SESSION['regsuccess'] = 3;
			header("Location: " . "index");
		}
		/* Admin Activation */ else if ($retval == 4) {
			$_SESSION['reguname']   = $_POST['firstname'] . " " . $_POST['lastname'];
			$_SESSION['regsuccess'] = 4;
			header("Location: " . "index");
		}
		/* No Activation Needed but E-mail going out */ else if ($retval == 5) {
			$_SESSION['reguname']   = $_POST['firstname'] . " " . $_POST['lastname'];
			$_SESSION['regsuccess'] = 5;
			header("Location: " . $session->referrer);
		}
		/* Error found with form */ else if ($retval == 1) {
			$_SESSION['value_array'] = $_POST;
			$_SESSION['error_array'] = $form->getErrorArray();
			header("Location: " . $session->referrer);
		}
		/* Registration attempt failed */ else if ($retval == 2) {
			$_SESSION['reguname']   = $_POST['firstname'] . " " . $_POST['lastname'];
			$_SESSION['regsuccess'] = 2;
			header("Location: " . $session->referrer);
		}
	}

	/**
	 * procForgotPass - Validates the given username then if
	 * everything is fine, a new password is generated and
	 * emailed to the address the user gave on sign up.
	 */
	function procForgotPass()
	{
		global $database, $session, $mailer, $form;
		$config   = $database->getConfigs();
		/* Username error checking */
		$subuser  = $_POST['user'];
		$subemail = $_POST['email'];
		$field    = "user"; //Use field name for username
		if (!$subuser || strlen($subuser = trim($subuser)) == 0) {
			$form->setError($field, "* Username not entered
				<br>
				");
		} else {
			/* Make sure username is in database */
			$subuser = stripslashes($subuser);
			if (strlen($subuser) < $config['min_user_chars'] || strlen($subuser) > $config['max_user_chars'] || !preg_match("/^[a-z0-9]([0-9a-z_-\s])+$/i", $subuser) || (!$database->usernameTaken($subuser))) {
				$form->setError($field, "* Username does not exist
					<br>
					");
			} else if ($database->checkUserEmailMatch($subuser, $subemail) == 0) {
				$form->setError($field, "* No Match
					<br>
					");
			}
		}

		/* Errors exist, have user correct them */
		if ($form->num_errors > 0) {
			$_SESSION['value_array'] = $_POST;
			$_SESSION['error_array'] = $form->getErrorArray();
		}
		/* Generate new password and email it to user */ else {
			/* Generate new password */
			$newpass = $session->generateRandStr(8);

			/* Get email of user */
			$usrinf = $database->getUserInfo($subuser);
			$email  = $usrinf['email'];

			/* Attempt to send the email with new password */
			if ($mailer->sendNewPass($subuser, $email, $newpass, $config)) {
				/* Email sent, update database */
				$usersalt = $session->generateRandStr(8);
				$newpass  = sha1($usersalt . $newpass);
				$database->updateUserField($subuser, "user_pass", $newpass);
				$database->updateUserField($subuser, "user_salt", $usersalt);
				$_SESSION['forgotpass'] = true;
			}
			/* Email failure, do not change password */ else {
				$_SESSION['forgotpass'] = false;
			}
		}

		header("Location: " . $session->referrer);
	}

	/**
	 * procEditAccount - Attempts to edit the user's account
	 * information, including the password, which must be verified
	 * before a change is made.
	 */
	function procEditAccount()
	{
		global $session, $form;
		/* Account edit attempt */
		$retval = $session->editAccount($_POST['curpass'], $_POST['newpass'], $_POST['conf_newpass'], $_POST['email']);

		/* Account edit successful */
		if ($retval) {
			$_SESSION['useredit'] = true;
			header("Location: " . $session->referrer);
		}
		/* Error found with form */ else {
			$_SESSION['value_array'] = $_POST;
			$_SESSION['error_array'] = $form->getErrorArray();
			header("Location: " . $session->referrer);
		}
	}

	function compress_image($source_url, $destination_url, $quality)
	{
		$info = getimagesize($source_url);

		if ($info['mime'] == 'image/jpeg')
			$image = imagecreatefromjpeg($source_url);
		elseif ($info['mime'] == 'image/gif')
			$image = imagecreatefromgif($source_url);
		elseif ($info['mime'] == 'image/png')
			$image = imagecreatefrompng($source_url);

		//save file
		imagejpeg($image, $destination_url, $quality);

		//return destination file
		return $destination_url;
	}

	function convert_date_nz($date)
	{
		// Convert the date from d-m-Y to Y-m-d
		$c = DateTime::createFromFormat('d/m/Y', $date);
		return $c->format('Y-m-d');
	}


	/* Make notifications 'read' */
	function procMarkNotificationsAsRead()
	{
		global $database;
		global $session;
		$database->markNotificationsAsRead($session->userinfo['ID']);
	}

	function procNewQuote()
	{
		global $database;

		// Convert the date from d-m-Y to Y-m-d
		$job_date = $this->convert_date_nz($_POST['new-job-date']);

		$_POST['new-truck-select'] = (!isset($_POST['new-truck-select'])) ? 'NULL' : $this->post('new-truck-select');
		$_POST['new-job-type-select'] = (!isset($_POST['new-job-type-select'])) ? 'NULL' : $this->post('new-job-type-select');
		$_POST['new-concrete-type-select'] = (!isset($_POST['new-concrete-type-select'])) ? 'NULL' : $this->post('new-concrete-type-select');
		$_POST['new-cubics'] = empty($_POST['new-cubics']) ? 'NULL' : $this->post('new-cubics');
		$_POST['new-mpa'] = empty($_POST['new-mpa']) ? 'NULL' : $this->post('new-mpa');
		$_POST['new-job-timing'] = empty($_POST['new-job-timing']) ? 'NULL' : $this->post('new-job-timing');

		$quote_id = $database->addQuote(
			$_POST['new-customer-select'],
			$job_date,
			$_POST['new-job-timing'],
			$_POST['new-job-address-1'],
			$_POST['new-job-address-2'],
			$_POST['new-job-suburb'],
			$_POST['new-job-city'],
			$_POST['new-job-post-code'],
			$_POST['range-address'],
			$_POST['new-truck-select'],
			$_POST['new-job-type-select'],
			$_POST['new-cubics'],
			$_POST['new-mpa'],
			$_POST['new-concrete-type-select'],
			NULL
		);

		// Add unified link table entry
		$unified_id = $database->addJobQuoteLinkTableEntry(TBL_QUOTES, $quote_id);

		// Notification
		$database->insertNewJobNotification(
			admin_user_levels,
			'quote',
			$quote_id,
			'fa fa-fw fa-info-circle text-info',
			"Quote JX-" . str_pad($unified_id, 6, "0", STR_PAD_LEFT) . " was created"
		);

		header("Location: quote?id=" . $quote_id);
	}

	function procNewJob()
	{
		global $database;

		// Convert the date from d-m-Y to Y-m-d
		$job_date = $this->convert_date_nz($_POST['new-job-date']);

		$_POST['new-truck-select'] = (!isset($_POST['new-truck-select'])) ? 'NULL' : $this->post('new-truck-select');
		$_POST['new-job-type-select'] = (!isset($_POST['new-job-type-select'])) ? 'NULL' : $this->post('new-job-type-select');
		$_POST['new-concrete-type-select'] = (!isset($_POST['new-concrete-type-select'])) ? 'NULL' : $this->post('new-concrete-type-select');
		$_POST['new-cubics'] = empty($_POST['new-cubics']) ? 'NULL' : $this->post('new-cubics');
		$_POST['new-mpa'] = empty($_POST['new-mpa']) ? 'NULL' : $this->post('new-mpa');
		$_POST['new-job-timing'] = empty($_POST['new-job-timing']) ? 'NULL' : $this->post('new-job-timing');

		if (!isset($_POST['new-mix-type-select'])) {
			$_POST['new-mix-type-select'] = 0;
		}

		if (empty($_POST['range-address'])) $_POST['range-address'] = 0;

		$job_id = $database->addJob(
			$_POST['new-customer-select'],
			$job_date,
			$_POST['new-job-timing'],
			$_POST['new-job-address-1'],
			$_POST['new-job-address-2'],
			$_POST['new-job-suburb'],
			$_POST['new-job-city'],
			$_POST['new-job-post-code'],
			$_POST['range-address'],
			$_POST['new-truck-select'],
			$_POST['new-job-type-select'],
			$_POST['new-cubics'],
			$_POST['new-mpa'],
			$_POST['new-concrete-type-select'],
			$_POST['new-mix-type-select']
		);

		// Add unified link table entry
		$unified_id = $database->addJobQuoteLinkTableEntry(TBL_JOBS, $job_id);

		// Notification
		$database->insertNewJobNotification(
			admin_user_levels,
			'job',
			$job_id,
			'fa fa-fw fa-info-circle text-info',
			"Job JX-" . str_pad($unified_id, 6, "0", STR_PAD_LEFT) . " was created"
		);

		header("Location: job?id=" . $job_id);
	}

	function post($name, $string = true)
	{
		$ret = stripslashes($_POST[$name]);
		return $string ? "'" . $ret . "'" : $ret;
	}

	/* Check if multiple variables are not null and empty. https://stackoverflow.com/questions/4993104/using-ifempty-with-multiple-variables-not-in-an-array */
	function mempty()
	{
		foreach (func_get_args() as $arg)
			if ($arg != '' && $arg != 'NULL')
				continue;
			else
				return false;
		return true;
	}

	/* Update a existing job */
	function procUpdateJob()
	{
		// var_dump($_POST['linesman-select']);

		global $database;
		// Convert the date from d-m-Y to Y-m-d
		$job_date = $this->convert_date_nz($_POST['job-date']);
		$job_id = $_POST['job-id'];

		$_POST['operator-select'] = (!isset($_POST['operator-select'])) ? 'NULL' : $this->post('operator-select');
		$_POST['truck-select'] = (!isset($_POST['truck-select'])) ? 'NULL' : $this->post('truck-select');
		$_POST['layer-select'] = (!isset($_POST['layer-select'])) ? 'NULL' : $this->post('layer-select');
		$_POST['foreman-select'] = (!isset($_POST['foreman-select'])) ? 'NULL' : $this->post('foreman-select');
		$_POST['supplier-select'] = (!isset($_POST['supplier-select'])) ? 'NULL' : $this->post('supplier-select');
		$_POST['site-visit-required'] = (!isset($_POST['site-visit-required'])) ? 'NULL' : $_POST['site-visit-required'];
		$_POST['job-type-select'] = (!isset($_POST['job-type-select'])) ? 'NULL' : $this->post('job-type-select');
		$_POST['concrete-type-select'] = (!isset($_POST['concrete-type-select'])) ? 'NULL' : $this->post('concrete-type-select');
		$_POST['cubics'] = empty($_POST['cubics']) ? 'NULL' : $this->post('cubics');
		$_POST['mpa'] = empty($_POST['mpa']) ? 'NULL' : $this->post('mpa');
		$_POST['job-timing'] = empty($_POST['job-timing']) ? 'NULL' : $this->post('job-timing');
		$_POST['establishment-fee'] = empty($_POST['establishment-fee']) ? 'NULL' : $this->post('establishment-fee');
		$linesmenArr = $_POST['linesman-select'] ?? [];
		// var_dump($_POST['linesman-select']);
		// var_dump($linesmenArr, "linesmenArr");
		// $linesmen_json = json_encode($linesmen);
		if (!isset($_POST['am-check'])) $_POST['am-check'] = 0;
		if (!isset($_POST['pm-check'])) $_POST['pm-check'] = 0;
		if (!isset($_POST['passed-check'])) $_POST['passed-check'] = 0;

		// Invoice number is only present after a job is invoiced, so pass an empty value if it's not set
		$invoice_number = isset($_POST['invoice-number']) ? $_POST['invoice-number'] : '';

		$database->updateJob(
			$job_id,
			$_POST['customer-select'],
			$_POST['layer-select'],
			$_POST['supplier-select'],
			$_POST['operator-select'],
			$_POST['foreman-select'],
			$job_date,
			$_POST['job-timing'],
			$_POST['job-address-1'],
			$_POST['job-address-2'],
			$_POST['job-suburb'],
			$_POST['job-city'],
			$_POST['job-post-code'],
			$_POST['site-visit-required'],
			$_POST['truck-select'],
			$_POST['job-type-select'],
			$_POST['cubics'],
			$_POST['mpa'],
			$_POST['concrete-type-select'],
			$_POST['mix-type-select'],
			$_POST['job-instructions'],
			$_POST['ohs-instructions'],
			$_POST['cubic-charge'],
			$_POST['establishment-fee'],
			$_POST['cubic-rate'],
			$_POST['travel-fee'],
			$_POST['discount'],
			$_POST['estimated-pump-time'],
			$invoice_number,
			$_POST['range-address'],
			$_POST['am-check'],
			$_POST['pm-check'],
			$_POST['passed-check'],
			$linesmenArr
		);

		//See waht job status is at this point
		// $job_details = $database->getJobDetails($job_id);
		// echo '12345';
		// var_dump($job_details);

		// Check if operator unassigned, if it is, set assigned flag to 0 (sent_to_operator)
		if ($_POST['operator-select'] == 'NULL' && $database->isJobAssignedToOperator($job_id)) {
			//2024-06-07
			//Disable the code below because it can be linesmen assigned without operator addigned
			$database->markJobAsNotAssigned($job_id);
			$job_details = $database->getJobDetails($job_id);
			$database->insertNewJobNotification(
				admin_user_levels,
				'job',
				$job_id,
				'fa fa-fw fa-user-minus text-warning',
				"Job JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT) . " was unassigned"
			);
		}

		// Update the status of the job if it is a updatable status currently	
		if (!in_array($database->getJobStatus($job_id), no_status_update_array)) {
			// Update the job status if it isn't one of the forbidden ones already
			$site_inspections_complete = $database->isJobSiteInspectionsComplete($job_id);
			$database->setJobStatus($job_id, getUpdatedJobStatus($database->getJobDetails($job_id), $site_inspections_complete));
		}


		header("Location: job?id=" . $job_id);
	}

	function procUpdateQuote()
	{
		global $database;
		// Convert the date from d-m-Y to Y-m-d
		$job_date = $this->convert_date_nz($_POST['job-date']);

		$database->updateQuote(
			$_POST['quote-id'],
			$_POST['customer-select'],
			$job_date,
			$_POST['job-timing'],
			$_POST['job-range'],
			$_POST['job-address-1'],
			$_POST['job-address-2'],
			$_POST['job-suburb'],
			$_POST['job-city'],
			$_POST['job-post-code'],
			$_POST['truck-select'],
			$_POST['job-type-select'],
			$_POST['cubics'],
			$_POST['mpa'],
			$_POST['concrete-type-select'],
			$_POST['mix-type-select'],
			$_POST['quote-summary-notes'],
			$_POST['cubic-charge'],
			$_POST['establishment-fee'],
			$_POST['cubic-rate'],
			$_POST['travel-fee'],
			$_POST['discount'],
			$_POST['estimated-pump-time']
		);

		header("Location: job_panel");
	}

	/* Get job details by job id on the job page */
	function procGetJobDetails()
	{
		global $database;
		$job_details = $database->getJobDetails($_POST['job_id']);
		echo json_encode($job_details);
	}

	/* Get quote details by quote id on the quote page */
	function procGetQuoteDetails()
	{
		global $database;
		$quote_details = $database->getQuoteDetails($_POST['quote_id']);
		echo json_encode($quote_details);
	}

	/* Email a quote to a user recipient */
	function procEmailQuote()
	{
		global $mailer;
		global $database;

		$recipient = $_POST['customer-quote-email-destination'];
		$quote_id = $_POST['quote-id'];

		// Change my email to the recipient variable when in production
		$sent = $mailer->sendQuote($recipient, $quote_id);

		if ($sent) {
			// Notification
			$quote_details = $database->getQuoteDetails($quote_id)[0];
			$database->insertNewJobNotification(
				admin_user_levels,
				'quote',
				$quote_id,
				'fa fa-fw fa-mail-bulk text-info',
				"Quote JX-" . str_pad($quote_details['unified_id'], 6, "0", STR_PAD_LEFT) . " was sent by email to " . $recipient
			);
		}
	}

	/* Decline a quote from the system user side */
	function procUserDeclineQuote()
	{
		global $database;
		global $session;
		$quote_id = $_POST['quote-id'];

		// Notification
		$quote_details = $database->getQuoteDetails($quote_id)[0];
		$database->insertNewJobNotification(
			admin_user_levels,
			'quote',
			$quote_id,
			'fa fa-fw fa-file-invoice text-danger',
			"Quote JX-" . str_pad($quote_details['unified_id'], 6, "0", STR_PAD_LEFT) . " was declined by " . $session->userinfo['user_firstname']
		);

		$database->userDeclineQuote($quote_id);
		header("Location: quote?id=" . $_POST['quote-id']);
	}

	/* Decline a quote from the system customer side */
	function procCustomerDeclineQuote()
	{
		global $database;
		global $session;
		$quote_id = $_POST['quote-id'];

		$reason_choice = $_POST['customer-decline-reason'];

		if ($reason_choice == "Other") {
			$reason = addslashes($_POST['other-reason-textarea']);
		} else $reason = addslashes($reason_choice);

		$database->customerDeclineQuote($quote_id, $reason);

		// Notification
		$quote_details = $database->getQuoteDetails($quote_id)[0];
		$database->insertNewJobNotification(
			admin_user_levels,
			'quote',
			$quote_id,
			'fa fa-fw fa-file-invoice text-danger',
			"Quote JX-" . str_pad($quote_details['unified_id'], 6, "0", STR_PAD_LEFT) . " was declined by the customer"
		);

		header("Location: customer_quote?id=" . $database->translateString($quote_id, 'e'));
	}

	function procCustomerAcceptQuote()
	{
		global $database;
		$quote_id = $database->translateString($_POST['accept-quote-id'], 'd');

		$database->customerAcceptQuote($quote_id);

		// Notification
		$quote_details = $database->getQuoteDetails($quote_id)[0];
		$database->insertNewJobNotification(
			admin_user_levels,
			'quote',
			$quote_id,
			'fa fa-fw fa-file-invoice text-success',
			"Quote JX-" . str_pad($quote_details['unified_id'], 6, "0", STR_PAD_LEFT) . " was accepted by the customer"
		);

		header("Location: customer_quote?id=" . $database->translateString($quote_id, 'e'));
	}

	function procUserAcceptQuoteAndCreateJob()
	{
		global $database;
		global $session;
		$quote_id = $_POST['quote-id'];

		$database->userAcceptQuote($quote_id);
		$new_job_id = $database->createJobFromQuote($quote_id);

		// Notification
		$quote_details = $database->getQuoteDetails($quote_id)[0];
		$database->insertNewJobNotification(
			admin_user_levels,
			'job',
			$new_job_id,
			'fa fa-fw fa-toolbox text-success',
			"Quote JX-" . str_pad($quote_details['unified_id'], 6, "0", STR_PAD_LEFT) . " was accepted and a job has been created by " . $session->userinfo['user_firstname']
		);

		header("Location: job?id=" . $new_job_id);
	}

	function procUserCreateJobFromQuote()
	{
		global $database;
		global $session;
		$quote_id = $_POST['quote-id'];
		$new_job_id = $database->createJobFromQuote($quote_id);

		// Notification
		$quote_details = $database->getQuoteDetails($quote_id)[0];
		$database->insertNewJobNotification(
			admin_user_levels,
			'job',
			$new_job_id,
			'fa fa-fw fa-toolbox text-success',
			"Job JX-" . str_pad($quote_details['unified_id'], 6, "0", STR_PAD_LEFT) . " was created from a quote by " . $session->userinfo['user_firstname']
		);

		header("Location: job?id=" . $new_job_id);
	}
	/* Assign linesman to job and create a notification for both admin and the operator users */
	//    function procAssignLinesmanToJob()
	//    {
	//        global $database;
	//        global $session;
	//
	//        $job_id = $_POST['job_id'];
	//        $job_details = $database->getJobDetails($job_id);
	//
	//        // Check the job has been filled out correctly and is complete before assigning the job
	//        if ($job_details['status'] != 7 && $job_details['status'] != 6) {
	//            echo json_encode(array('error' => 'The job is not ready to be assigned'));
	//            return;
	//        }
	//
	////        // Mark job as assigned to operator
	////        $database->markJobAsAssigned($job_id);
	//
	//        // Notification for all admins
	//        $operator_details = $database->getOperatorDetails($job_details['operator_id']);
	//        $database->insertNewJobNotification(
	//            admin_user_levels,
	//            'job',
	//            $job_id,
	//            'fa fa-fw fa-user-plus text-warning',
	//            "Job JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT) . " was assigned to " . $operator_details['user_firstname'] . " " . $operator_details['user_lastname']
	//        );
	//
	//        // Notification for operator assigned
	//        $database->insertNewNotificationForOperator(
	//            $operator_details['ID'],
	//            'job',
	//            $job_id,
	//            'fa fa-fw fa-user-plus text-warning',
	//            "Job JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT) . " was assigned to you by " . $session->userinfo['user_firstname']
	//        );
	//    }
	/* Assign operator to job and create a notification for both admin and the operator users */
	function procAssignLinesmenToJob()
	{
		global $database;
		global $session;

		$job_id = $_POST['job_id'];
		$job_details = $database->getJobDetails($job_id);

		// Mark job as assigned to operator
		$database->markLinesmanJobAsAssigned($job_id);

		// Notification for all admins
		$linesmenDetails = $database->getLinesmenDetails($job_id);
		// $operator_details = $database->getOperatorDetails($job_details['operator_id']);

		// 创建一个包含所有 linesmen 姓名的数组
		$linesmenNames = array_map(function ($linesman) {
			return $linesman['user_firstname'] . " " . $linesman['user_lastname'];
		}, $linesmenDetails);


		// 将 linesmen 姓名数组连接成一个字符串，用逗号分隔
		$linesmenNamesString = implode(', ', $linesmenNames);


		$database->insertNewJobNotification(
			admin_user_levels,
			'job',
			$job_id,
			'fa fa-fw fa-user-plus text-warning',
			"Job JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT) . " was assigned to " . $linesmenNamesString
		);

		// 遍历每个 linesman，并为每个 linesman 插入通知
		foreach ($linesmenDetails as $linesman) {
			$database->insertNotificationForLinesmen(
				$linesman['ID'],
				'job',
				$job_id,
				'fa fa-fw fa-user-plus text-warning',
				"Job JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT) . " was assigned to you by " . $session->userinfo['user_firstname']
			);
		}
	}

	function procAssignOperatorToJob()
	{
		global $database;
		global $session;

		$job_id = $_POST['job_id'];
		$job_details = $database->getJobDetails($job_id);

		//See waht job status is at this point
		// echo '12345';
		// var_dump($job_details);

		// Check the job has been filled out correctly and is complete before assigning the job
		if ($job_details['status'] != 7 && $job_details['status'] != 6) {
			echo json_encode(array('error' => 'The job is not ready to be assigned'));
			return;
		}

		// Mark job as assigned to operator
		$database->markJobAsAssigned($job_id);

		// Notification for all admins
		$operator_details = $database->getOperatorDetails($job_details['operator_id']);
		$database->insertNewJobNotification(
			admin_user_levels,
			'job',
			$job_id,
			'fa fa-fw fa-user-plus text-warning',
			"Job JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT) . " was assigned to " . $operator_details['user_firstname'] . " " . $operator_details['user_lastname']
		);

		// Notification for operator assigned
		$database->insertNewNotificationForOperator(
			$operator_details['ID'],
			'job',
			$job_id,
			'fa fa-fw fa-user-plus text-warning',
			"Job JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT) . " was assigned to you by " . $session->userinfo['user_firstname']
		);
	}

	/* User cancelling a job from the job form */
	function procUserCancelJob()
	{
		global $database;
		global $session;

		$job_id = $_POST['job-id'];
		$reason = addslashes($_POST['other-reason-textarea']);

		// Cancel the job
		$database->cancelJob($job_id, $reason);

		// Notification for all admins
		$job_details = $database->getJobDetails($job_id);
		$database->insertNewJobNotification(
			admin_user_levels,
			'job',
			$job_id,
			'fa fa-fw fa-exclamation-triangle text-danger',
			"Job JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT) . " was cancelled"
		);

		// Log the change in the change history
		$database->logJobChange($job_id, 'status', 'Job cancelled', $reason, '', '', $job_details['status']);

		// Notify operator is job was assigned
		if ($job_details['sent_to_operator'] == 1) {
			$operator_details = $database->getOperatorDetails($job_details['operator_id']);
			// Notification for operator assigned
			$database->insertNewNotificationForOperator(
				$operator_details['ID'],
				'job',
				$job_id,
				'fa fa-fw fa-exclamation-triangle text-danger',
				"Job JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT) . " cancelled by " . $session->userinfo['user_firstname']
			);
		}

		header("Location: job?id=" . $job_id);
	}

	function procCompleteSiteInspection()
	{
		global $database;
		global $session;

		$job_id = $_POST['job-id'];
		//linesman job
		$linesmenIds = $_POST['linesman-select'] ?? null;
		// echo $linesmenIds;
		// var_dump($linesmenIds);
		$site_inspection_id = $_POST['site-inspection-id'];
		$site_inspection_notes = addslashes($_POST['site-visit-notes']);
		$pump_numbers = json_encode($_POST['truck-select']);
		$photo_count = $database->getPhotoCount();
		$site_inspection_photo_reference = "";
		// Handle photo upload
		for ($i = 0; $i < $photo_count; $i++) {
			$photo = $_FILES['site-photo-input-' . $i];
			var_dump($photo);
			if ($photo['size'] !== 0) {
				echo '123';
				$target_photo = UPLOAD_DIR . basename($photo['name']);
				$uploadOk = 1;
				$imageFileType = strtolower(pathinfo($target_photo, PATHINFO_EXTENSION));
				$check = getimagesize($photo["tmp_name"]);
				// Check file is actually a image
				if ($check == false) {
					echo '222';
					$_SESSION['upload_error'] = "File is not an image.";
				}

				// Check if file already exists
				if (file_exists($target_photo)) {
					echo '333';
					$_SESSION['upload_error'] = "Sorry, file already exists.";
				}
				// Check file size (500000 = ~500KB)
				if ($photo["size"] > 16388608) {
					echo '33345';
					$_SESSION['upload_error'] = "Sorry, your file is too large.";
				}
				// Allow certain file formats
				error_log("imageFileType" . $imageFileType);
				if (
					$imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
					&& $imageFileType != "gif"
				) {
					echo '33366';
					$_SESSION['upload_error'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
				}
				if (move_uploaded_file($photo["tmp_name"], $target_photo)) {
					echo '333777';
					error_log("photo," . $photo . ",target_photo," . $target_photo);
					echo "The file " . basename($photo["name"]) . " has been uploaded.";
					$site_inspection_photo_reference .= $target_photo . ",";
				} else {
					echo '33389';
					$_SESSION['upload_error'] = "Sorry, there was an error uploading your file.";
				}

				// Upload not successful
				if (isset($_SESSION['upload_error'])) {
					echo '33356';
					header("Location: site_inspection?id=" . $site_inspection_id);
					return;
				}
			} else {
				echo '33312312';
				error_log("site_inspection_photo_reference" . $site_inspection_photo_reference);
				$site_inspection_photo_reference .= ",";
			}
		}
		$site_inspection_photo_reference = substr($site_inspection_photo_reference, 0, -1);
		$database->updateSiteInspectionAsComplete($job_id, $site_inspection_id, $site_inspection_photo_reference, $pump_numbers, $site_inspection_notes, $session->userinfo['ID'], $linesmenIds);

		// Notification for all admins
		$job_details = $database->getJobDetails($job_id);
		$database->insertNewJobNotification(
			admin_user_levels,
			'site_inspection',
			$site_inspection_id,
			'fa fa-fw fa-eye text-success',
			"Job JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT) . " site inspection completed by " . $session->userinfo['user_firstname']
		);

		// Notification for operator completing it
		$database->insertNewNotificationForOperator(
			$session->userinfo['ID'],
			'site_inspection',
			$site_inspection_id,
			'fa fa-fw fa-eye text-info',
			"You completed the site inspection for Job JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT)
		);

		// Update the job status
		$site_inspections_complete = $database->isJobSiteInspectionsComplete($job_id);
		if (!in_array($database->getJobStatus($job_id), no_status_update_array)) {
			// Update the job status if it isn't one of the forbidden ones already
			$database->setJobStatus($job_id, getUpdatedJobStatus($database->getJobDetails($job_id), $site_inspections_complete));
		}

		// Append site inspection notes to the job's instructions
		$database->appendSiteInspectionNotesToJobInstruction($job_id, $job_details['job_instructions'], $site_inspection_notes);

		header("Location: site_inspection?id=" . $site_inspection_id);
	}

	/**
	 * 2024-08-02
	 * Complete linesman job
	 */
	function procCompleteLinesmanJob()
	{
		global $session;
		global $database;
		$job_id = $_POST['job-id'];
		$linesman_job_id = $_POST['linesman-job-id'];
		// echo $job_id . $linesman_job_id;
		$linesman_notes = addslashes($_POST['linesman-job-notes']);
		// echo $linesman_notes;

		$database->updateLinesmanJobAsComplete(
			$job_id,
			$linesman_job_id,
		);

		// Notification for all admins
		$job_details = $database->getJobDetails($job_id);
		$database->insertNewJobNotification(
			admin_user_levels,
			'job',
			$job_id,
			'fa fa-fw fa-check-circle text-success',
			"Linesman Job JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT) . " completed by " . $session->userinfo['user_firstname']
		);

		// Notification for operator completing it
		$database->insertNewNotificationForOperator(
			$session->userinfo['ID'],
			'job',
			$job_id,
			'fa fa-fw fa-check-circle text-success',
			"You completed Linesman Job JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT)
		);

		header("Location: operator_main");
	}

	/* Operator invoked function to complete the job */
	function procCompleteJob()
	{
		global $session;
		global $database;
		$job_id = $_POST['job-id'];

		$operator_notes = addslashes($_POST['operator-job-notes']);

		// job-timing is the actual time the job starts
		$database->updateJobAsComplete($job_id, $_POST['actual-cubics'], $_POST['job-finish-time'], $_POST['actual-job-timing'], $_POST['onsite-washout'], $_POST['onsite-disposal'], $operator_notes);

		// Notification for all admins
		$job_details = $database->getJobDetails($job_id);
		$database->insertNewJobNotification(
			admin_user_levels,
			'job',
			$job_id,
			'fa fa-fw fa-check-circle text-success',
			"Job JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT) . " completed by " . $session->userinfo['user_firstname']
		);

		// Load current truck invoice details on job completion
		//$database->updateJobOrQuoteTruckDetails(TBL_JOBS, $job_details['truck_id'], $job_id);

		// Notification for operator completing it
		$database->insertNewNotificationForOperator(
			$session->userinfo['ID'],
			'job',
			$job_id,
			'fa fa-fw fa-check-circle text-success',
			"You completed Job JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT)
		);

		header("Location: operator_complete_job?job_no=JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT));
	}

	function procEmailJobToOperator()
	{
		global $mailer;
		global $database;
		$job_id = $_POST['job-id'];
		$operator_details = $database->getOperatorDetails($_POST['operator-id']);
		$operator_email = $operator_details['user_email'];
		$operator_name = $operator_details['user_firstname'];

		// Change my email to the recipient variable when in production
		$sent = $mailer->sendOperatorJob($operator_email, $job_id, $operator_name, "Operator");

		if ($sent) {
			$database->jobSentToOperator($job_id); // Set the date sent to now indicating job has been sent

			// Notification
			$job_details = $database->getJobDetails($job_id);
			$database->insertNewJobNotification(
				admin_user_levels,
				'job',
				$job_id,
				'fa fa-fw fa-mail-bulk text-info',
				"Job JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT) . " was sent to operator"
			);
		}
	}

	function procEmailJobToLinesmen()
	{
		global $mailer;
		global $database;

		$job_id = $_POST['job-id'];
		// var_dump($job_id, "job_id");

		$linesmanId = $_POST['linesman-id'];
		// var_dump($linesmanId);
		// 处理每个id
		$linesmen_details = $database->getLinesmanDetails($linesmanId);
		// var_dump($linesmen_details, "linesmen_details");

		$linesman_email = $linesmen_details['user_email'];
		$linesman_name = $linesmen_details['user_firstname'];

		$sent = $mailer->sendOperatorJob($linesman_email, $job_id, $linesman_name, "Linesman");
		// echo "1111";
		if ($sent) {
			$database->jobSentToLinesman($job_id);
		} else {
			error_log("Failed to send job to linesman: " . $linesman_name);
		}
	}

	function procEmailJobToSupplier()
	{
		global $mailer;
		global $database;

		$job_id = $_POST['job-id'];

		$supplier_details = $database->getSupplierDetails($_POST['supplier-id']);
		$supplier_email = $supplier_details['email'];
		$supplier_name = $supplier_details['supplier_name'];

		// Change my email to the recipient variable when in production
		$sent = $mailer->sendJob($supplier_email, $job_id, $supplier_name, "Supplier");
		$database->jobSentToSupplier($job_id); // Set the date sent to now indicating job has been sent

		if ($sent) {
			// Notification
			$job_details = $database->getJobDetails($job_id);
			$database->insertNewJobNotification(
				admin_user_levels,
				'job',
				$job_id,
				'fa fa-fw fa-mail-bulk text-info',
				"Job JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT) . " was sent to supplier"
			);
		}
	}

	function procEmailJobToCustomer()
	{
		global $mailer;
		global $database;

		$job_id = $_POST['job-id'];

		$customer_details = $database->getCustomerDetails($_POST['customer-id']);
		$customer_email = $customer_details['email'];
		$customer_name = $customer_details['first_name'];

		// Change my email to the recipient variable when in production
		$sent = $mailer->sendJob($customer_email, $job_id, $customer_name, "Customer");

		if ($sent) {
			$database->jobSentToCustomer($job_id); // Set the date sent to now indicating job has been sent

			// Notification
			$job_details = $database->getJobDetails($job_id);
			$database->insertNewJobNotification(
				admin_user_levels,
				'job',
				$job_id,
				'fa fa-fw fa-mail-bulk text-info',
				"Job JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT) . " was sent to customer"
			);
		}
	}

	function procEmailJobToLayer()
	{
		global $mailer;
		global $database;

		$job_id = $_POST['job-id'];

		$layer_details = $database->getLayerDetails($_POST['layer-id']);
		$layer_email = $layer_details['email'];
		$layer_name = $layer_details['layer_name'];

		// Change my email to the recipient variable when in production
		$sent = $mailer->sendJob($layer_email, $job_id, $layer_name, "Layer");

		if ($sent) {
			$database->jobSentToLayer($job_id); // Set the date sent to now indicating job has been sent

			// Notification
			$job_details = $database->getJobDetails($job_id);
			$database->insertNewJobNotification(
				admin_user_levels,
				'job',
				$job_id,
				'fa fa-fw fa-mail-bulk text-info',
				"Job JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT) . " was sent to layer"
			);
		}
	}

	function procEmailJobToForeman()
	{
		global $mailer;
		global $database;

		$job_id = $_POST['job-id'];

		$foreman_details = $database->getForemanDetails($_POST['foreman-id']);
		$foreman_email = $foreman_details['email'];
		$foreman_name = $foreman_details['foreman_name'];

		// Change my email to the recipient variable when in production
		$sent = $mailer->sendJob($foreman_email, $job_id, $foreman_name, "Foreman");

		if ($sent) {
			$database->jobSentToForeman($job_id); // Set the date sent to now indicating job has been sent

			// Notification
			$job_details = $database->getJobDetails($job_id);
			$database->insertNewJobNotification(
				admin_user_levels,
				'job',
				$job_id,
				'fa fa-fw fa-mail-bulk text-info',
				"Job JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT) . " was sent to foreman"
			);
		}
	}

	/**
	 * 2024-08-02
	 */
	function proLinesmanUpdateJob()
	{
		global $database;
		$job_id = $_POST['job-id'];
		$linesman_job_id = $_POST['linesman-job-id'];
		// var_dump($_POST['line-size-select']);
		if (isset($_POST['actual-job-timing'])) {
			$database->updateLinemanJobDetail($linesman_job_id, 'actual_job_timing', $_POST['actual-job-timing']);
		}
		if (isset($_POST['job-finish-time']) && !empty($_POST['job-finish-time'])) {
			$database->updateLinemanJobDetail($linesman_job_id, 'job_time_finished', $_POST['job-finish-time']);
		}
		if (isset($_POST['line-size-select']) && !empty($_POST['line-size-select'])) {
			$database->updateLinemanJobDetail($linesman_job_id, 'line_size_select', $_POST['line-size-select']);
		} else {
			// echo '123';
			$database->updateLinemanJobDetail($linesman_job_id, 'line_size_select', 0);
		}
		if (isset($_POST['linesman-job-notes']) && !empty($_POST['linesman-job-notes'])) {
			$database->updateLinemanJobDetail($linesman_job_id, 'linesman_notes', $_POST['linesman-job-notes']);
		}
		echo "Seamless update via proLinesmanUpdateJob for linesman job: " . $linesman_job_id;
	}

	/* Ajax called function */
	function procOperatorUpdateJob()
	{
		global $database;

		$job_id = $_POST['job-id'];
		if (isset($_POST['actual-job-timing'])) {
			$database->updateJobDetail($job_id, 'actual_job_timing', $_POST['actual-job-timing']);
		}
		if (isset($_POST['actual-cubics']) && is_numeric($_POST['actual-cubics'])) {
			$database->updateJobDetail($job_id, 'actual_cubics', $_POST['actual-cubics']);
		}
		if (isset($_POST['first-concrete-mixer-arrival-time']) && !empty($_POST['first-concrete-mixer-arrival-time'])) {
			$database->updateJobDetail($job_id, 'first_mixer_arrival_time', $_POST['first-concrete-mixer-arrival-time']);
		}
		if (isset($_POST['job-finish-time']) && !empty($_POST['job-finish-time'])) {
			$database->updateJobDetail($job_id, 'job_time_finished', $_POST['job-finish-time']);
		}
		if (isset($_POST['onsite-washout'])) {
			$database->updateJobDetail($job_id, 'onsite_washout', $_POST['onsite-washout']);
		}
		if (isset($_POST['onsite-disposal'])) {
			$database->updateJobDetail($job_id, 'onsite_disposal', $_POST['onsite-disposal']);
		}
		if (isset($_POST['operator-job-notes']) && !empty($_POST['operator-job-notes'])) {
			$database->updateJobDetail($job_id, 'operator_notes', $_POST['operator-job-notes']);
		}
		echo 'Seamless update via procoperatorupdatejob';
	}

	/* Ready for invoicing status change for job. Generally jobs that are complete will call this function */
	function procJobReadyForInvoicing()
	{
		global $database;
		$job_id = $_POST['job-id'];

		/* Check if the job has invoice data in the invoice_data table */
		if (!$database->getJobInvoiceData($job_id)) {
			// Update various details and add entry in invoice data for job
			$database->addInvoiceDataForJob(
				$job_id,
				$_POST['invoice-rate-type'],
				$_POST['invoice-establishment-fee'],
				$_POST['invoice-travel-fee'],
				$_POST['invoice-actual-cubics'],
				$_POST['invoice-concrete-rate'],
				$_POST['invoice-cubic-rate'],
				$_POST['invoice-actual-hours'],
				$_POST['invoice-truck-rate'],
				$_POST['invoice-hourly-rate'],
				$_POST['invoice-washdown-fee'],
				$_POST['invoice-disposal-fee'],
				$_POST['invoice-special-rate'],
				$_POST['invoice-special-1'],
				$_POST['invoice-special-2'],
				$_POST['extra-cost-name-1'],
				$_POST['extra-cost-name-2'],
				$_POST['invoice-discount'],
				GST
			);
			$database->updateJobDetail($job_id, 'job_range', $_POST['invoice-range']);

			// Update job's status to ready for invoicing
			$database->markJobAsReadyForInvoicing($job_id);

			// Notification
			$job_details = $database->getJobDetails($job_id);
			$database->insertNewJobNotification(
				admin_user_levels,
				'job',
				$job_id,
				'fa fa-fw fa-file-invoice-dollar text-info',
				"Job JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT) . " is ready to invoice"
			);
		}

		header("Location: jobs_panel?filter=complete-box");
	}

	function procUpdateJobInvoiceDetails()
	{
		global $database;
		$job_id = $_POST['job-id'];

		$database->updateInvoiceDataForJob($job_id, $_POST['invoice-rate-type'], $_POST['invoice-establishment-fee'], $_POST['invoice-travel-fee'], $_POST['invoice-actual-cubics'], $_POST['invoice-concrete-rate'], $_POST['invoice-cubic-rate'], $_POST['invoice-actual-hours'], $_POST['invoice-truck-rate'], $_POST['invoice-hourly-rate'], $_POST['invoice-washdown-fee'], $_POST['invoice-disposal-fee'], $_POST['invoice-special-rate'], $_POST['invoice-special-1'], $_POST['invoice-special-2'], $_POST['extra-cost-name-1'], $_POST['extra-cost-name-2'], $_POST['invoice-discount'], GST);
		$database->updateJobDetail($job_id, 'job_range', $_POST['invoice-range']);

		// Notification
		$job_details = $database->getJobDetails($job_id);
		$database->insertNewJobNotification(
			admin_user_levels,
			'job',
			$job_id,
			'fa fa-fw fa-file-invoice-dollar text-warning',
			"Job JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT) . " invoice details were updated"
		);

		header("Location: job?id=" . $job_id);
	}

	function procJobInvoiced()
	{
		global $database;
		$job_id = $_POST['job-id'];

		$database->markJobAsInvoiced($job_id);
		$database->updateJobInvoiceNumber($job_id, $_POST['new-invoice-number']);

		// Notification
		$job_details = $database->getJobDetails($job_id);
		$database->insertNewJobNotification(
			admin_user_levels,
			'job',
			$job_id,
			'fa fa-fw fa-file-invoice-dollar text-success',
			"Job JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT) . " has been invoiced"
		);

		header("Location: jobs_panel?filter=ready-invoicing-box"); // Redirect to jobs to be invoiced
	}

	/**
	 * Update linesman jobs when admin update
	 */
	function procUpdateLinesmanJobsDetails()
	{
		global $database;
		$linesmanJobIdsArr = $_POST['linesman_job_id'];
		$actualJobTimingsArr = $_POST['linesman-actual-job-timing'];
		$jobFinishTimesArr = $_POST['linesman-job-finish-time'];
		$linesSizeSelectsArr = $_POST['linesman-line-size-select'];
		$linesmanNotesArr = $_POST['linesman-job-notes'];
		// var_dump($linesmanJobIdsArr, $actualJobTimingsArr, $jobFinishTimesArr, $linesSizeSelectsArr, $linesmanNotesArr);

		foreach ($linesmanJobIdsArr as $linesmanJobId) {
			$actualJobTiming = $actualJobTimingsArr[$linesmanJobId] ?? null;
			$jobFinishTime = $jobFinishTimesArr[$linesmanJobId] ?? null;
			$lineSizeSelect = $linesSizeSelectsArr[$linesmanJobId] ?? null;
			$jobNote = $linesmanNotesArr[$linesmanJobId] ?? null;

			$database->updateLinesmanJobsDetails(
				$linesmanJobId,
				$actualJobTiming,
				$jobFinishTime,
				$lineSizeSelect,
				$jobNote
			);
		}
	}

	function procUpdateOperatorJobDetails()
	{
		global $database;
		$job_id = $_POST['job-id'];

		$database->updateOperatorJobDetails(
			$job_id,
			$_POST['actual-job-timing'],
			$_POST['actual-cubics'],
			$_POST['first-concrete-mixer-arrival-time'],
			$_POST['job-finish-time'],
			$_POST['onsite-washout'],
			$_POST['onsite-disposal'],
			$_POST['operator-job-notes']
		);
	}

	function logChangedInvoiceDetails()
	{
		global $database;
		$job_id = $_POST['job-id'];

		$job_details = $database->getJobDetails($job_id);
		$details = $database->getJobInvoiceData($job_id);

		$changed_details_array = array();

		// Rate type
		$field = 'rate_type';
		$name = 'invoice-rate-type';
		if ($_POST[$name] != $details[$field]) {
			array_push($changed_details_array, array($field, "", $_POST[$name], $details[$field]));
		}

		// Establishment fee
		$field = 'establishment_fee';
		$name = 'invoice-establishment-fee';
		if ($_POST[$name] != $details[$field]) {
			array_push($changed_details_array, array($field, "", $_POST[$name], $details[$field]));
		}

		// Travel fee
		$field = 'travel_fee';
		$name = 'invoice-travel-fee';
		if ($_POST[$name] != $details[$field]) {
			array_push($changed_details_array, array($field, "", $_POST[$name], $details[$field]));
		}

		// Actual cubics
		$field = 'actual_cubics';
		$name = 'invoice-actual-cubics';
		if ($_POST[$name] != $details[$field]) {
			array_push($changed_details_array, array($field, "", $_POST[$name], $details[$field]));
		}

		// Concrete charge
		$field = 'concrete_charge';
		$name = 'invoice-concrete-rate';
		if ($_POST[$name] != $details[$field]) {
			array_push($changed_details_array, array($field, "", $_POST[$name], $details[$field]));
		}

		// Cubic rate
		$field = 'cubic_rate';
		$name = 'invoice-cubic-rate';
		if ($_POST[$name] != $details[$field]) {
			array_push($changed_details_array, array($field, "", $_POST[$name], $details[$field]));
		}

		// Truck hourly rate
		$field = 'truck_hourly_rate';
		$name = 'invoice-truck-rate';
		if ($_POST[$name] != $details[$field]) {
			array_push($changed_details_array, array($field, "", $_POST[$name], $details[$field]));
		}

		// Hourly rate
		$field = 'hourly_rate';
		$name = 'invoice-hourly-rate';
		if ($_POST[$name] != $details[$field]) {
			array_push($changed_details_array, array($field, "", $_POST[$name], $details[$field]));
		}

		// Actual hours
		$field = 'actual_job_hours';
		$name = 'invoice-actual-hours';
		if ($_POST[$name] != $details[$field]) {
			array_push($changed_details_array, array($field, "", $_POST[$name], $details[$field]));
		}

		// Washdown fee
		$field = 'washdown_fee';
		$name = 'invoice-washdown-fee';
		if ($_POST[$name] != $details[$field]) {
			array_push($changed_details_array, array($field, "", $_POST[$name], $details[$field]));
		}

		// Disposal fee
		$field = 'disposal_fee';
		$name = 'invoice-disposal-fee';
		if ($_POST[$name] != $details[$field]) {
			array_push($changed_details_array, array($field, "", $_POST[$name], $details[$field]));
		}


		// Special rate
		$field = 'special_rate';
		$name = 'invoice-special-rate';
		if ($_POST[$name] != $details[$field]) {
			array_push($changed_details_array, array($field, "", $_POST[$name], $details[$field]));
		}

		// Extra cost 1
		$field = 'special_rate_1';
		$name = 'invoice-special-1';
		if ($_POST[$name] != $details[$field]) {
			array_push($changed_details_array, array($field, "", $_POST[$name], $details[$field]));
		}

		// Extra cost 2
		$field = 'special_rate_2';
		$name = 'invoice-special-2';
		if ($_POST[$name] != $details[$field]) {
			array_push($changed_details_array, array($field, "", $_POST[$name], $details[$field]));
		}

		// Extra cost description 1
		$field = 'special_cost_description_1';
		$name = 'extra-cost-name-1';
		if ($_POST[$name] != $details[$field]) {
			array_push($changed_details_array, array($field, "", $_POST[$name], $details[$field]));
		}

		// Extra cost description 2
		$field = 'special_cost_description_2';
		$name = 'extra-cost-name-2';
		if ($_POST[$name] != $details[$field]) {
			array_push($changed_details_array, array($field, "", $_POST[$name], $details[$field]));
		}

		// Discount
		$field = 'discount';
		$name = 'invoice-discount';
		if ($_POST[$name] != $details[$field]) {
			array_push($changed_details_array, array($field, "", $_POST[$name], $details[$field]));
		}

		// Log the changes in the database
		foreach ($changed_details_array as $change) {
			$database->logJobChange($job_id, $change[0], $change[1], $_POST['invoice-reason-to-edit'], $change[3], $change[2], $job_details['status']);
		}
	}

	function logChangedJobDetails()
	{
		global $database;
		$job_id = $_POST['job-id'];
		$details = $database->getJobDetails($job_id);
		$linesmanJobsDetails = $database->getAllLinesmanJobsByJobId($job_id);
		// var_dump($details);
		// var_dump($linesmanJobsDetails);
		// var_dump($_POST['linesman-actual-job-timing']);

		$changed_details_array = array();

		/**
		 * log changes for linesman jobs
		 */
		if (isset($_POST['linesman-actual-job-timing'])) {
			foreach ($_POST['linesman-actual-job-timing'] as $linesman_job_id => $new_actual_job_timing) {
				foreach ($linesmanJobsDetails as $linesmanJob) {
					if ($linesmanJob['id'] == $linesman_job_id) {
						$old_actual_job_timing = $linesmanJob['actual_job_timing'];
						//Compare new timing and old timing
						if ($new_actual_job_timing != $old_actual_job_timing) {
							array_push($changed_details_array, array("linesman_actual_job_timing", "", $new_actual_job_timing, $old_actual_job_timing));
						}
					}
				}
			}
		}
		// var_dump($_POST['linesman-job-finish-time']);
		if (isset($_POST['linesman-job-finish-time'])) {
			foreach ($_POST['linesman-job-finish-time'] as $linesman_job_id => $new_job_time_finished) {
				foreach ($linesmanJobsDetails as $linesmanJob) {
					if ($linesmanJob['id'] == $linesman_job_id) {
						$old_job_time_finished = $linesmanJob['job_time_finished'];
						//Compare new timing and old timing
						if ($new_job_time_finished != $old_job_time_finished) {
							array_push($changed_details_array, array("linesman_job_time_finished", "", $new_job_time_finished, $old_job_time_finished));
						}
					}
				}
			}
		}
		// var_dump($_POST['linesman-job-finish-time']);
		if (isset($_POST['linesman-line-size-select'])) {
			foreach ($_POST['linesman-line-size-select'] as $linesman_job_id => $new_line_size_select) {
				foreach ($linesmanJobsDetails as $linesmanJob) {
					if ($linesmanJob['id'] == $linesman_job_id) {
						$old_line_size_select = $linesmanJob['line_size_select'];
						//Compare new timing and old timing
						if ($new_line_size_select != $old_line_size_select) {
							array_push($changed_details_array, array("line_size_select", "", $new_line_size_select, $old_line_size_select));
						}
					}
				}
			}
		}
		if (isset($_POST['linesman-job-notes'])) {
			foreach ($_POST['linesman-job-notes'] as $linesman_job_id => $new_linesman_job_notes) {
				foreach ($linesmanJobsDetails as $linesmanJob) {
					if ($linesmanJob['id'] == $linesman_job_id) {
						$old_linesman_job_notes = $linesmanJob['linesman_notes'];
						//Compare new timing and old timing
						if ($new_linesman_job_notes != $old_linesman_job_notes) {
							array_push($changed_details_array, array("linesman_job_notes", "", $new_linesman_job_notes, $old_linesman_job_notes));
						}
					}
				}
			}
		}

		// Job date
		if (isset($_POST['job-date'])) {
			$job_date = date("Y-m-d", strtotime(str_replace("/", "-", $_POST['job-date'])));
			if ($job_date != $details['job_date']) {
				array_push($changed_details_array, array("job_date", "", $job_date, $details['job_date']));
			}
		}

		// Job timing
		if (isset($_POST['job-timing'])) {
			$job_timing = date('H:i', strtotime($details['job_timing']));
			if (date('H:i', strtotime($_POST['job-timing'])) != $job_timing) {
				array_push($changed_details_array, array("job_timing", "", $_POST['job-timing'], $job_timing));
			}
		}

		// Job address 1
		if (isset($_POST['job-address-1']))
			if ($_POST['job-address-1'] != $details['job_addr_1']) {
				array_push($changed_details_array, array("job_addr_1", "", $_POST['job-address-1'], $details['job_addr_1']));
			}

		// Job address 2
		if (isset($_POST['job-address-2']))
			if ($_POST['job-address-2'] != $details['job_addr_2']) {
				array_push($changed_details_array, array("job_addr_2", "", $_POST['job-address-2'], $details['job_addr_2']));
			}

		// Suburb
		if (isset($_POST['job-suburb']))
			if ($_POST['job-suburb'] != $details['job_suburb']) {
				array_push($changed_details_array, array("job_suburb", "", $_POST['job-suburb'], $details['job_suburb']));
			}

		// City
		if (isset($_POST['job-city']))
			if ($_POST['job-city'] != $details['job_city']) {
				array_push($changed_details_array, array("job_city", "", $_POST['job-city'], $details['job_city']));
			}

		// Post Code
		if (isset($_POST['job-post-code']))
			if ($_POST['job-post-code'] != $details['job_post_code']) {
				array_push($changed_details_array, array("job_post_code", "", $_POST['job-post-code'], $details['job_post_code']));
			}

		// Job type
		if (isset($_POST['job-type-select']))
			if ($_POST['job-type-select'] != $details['job_type']) {
				$old_value = $database->getNameForJobTypeID($details['job_type']);
				$log_message = $old_value != false ? $old_value['type_name'] . " -> " . $database->getNameForJobTypeID($_POST['job-type-select'])['type_name'] : 'None' . " -> " . $database->getNameForJobTypeID($_POST['job-type-select'])['type_name'];
				array_push($changed_details_array, array("job_type", addslashes($log_message), $_POST['job-type-select'], $details['job_type']));
			}

		if (isset($_POST['cubics']))
			if ($_POST['cubics'] != $details['cubics']) {
				array_push($changed_details_array, array("cubics", "", $_POST['cubics'], $details['cubics']));
			}

		if (isset($_POST['mpa']))
			if ($_POST['mpa'] != $details['mpa']) {
				array_push($changed_details_array, array("mpa", "", $_POST['mpa'], $details['mpa']));
			}

		// Concrete type
		if (isset($_POST['concrete-type-select']))
			if ($_POST['concrete-type-select'] != $details['concrete_type']) {
				$old_value = $database->getConcreteDetails($details['concrete_type']);
				$log_message = $old_value != false ? $old_value['concrete_name'] . " -> " . $database->getConcreteDetails($_POST['concrete-type-select'])['concrete_name'] : 'None' . " -> " . $database->getConcreteDetails($_POST['concrete-type-select'])['concrete_name'];
				array_push($changed_details_array, array("concrete_type", addslashes($log_message), $_POST['concrete-type-select'], $details['concrete_type']));
			}

		// Truck
		if (isset($_POST['truck-select']))
			if ($_POST['truck-select'] != $details['truck_id']) {
				$old_value = $database->getTruckDetails($details['truck_id']);
				$log_message = $old_value != false ? $old_value['number_plate'] . " -> " . $database->getTruckDetails($_POST['truck-select'])['number_plate'] : 'None' . " -> " . $database->getTruckDetails($_POST['truck-select'])['number_plate'];
				array_push($changed_details_array, array("truck_id", addslashes($log_message), $_POST['truck-select'], $details['truck_id']));
			}

		// Customer
		if (isset($_POST['customer-select']))
			if ($_POST['customer-select'] != $details['customer_id']) {
				$old_value = $database->getCustomerDetails($details['customer_id']);
				$log_message = $old_value != false ? $old_value['name']  . " -> " . $database->getCustomerDetails($_POST['customer-select'])['name'] : 'None' . " -> " . $database->getCustomerDetails($_POST['customer-select'])['name'];
				array_push($changed_details_array, array("customer_id", addslashes($log_message), $_POST['customer-select'], $details['customer_id']));
			}

		// Foreman
		if (isset($_POST['foreman-select']))
			if ($_POST['foreman-select'] != $details['foreman_id']) {
				$old_value = $database->getForemanDetails($details['foreman_id']);
				$new_value = $database->getForemanDetails($_POST['foreman-select']);
				$log_message = $old_value != false ? $old_value['first_name'] . ' ' . $old_value['last_name'] . ' (' . $old_value['company'] . ')' . " -> " . $new_value['first_name'] . ' ' . $new_value['last_name'] . ' (' . $new_value['company'] . ')' : 'None' . " -> " . $new_value['first_name'] . ' ' . $new_value['last_name'] . ' (' . $new_value['company'] . ')';
				array_push($changed_details_array, array("foreman_id", addslashes($log_message), $_POST['foreman-select'], $details['foreman_id']));
			}

		// Layer
		if (isset($_POST['layer-select']))
			if ($_POST['layer-select'] != $details['layer_id']) {
				$old_value = $database->getLayerDetails($details['layer_id']);
				$new_value = $database->getLayerDetails($_POST['layer-select']);
				$log_message = $old_value != false ? $old_value['layer_name'] . ' - ' . $old_value['layer_firstname'] . " -> " . $new_value['layer_name'] . ' - ' . $new_value['layer_firstname'] : 'None' . " -> " . $new_value['layer_name'] . ' - ' . $new_value['layer_firstname'];
				array_push($changed_details_array, array("layer_id", addslashes($log_message), $_POST['layer-select'], $details['layer_id']));
			}

		// Supplier
		if (isset($_POST['supplier-select']))
			if ($_POST['supplier-select'] != $details['supplier_id']) {
				$old_value = $database->getSupplierDetails($details['supplier_id']);
				$log_message = $old_value != false ? $old_value['supplier_name'] . " -> " . $database->getSupplierDetails($_POST['supplier-select'])['supplier_name'] : 'None' . " -> " . $database->getSupplierDetails($_POST['supplier-select'])['supplier_name'];
				array_push($changed_details_array, array("supplier_id", addslashes($log_message), $_POST['supplier-select'], $details['supplier_id']));
			}

		// Operator
		if (isset($_POST['operator-select']))
			if ($_POST['operator-select'] != $details['operator_id']) {
				$old_value = $database->getOperatorDetails($details['operator_id']);
				$log_message = $old_value != false ? $old_value['user_firstname'] . " -> " . $database->getOperatorDetails($_POST['operator-select'])['user_firstname'] : 'None' . " -> " . $database->getOperatorDetails($_POST['operator-select'])['user_firstname'];
				array_push($changed_details_array, array("operator_id", addslashes($log_message), $_POST['operator-select'], $details['operator_id']));
			}
		/**
		 * 2024-08-01 Linesmen 
		 */
		// Linesman select
		if (isset($_POST['linesman-select']) && is_array($_POST['linesman-select']) && is_array($details['linesman_user_ids'])) {
			//Compare the two arrs
			if (!empty(array_diff($_POST['linesman-select'], $details['linesman_user_ids'])) || !empty(array_diff($details['linesman_user_ids'], $_POST['linesman-select']))) {
				// Transfer arr into string for logging
				$old_value = implode(',', $details['linesman_user_ids']);
				$new_value = implode(',', $_POST['linesman-select']);

				// create log message
				$log_message = $old_value . " -> " . $new_value;

				// record into log message
				array_push($changed_details_array, array("linesman_user_ids", addslashes($log_message), $new_value, $old_value));
			}
		}

		if (isset($_POST['job-instructions']))
			if ($_POST['job-instructions'] != $details['job_instructions']) {
				array_push($changed_details_array, array("job_instructions", "", $_POST['job-instructions'], $details['job_instructions']));
			}

		if (isset($_POST['ohs-instructions']))
			if ($_POST['ohs-instructions'] != $details['ohs_instructions']) {
				array_push($changed_details_array, array("ohs_instructions", "", $_POST['ohs-instructions'], $details['ohs_instructions']));
			}

		/* Check the builder details are even included in this update before logging changes */
		/* It could be an update before the job is complete, so no operator details will be present */
		if (isset($_POST['onsite-disposal'])) {
			$actual_timing = date('H:i', strtotime($details['actual_job_timing']));
			if (date('H:i', strtotime($_POST['actual-job-timing'])) != $actual_timing) {
				array_push($changed_details_array, array("actual_job_timing", "", $_POST['actual-job-timing'], $actual_timing));
			}

			$mixer_arrival_time = date('H:i', strtotime($details['first_mixer_arrival_time']));
			if (date('H:i', strtotime($_POST['first-concrete-mixer-arrival-time'])) != $mixer_arrival_time) {
				array_push($changed_details_array, array("first_mixer_arrival_time", "", $_POST['first-concrete-mixer-arrival-time'], $mixer_arrival_time));
			}

			if ($_POST['actual-cubics'] != $details['actual_cubics']) {
				array_push($changed_details_array, array("actual_cubics", "", $_POST['actual-cubics'], $details['actual_cubics']));
			}

			$finish_time = date('H:i', strtotime($details['job_time_finished']));
			if (date('H:i', strtotime($_POST['job-finish-time'])) != $finish_time) {
				array_push($changed_details_array, array("job_time_finished", "", $_POST['job-finish-time'], $finish_time));
			}

			if ($_POST['onsite-washout'] != $details['onsite_washout']) {
				$log_message = $_POST['onsite-washout'] == 1 ? "Additional Washout Fee" : "No Washout Fee";
				array_push($changed_details_array, array("onsite_washout", addslashes($log_message), $_POST['onsite-washout'], $details['onsite_washout']));
			}

			if ($_POST['onsite-disposal'] != $details['onsite_disposal']) {
				$log_message = $_POST['onsite-disposal'] == 1 ? "Additional Disposal Fee" : "No Disposal Fee";
				array_push($changed_details_array, array("onsite_disposal", addslashes($log_message), $_POST['onsite-disposal'], $details['onsite_disposal']));
			}

			if ($_POST['operator-job-notes'] != $details['operator_notes']) {
				array_push($changed_details_array, array("operator_notes", "", $_POST['operator-job-notes'], $details['operator_notes']));
			}
		}

		// Invoice number does not get submitted till it exists, so check it is set first before logging a change
		if (isset($_POST['invoice-number'])) {
			if ($_POST['invoice-number'] != $details['invoice_number']) {
				array_push($changed_details_array, array("invoice_number", "", $_POST['invoice-number'], $details['invoice_number']));
			}
		}

		// Log the changes in the database
		foreach ($changed_details_array as $change) {
			$database->logJobChange($job_id, $change[0], $change[1], $_POST['reason-to-edit'], $change[3], $change[2], $details['status']);
		}
	}

	/* Copy a job */
	function procCopyJob()
	{
		global $database;
		$job_id = $_POST['job-id'];

		$new_job_id = $database->copyJob($job_id);
		$database->addJobQuoteLinkTableEntry(TBL_JOBS, $new_job_id);
		header("Location: job?id=" . $new_job_id);
	}

	/* Undo a completed job */
	function procUndoCompletedJob()
	{
		global $database;
		$job_id = $_POST['job-id'];
		$database->undoCompletedJob($job_id);

		// Notification
		$unified_id = $database->getJobDetails($job_id)['unified_id'];
		$database->insertNewJobNotification(
			admin_user_levels,
			'job',
			$job_id,
			'fa fa-fw fa-info-circle text-warning',
			"Job JX-" . str_pad($unified_id, 6, "0", STR_PAD_LEFT) . " was uncompleted"
		);

		header("Location: job?id=" . $job_id);
	}

	/* Reinstate a cancelled job */
	function procReinstateJob()
	{
		global $database;
		$job_id = $_POST['subreinstate'];

		$database->reinstateCancelledJob($job_id);
		$database->logJobChange($job_id, 'status', 'Job reinstated', '', '', '', 2);
		header("Location: job?id=" . $job_id);
	}

	/* Update a site inspection */
	function procUpdateSiteInspection()
	{
		global $database;
		$site_inspection_id = $_POST['site-inspection-id'];
		$site_inspection_notes = addslashes($_POST['site-visit-notes']);
		$pump_numbers = json_encode($_POST['truck-select']);
		$linesmenIds = $_POST['linesman-select'] ?? null;
		$job_id = $_POST['job-id'];

		$job_details = $database->getJobDetails($job_id);

		$database->updateSiteInspectionDetails($job_id, $site_inspection_id, $pump_numbers, $site_inspection_notes, $linesmenIds);

		// Append site inspection notes to the job's instructions
		$database->appendSiteInspectionNotesToJobInstruction($job_id, $job_details['job_instructions'], $site_inspection_notes);

		// Notification		
		$unified_id = $database->getJobDetails($job_id)['unified_id'];
		$database->insertNewJobNotification(
			admin_user_levels,
			'job',
			$_POST['job-id'],
			'fa fa-fw fa-pen text-xsmooth',
			"Job JX-" . str_pad($unified_id, 6, "0", STR_PAD_LEFT) . " site inspection was updated"
		);

		header("Location: site_inspection?id=" . $site_inspection_id);
	}

	/*
			2024-09-11
			Switch job color
		*/
	function procSwitchJobColor()
	{
		global $database;
		$jobID = $_POST['job-id'];
		$isSiteInspection = isset($_POST['isSiteInspection']) ? $_POST['isSiteInspection'] : false;
		$siteInspectionId = isset($_POST['siteInspectionId']) ? $_POST['siteInspectionId'] : null;

		$result = $database->switchJobColor($jobID);

		if ($isSiteInspection && $siteInspectionId) {
			// echo json_encode(['redirectUrl' => "site_inspection?id=" . $siteInspectionId]);
			echo json_encode(['statusColor' => $result]);
		} else {
			header("Location: job?id=" . $jobID);
		}

		exit();
	}
};

/* Initialize process */
$process = new Process;
