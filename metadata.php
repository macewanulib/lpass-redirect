<?php

  require_once '../vendor/autoload.php';
  require_once __DIR__ . '/config.php';

  use OneLogin\Saml2\Auth;

try {
    $auth = new Auth(SAML_SETTINGS);
    $settings = $auth->getSettings();
    $metadata = $settings->getSPMetadata();
    $errors = $settings->validateMetadata($metadata);
    if (empty($errors)) {
        header('Content-Type: text/xml');
        echo $metadata;
    } else {
           echo 'Invalid SP metadata: '.implode(', ', $errors);
    }
} catch (Exception $e) {
    echo $e->getMessage();
}


?>
