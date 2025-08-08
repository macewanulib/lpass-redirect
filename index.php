<?php
  ##########################################################################################################
  #  L-Pass Redirect Script
  #    MacEwan University - June 26, 2025
  #    Michael Schouten
  #
  #  Function: This script will take a SAML request and use it to authorize and prepopulate the Edmonton
  #            Public Library (EPL) L-Pass form, allowing MacEwan students, faculty and staff to register
  #            for a year of EPL for free.  This is particularly useful for distance students and those in
  #            nearby towns as usually only Edmonton residents can access EPL's collection and online
  #            resources (eg: LinkedIn Learning videos) for free.
  #
  #            If the user is accepted by role, the rest of the session arguments are processed and a call
  #            made to EPL's L-Pass pre-processing service, prepping the data on the EPL side.  If that's
  #            accepted, the resulting token is used to forward the user to the L-Pass form itself for
  #            final validation, edits of editable fields (like email), and submission, after which an 
  #            EPL account is created.
  #
  #            Requires composer and the OneLogin Saml2 package installed.  SAML configuration is managed
  #            there.  Other form configuration is found in ./config.php
  #
  ##########################################################################################################

require_once __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/config.php';

use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Error;
use Symfony\Component\Intl\Countries;

# Load settings
$auth = new Auth(SAML_SETTINGS);

# Start session for SAML processing
session_start();

try {
    # Check if this request has a valid SAML Response
    if (isset($_POST['SAMLResponse'])) {
        $auth->processResponse();

        # Check for errors after processing
        if ($auth->getErrors()) {
            error_log('SAML error in L-Pass request form: ' . $auth->getLastErrorReason());
            header('Location: ' . ERROR_SORRY_PAGE);
            exit;
        }

        # Check if user is authenticated
        if ($auth->isAuthenticated()) {
            $data = $auth->getAttributes();

            #Check Roles
            $authorized = false;
            foreach($data[SAML_ROLES] as $role) {
                if(in_array($role, ALLOWED_ROLES)) {
                    $authorized = true;
                }
            }

            if($authorized) {
                # User Authorized... convert data into submission format
                $submission_data = array('firstname' => $data[SAML_FIRST_NAME][0],
                                         'lastname' => $data[SAML_LAST_NAME][0],
                                         'middlename' => $data[SAML_MIDDLE_NAME][0],
                                         'email' => $data[SAML_EMAIL][0],
                                         'dateofbirth' => $data[SAML_BIRTH_DATE][0],
                                         'profile' => 'MacEwan University',
                                         'studentid' => $data[SAML_USER_ID][0],
                                         'id' => sprintf('%s%0' . ID_LENGTH . 'd', MACEWAN_NEOS_PREFIX, $data[SAML_USER_ID][0])
                                        );

                # Phone
                $ph = preg_replace('/[^0-9]/', '', $data[SAML_PHONE][0]);
                if(strlen($ph) == 4) {
                    # Four digit phone number? Assume work phone.  This is specific to MacEwan.
                    $ph = '780497' . $ph;
                }
                $submission_data['phone'] = $ph;

                # Address.  Original data at MacEwan has all address fields combined into one semicolon-separated value.
                $bits = explode(';', $data[SAML_ADDRESS][0]);
                $a_part = array();
                for($x = 0; $x < 4; $x++) {
                    if(trim($bits[$x]) != '') {
                        $a_part[] = trim($bits[$x]);
                    }
                }
                $submission_data['address'] = implode(';', $a_part);
                $submission_data['city'] = ucwords(strtolower($bits[4]));
                $submission_data['province'] = $bits[5];
                $submission_data['postalcode'] = $bits[6];
                $submission_data['country'] = $bits[7];

                # Convert Country if Three Character Code
                if(preg_match('/^[A-Z]{3}$/', $submission_data['country'])) {
                  $a3_to_a2 = array_flip(Countries::getAlpha3Codes());
                  if(isset($a3_to_a2[$submission_data['country']])) {
                    $a2 = $a3_to_a2[$submission_data['country']];
                    $submission_data['country'] = Countries::getName($a2, 'en');
                  }
                }

                # Expiry Date
                $expiry = DateTime::createFromFormat('Y-m-d', date('Y') . '-' . EXPIRY_DATE);
                $now = new DateTime('@' . strtotime(EXPIRY_LEEWAY));

                if($expiry < $now) {
                    $expiry->modify('+1 year');
                }
                $submission_data['expirydate'] = $expiry->format('Y-m-d');
                #Now we need a different authorization token for submitting user information
                $ath_postbody = array('username' => EPL_API_AUTH_USERNAME,
                                      'password' => EPL_API_AUTH_PASSWORD);
                $ath_headers = array('Content-Type: application/json',
                                     'Accept: application/json');
                $ath = curl_init();
                curl_setopt($ath, CURLOPT_URL, EPL_API_BASE_URL . '/auth-login');
                curl_setopt($ath, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ath, CURLOPT_POST, true);
                curl_setopt($ath, CURLOPT_HTTPHEADER, $ath_headers);
                curl_setopt($ath, CURLOPT_POSTFIELDS, json_encode($ath_postbody));
                $ath_response = curl_exec($ath);
                curl_close($ath);

                $authentication = json_decode($ath_response);
                if($authentication !== FALSE && isset($authentication->token) && $authentication->token != '') {

                    # Finally ready to submit student data
                    $stu_headers = array('Content-Type: application/json',
                                         'Accept: application/json',
                                         'Authorization: Bearer ' . $authentication->token);
                    $stu = curl_init();
                    curl_setopt($stu, CURLOPT_URL, EPL_API_BASE_URL . '/lpass-student-record');
                    curl_setopt($stu, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($stu, CURLOPT_POST, true);
                    curl_setopt($stu, CURLOPT_HTTPHEADER, $stu_headers);
                    curl_setopt($stu, CURLOPT_POSTFIELDS, json_encode($submission_data));
                    $stu_response = curl_exec($stu);
                    curl_close($stu);

                    $form_id = json_decode($stu_response);
                    if($form_id !== FALSE && isset($form_id->id) && $form_id->id != '') {

                        # Direct user to the L-Pass Form
                        header('Location: ' . EPL_LPASS_FORM_URL . '?id=' . $form_id->id);

                    } else {
                        # There was an error submitting person data to EPL and/or getting a form ID back
                        error_log('L-Pass Redirect: Error submitting user details to epl: ' . $stu_response);
                        header('Location: ' . ERROR_SORRY_PAGE);
                        exit;
                    }
                } else {
                    # There was an error getting authentication token for submitting user data
                    error_log('L-Pass Redirect: Error fetching auth token for /lpass-student-record: ' . $ath_response);
                    header('Location: ' . ERROR_SORRY_PAGE);
                    exit;
                }

            } else {
                # User authenticated, but is not authorized for L-Pass
                header('Location: ' . ROLES_SORRY_PAGE);
                exit;
            }
        } else {
            error_log('SAML authentication problem in L-Pass request form...');
            header('Location: ' . ERROR_SORRY_PAGE);
            exit;
        }
    } else {
        # No SAML response, initiate SSO login
        $auth->login(); # Automatically redirects to IdP
    }
} catch (Exception $e) {
    error_log('SAML exception in L-Pass request form: ' . $e->getMessage());
    header('Location: ' . ERROR_SORRY_PAGE);
}
