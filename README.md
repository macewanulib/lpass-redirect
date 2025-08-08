# L-Pass Redirect (MacEwan University)

A simple script that accepts an insitutional SAML login, processes the attributes and initiates the required calls to the Edmonton Public Library (EPL) systems to prepare and populate the L-Pass form.  This form allows verified members of partner institutions to register for EPL accounts for free regardless of where they call home.

## Requirements

Requires the backing of a SAML compliant authentication system that has access to the required range of user data that EPL's L-Pass form is expecting.  Also requires OneLogin's PHP SAML2 library to be installed via composer ("onelogin/php-saml"), properly configured for the aforementioned authenitcation system and accessible by the script.

The "symfony/intl" library was also installed via composer to facilitate transforming country codes into full country names per EPL's preference.  If your source data for home address already has full country names, this library is not needed.

## Disclaimer

Note that this repo is provided as an example only.  It contains MacEwan-specific functions and data processing that would have to be rewritten, removed or added to for other institutions.
