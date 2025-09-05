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
  define('MACEWAN_NEOS_PREFIX', '1230'); # This values is sent as "id" and matches what EPL has configured for your institution prefix.
  define('ID_LENGTH', 10); # Zero pads the obfuscated person id to this many characters.  Does not include the prefix.
                           #   Both prefix and id together should add up to 13 or 14 digits, as per EPL requirements.
  define('OBFUSCATION_A', 1234567891);  # Integer used to uniquely obfuscate ID numbers.  Must be coprime with 10
  define('OBFUSCATION_B', 9876543219);  # Another integer used to uniquely obfuscate ID numbers.
  define('OBFUSCATION_M', 10000000000); # Determines the length of the obfuscated value (10^x for an "x" digit number)

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

  # SAML Settings
  #   "composer update" has a nasty habit of deleting SAML settings stored in the "vendor" directory
  #   As such, we'll be including the settings here and instaciating the class with this array.
  define('SAML_SETTINGS', array(
    // If 'strict' is True, then the PHP Toolkit will reject unsigned
    // or unencrypted messages if it expects them signed or encrypted
    // Also will reject the messages if not strictly follow the SAML
    // standard: Destination, NameId, Conditions ... are validated too.
    'strict' => true,

    // Enable debug mode (to print errors)
    'debug' => false,

    // Set a BaseURL to be used instead of try to guess
    // the BaseURL of the view that process the SAML Message.
    // Ex. http://sp.example.com/
    //     http://example.com/sp/
    'baseurl' => null,

    // Service Provider Data that we are deploying
    'sp' => array(
        // Identifier of the SP entity  (must be a URI)
        'entityId' => '',
        // Specifies info about where and how the <AuthnResponse> message MUST be
        // returned to the requester, in this case our SP.
        'assertionConsumerService' => array(
            // URL Location where the <Response> from the IdP will be returned
            'url' => '',
            // SAML protocol binding to be used when returning the <Response>
            // message.  Onelogin Toolkit supports for this endpoint the
            // HTTP-POST binding only
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
        ),
        // If you need to specify requested attributes, set a
        // attributeConsumingService. nameFormat, attributeValue and
        // friendlyName can be omitted. Otherwise remove this section.
        "attributeConsumingService"=> array(
                "serviceName" => "SP test",
                "serviceDescription" => "Test Service",
                "requestedAttributes" => array(
                    array(
                        "name" => "",
                        "isRequired" => false,
                        "nameFormat" => "",
                        "friendlyName" => "",
                        "attributeValue" => ""
                    )
                )
        ),
        // Specifies info about where and how the <Logout Response> message MUST be
        // returned to the requester, in this case our SP.
        'singleLogoutService' => array(
            // URL Location where the <Response> from the IdP will be returned
            'url' => '',
            // SAML protocol binding to be used when returning the <Response>
            // message.  Onelogin Toolkit supports for this endpoint the
            // HTTP-Redirect binding only
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ),
        // Specifies constraints on the name identifier to be used to
        // represent the requested subject.
        // Take a look on lib/Saml2/Constants.php to see the NameIdFormat supported
        'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',

        // Usually x509cert and privateKey of the SP are provided by files placed at
        // the certs folder. But we can also provide them with the following parameters
        'x509cert' => '/path/to/SAML/cert.pem',
        'privateKey' => '/path/to/SAML/key.pem',

        /*
         * Key rollover
         * If you plan to update the SP x509cert and privateKey
         * you can define here the new x509cert and it will be 
         * published on the SP metadata so Identity Providers can
         * read them and get ready for rollover.
         */
        // 'x509certNew' => '',
    ),

    // Identity Provider Data that we want connect with our SP
    'idp' => array(
        // Identifier of the IdP entity  (must be a URI)
        'entityId' => '',
        // SSO endpoint info of the IdP. (Authentication Request protocol)
        'singleSignOnService' => array(
            // URL Target of the IdP where the SP will send the Authentication Request Message
            'url' => '',
            // SAML protocol binding to be used when returning the <Response>
            // message.  Onelogin Toolkit supports for this endpoint the
            // HTTP-Redirect binding only
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ),
        // SLO endpoint info of the IdP.
        'singleLogoutService' => array(
            // URL Location of the IdP where the SP will send the SLO Request
            'url' => '',
            // URL location of the IdP where the SP SLO Response will be sent (ResponseLocation)
            // if not set, url for the SLO Request will be used
            'responseUrl' => '',
            // SAML protocol binding to be used when returning the <Response>
            // message.  Onelogin Toolkit supports for this endpoint the
            // HTTP-Redirect binding only
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ),
        // Public x509 certificate of the IdP
        'x509cert' => '',
        /*
         *  Instead of use the whole x509cert you can use a fingerprint in
         *  order to validate the SAMLResponse, but we don't recommend to use
         *  that method on production since is exploitable by a collision
         *  attack.
         *  (openssl x509 -noout -fingerprint -in "idp.crt" to generate it,
         *   or add for example the -sha256 , -sha384 or -sha512 parameter)
         *
         *  If a fingerprint is provided, then the certFingerprintAlgorithm is required in order to
         *  let the toolkit know which Algorithm was used. Possible values: sha1, sha256, sha384 or sha512
         *  'sha1' is the default value.
         */
        // 'certFingerprint' => '',
        // 'certFingerprintAlgorithm' => 'sha1',

        /* In some scenarios the IdP uses different certificates for
         * signing/encryption, or is under key rollover phase and more 
         * than one certificate is published on IdP metadata.
         * In order to handle that the toolkit offers that parameter.
         * (when used, 'x509cert' and 'certFingerprint' values are
         * ignored).
         */
        // 'x509certMulti' => array(
        //      'signing' => array(
        //          0 => '<cert1-string>',
        //      ),
        //      'encryption' => array(
        //          0 => '<cert2-string>',
        //      )
        // ),
    ),
  ));

?>
