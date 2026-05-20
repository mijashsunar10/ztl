<?php

$ztlEnv = getenv('ZTL_ENVIRONMENT') ?: 'sandbox';
$ztlProd = $ztlEnv === 'production';

return [
    // Application URLs
    'landingUrl' => getenv('ZTL_LANDING_URL') ?: 'https://webdev.splitgr.id/index.php?page=ztl_landing',
    'settleUrl' => getenv('ZTL_SETTLE_URL') ?: 'https://webdev.splitgr.id/index.php?page=ztl_settle',
    'paymentsApiUrl' => getenv('ZTL_PAYMENTS_API_URL') ?: 'https://webdev.splitgr.id/api/adminv2/getoutstandingmanualcollections',

    // ZTL API connection
    'environment' => $ztlEnv,
    'clientId' => getenv('ZTL_CLIENT_ID') ?: 'splitgrid',
    'clientSecret' => getenv('ZTL_CLIENT_SECRET') ?: '4cc10a88-8764-4298-b4e7-9f9dbe153ccd',
    'apiBaseUrl' => getenv('ZTL_API_BASE_URL') ?: ($ztlProd ? 'https://api.ztlpay.io' : 'https://api.sandbox.ztlpay-test.io'),
    'authBaseUrl' => getenv('ZTL_AUTH_BASE_URL') ?: ($ztlProd ? 'https://oidc.ztlpay.io' : 'https://oidc.sandbox.ztlpay-test.io'),
    'onboardingBaseUrl' => getenv('ZTL_ONBOARDING_BASE_URL') ?: ($ztlProd ? 'https://welcome.ztlpay.io' : 'https://welcome.sandbox.ztlpay-test.io'),
    'tokenEndpoint' => getenv('ZTL_TOKEN_ENDPOINT') ?: '/connect/token',
    'onboardingEndpoint' => getenv('ZTL_ONBOARDING_ENDPOINT') ?: '/onboarding',
    'consentEndpoint' => getenv('ZTL_CONSENT_ENDPOINT') ?: '/api/v2/consents',
    'companiesEndpoint' => getenv('ZTL_COMPANIES_ENDPOINT') ?: '/api/companies',
    'tokenAudience' => getenv('ZTL_TOKEN_AUDIENCE') ?: '',
    'tokenScope' => getenv('ZTL_TOKEN_SCOPE') ?: 'payments',
    'verifySsl' => filter_var(getenv('ZTL_VERIFY_SSL') ?: false, FILTER_VALIDATE_BOOLEAN),
    'timeout' => getenv('ZTL_TIMEOUT') ?: 45,
    'storagePath' => __DIR__ . DIRECTORY_SEPARATOR . 'ztl_settle_agreements.json',

    'formDefaults' => [
        'country' => getenv('ZTL_DEFAULT_COUNTRY') ?: '',
        'organizationNumber' => getenv('ZTL_DEFAULT_ORGANIZATION_NUMBER') ?: '',
        'onboardingRedirectUrl' => getenv('ZTL_ONBOARDING_REDIRECT_URL') ?: getenv('ZTL_SETTLE_URL') ?: 'https://webdev.splitgr.id/index.php?page=ztl_settle',
        'userId' => getenv('ZTL_DEFAULT_USER_ID') ?: 'TX99999',
        'bic' => getenv('ZTL_DEFAULT_BIC') ?: 'DNBANOKK',
        'bankBranch' => getenv('ZTL_DEFAULT_BANK_BRANCH') ?: '',
        'consentCallbackUrl' => getenv('ZTL_SCA_CALLBACK_URL') ?: getenv('ZTL_SETTLE_URL') ?: 'https://webdev.splitgr.id/index.php?page=ztl_settle',
        'preferredScaMethod' => getenv('ZTL_DEFAULT_SCA_METHOD') ?: 'Redirect',
    ],

    'paymentDefaults' => [
        'callbackUrl' => getenv('ZTL_PAYMENT_CALLBACK_URL') ?: getenv('ZTL_SCA_CALLBACK_URL') ?: getenv('ZTL_SETTLE_URL') ?: 'https://webdev.splitgr.id/index.php?page=ztl_settle',
        'preferredScaMethod' => getenv('ZTL_PAYMENT_SCA_METHOD') ?: 'Redirect',
        'fromAccountId' => getenv('ZTL_DEFAULT_PAYMENT_FROM_ACCOUNT_ID') ?: '',
        'toName' => getenv('ZTL_DEFAULT_PAYMENT_TO_NAME') ?: 'ZTL Payment Solution AS',
        'toBban' => getenv('ZTL_DEFAULT_PAYMENT_TO_BBAN') ?: '97105048304',
        'toIban' => getenv('ZTL_DEFAULT_PAYMENT_TO_IBAN') ?: '',
        'toBic' => getenv('ZTL_DEFAULT_PAYMENT_TO_BIC') ?: 'DNBANOKK',
        'toCountry' => getenv('ZTL_DEFAULT_PAYMENT_TO_COUNTRY') ?: 'NO',
        'toClearingCode' => getenv('ZTL_DEFAULT_PAYMENT_TO_CLEARING_CODE') ?: '',
        'currency' => getenv('ZTL_DEFAULT_PAYMENT_CURRENCY') ?: 'NOK',
        'amount' => getenv('ZTL_DEFAULT_PAYMENT_AMOUNT') ?: '100.00',
        'dueDate' => getenv('ZTL_PAYMENT_DUE_DATE') ?: date('Y-m-d', strtotime('+1 day')),
        'remittance' => getenv('ZTL_DEFAULT_PAYMENT_REMITTANCE') ?: 'Test payment',
        'purposeCode' => getenv('ZTL_DEFAULT_PAYMENT_PURPOSE_CODE') ?: 'SUPP',
        'endToEndId' => getenv('ZTL_DEFAULT_PAYMENT_END_TO_END_ID') ?: '',
        'fromAccountBic' => getenv('ZTL_DEFAULT_PAYMENT_FROM_ACCOUNT_BIC') ?: 'DNBANOKK',
        'fromAddressStreetName' => getenv('ZTL_DEFAULT_PAYMENT_FROM_ADDRESS_STREET') ?: 'Kristian IVs gate',
        'fromAddressBuildingNumber' => getenv('ZTL_DEFAULT_PAYMENT_FROM_ADDRESS_BUILDING') ?: '15',
        'fromAddressCity' => getenv('ZTL_DEFAULT_PAYMENT_FROM_ADDRESS_CITY') ?: 'Oslo',
        'fromAddressPostCode' => getenv('ZTL_DEFAULT_PAYMENT_FROM_ADDRESS_POSTCODE') ?: '0164',
        'fromAddressCountry' => getenv('ZTL_DEFAULT_PAYMENT_FROM_ADDRESS_COUNTRY') ?: 'NO',
        'fromTelephoneNumber' => getenv('ZTL_DEFAULT_PAYMENT_FROM_TELEPHONE') ?: '+4740000858',
        'toAddressStreetName' => getenv('ZTL_DEFAULT_PAYMENT_TO_ADDRESS_STREET') ?: 'Kristian IVs gate',
        'toAddressBuildingNumber' => getenv('ZTL_DEFAULT_PAYMENT_TO_ADDRESS_BUILDING') ?: '15',
        'toAddressCity' => getenv('ZTL_DEFAULT_PAYMENT_TO_ADDRESS_CITY') ?: 'Oslo',
        'toAddressPostCode' => getenv('ZTL_DEFAULT_PAYMENT_TO_ADDRESS_POSTCODE') ?: '0164',
        'toAddressCountry' => getenv('ZTL_DEFAULT_PAYMENT_TO_ADDRESS_COUNTRY') ?: 'NO',
        'toTelephoneNumber' => getenv('ZTL_DEFAULT_PAYMENT_TO_TELEPHONE') ?: '+4740000858',
        'additionalInformationType' => getenv('ZTL_DEFAULT_PAYMENT_ADDITIONAL_INFO_TYPE') ?: '',
        'additionalInformationValue' => getenv('ZTL_DEFAULT_PAYMENT_ADDITIONAL_INFO_VALUE') ?: '',
        'regulatoryReportingCode' => getenv('ZTL_DEFAULT_PAYMENT_REG_REPORTING_CODE') ?: '',
        'regulatoryReportingInformation' => getenv('ZTL_DEFAULT_PAYMENT_REG_REPORTING_INFO') ?: '',
    ],
];
