<?php
  ##########################################################################################################
  #  L-Pass Redirect Script - Configuration
  #    MacEwan University - June 26, 2025
  #    Michael Schouten
  ##########################################################################################################

  # Allowed Roles
  #   Since anyone with a valid login can authenticate via SAML here, we check roles after the fact and 
  # redirect users without an allowed role to a sorry page
  define('ALLOWED_ROLES', array('EMPLOYEE', 'STUDENT', 'INSTRUCTOR', 'FACULTY'));
      
  # Sorry Pages
  #   In order to keep this script lean, it has no front end.  When errors occur or users are denied due to
  # role, redirect to the main CMS for information
  define('ROLES_SORRY_PAGE', 'https://library.domain.com/sorry/roles');
  define('ERROR_SORRY_PAGE', 'https://library.domain.com/sorry/error');
              
  # Expiry Date
  #   When the L-Pass account will expire based on institutional standards.  Includes a way to prevent one-
  # day L-Pass accounts with a configurable leeway.
  define('EXPIRY_DATE', '08-30'); # MM-DD
  define('EXPIRY_LEEWAY', '+14 days'); # Datetime difference to apply to "now" so people don't register for an L-Pass that expires in a day
                               
  # MacEwan ID
  #   Configuration concerning formatting the "id" value for L-Pass.  This includes a prefix value and a total length for zero-padding, resulting
  # in a number like '3201234567' for MacEwan
  define('MACEWAN_NEOS_PREFIX', '32');
  define('ID_LENGTH', 8); # Zero pads the person id to this many characters.  Does not include the prefix.

  # EPL API Config
  #   Configuration for connecting and authenticating to the EPL L-Pass API and Form
  define('EPL_API_BASE_URL', 'https://lpass.domain.com/api');
  define('EPL_LPASS_FORM_URL', 'https://lpass.domain.com/lpass-form');
        
  define('EPL_API_AUTH_USERNAME', 'institutional_user');
  define('EPL_API_AUTH_PASSWORD', 'institutional_password');

  # SAML Attributes
  #   A place to manage SAML attribute names for the required data ingested by the L-Pass form
  define('SAML_FIRST_NAME', 'FirstName');
  define('SAML_MIDDLE_NAME', 'MiddleName');
  define('SAML_LAST_NAME', 'LastName');
  define('SAML_USER_ID', 'urn:oid:1.23.456.7.89012345.6.7.8');
  define('SAML_EMAIL', 'urn:oid:9.8.7654.32109876.543.2.1');
  define('SAML_ADDRESS', 'Address');
  define('SAML_BIRTH_DATE', 'DateOfBirth');
  define('SAML_PHONE', 'Phone');

  define('SAML_ROLES', 'Roles');

?>
