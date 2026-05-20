<?php

if (!class_exists('ZtlApiException', false)) {
    class ZtlApiException extends Exception
    {
        private $ztlRequestId;
        private $statusCode;
        private $responseBody;

        public function __construct($message, $statusCode = 0, $ztlRequestId = null, $responseBody = null)
        {
            parent::__construct($message, (int) $statusCode);
            $this->statusCode = (int) $statusCode;
            $this->ztlRequestId = $ztlRequestId;
            $this->responseBody = $responseBody;
        }

        public function getZtlRequestId()
        {
            return $this->ztlRequestId;
        }

        public function getStatusCode()
        {
            return $this->statusCode;
        }

        public function getResponseBody()
        {
            return $this->responseBody;
        }
    }
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'ztl_userConsent.class.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'ztl_accounts.class.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'ztl_payments.class.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'ztl_auth_onboard.class.php';

class ZtlSettle
{
    use ZtlAuthOnboardModuleTrait;
    use ZtlUserConsentModuleTrait;
    use ZtlAccountsModuleTrait;
    use ZtlPaymentsModuleTrait;

    private $db;
    private $apiInfoId;

    private $environment = 'sandbox';
    private $clientId = '';
    private $clientSecret = '';
    private $apiBaseUrl = '';
    private $authBaseUrl = '';
    private $onboardingBaseUrl = '';
    private $tokenEndpoint = '/connect/token';
    private $onboardingEndpoint = '/onboarding';
    private $consentEndpoint = '/api/v2/consents';
    private $accountsEndpoint = '/api/v2/accounts';
    private $paymentsEndpoint = '/api/v2/payments';
    private $companiesEndpoint = '/api/companies';
    private $tokenAudience = '';
    private $tokenScope = 'payments';
    private $verifySsl = true;
    private $timeout = 45;
    private $accessToken = '';
    private $tokenExpiresAt = 0;
    private $lastAuthErrors = [];

    private $country = '';
    private $organizationNumber = '';
    private $onboardingRedirectUrl = '';
    private $userId = '';
    private $bic = '';
    private $bankBranch = '';
    private $consentCallbackUrl = '';
    private $preferredScaMethod = 'Redirect';
    private $psu = [];
    private $consentId = '';
    private $accounts = [];
    private $paymentCallbackUrl = '';
    private $paymentPreferredScaMethod = 'Redirect';
    private $paymentFromAccountId = '';
    private $paymentToName = '';
    private $paymentToBban = '';
    private $paymentToIban = '';
    private $paymentToBic = '';
    private $paymentToCountry = '';
    private $paymentToClearingCode = '';
    private $paymentCurrency = 'NOK';
    private $paymentAmount = '';
    private $paymentDueDate = '';
    private $paymentRemittance = '';
    private $paymentPurposeCode = 'OTHR';
    private $paymentEndToEndId = '';
    private $paymentFromAccountBic = '';
    private $paymentFromAddressStreetName = '';
    private $paymentFromAddressBuildingNumber = '';
    private $paymentFromAddressCity = '';
    private $paymentFromAddressPostCode = '';
    private $paymentFromAddressCountry = '';
    private $paymentFromTelephoneNumber = '';
    private $paymentToAddressStreetName = '';
    private $paymentToAddressBuildingNumber = '';
    private $paymentToAddressCity = '';
    private $paymentToAddressPostCode = '';
    private $paymentToAddressCountry = '';
    private $paymentToTelephoneNumber = '';
    private $paymentAdditionalInformationType = '';
    private $paymentAdditionalInformationValue = '';
    private $paymentRegulatoryReportingCode = '';
    private $paymentRegulatoryReportingInformation = '';
    private $lastResult = null;
    private $debugInfo = [];
    private $storagePath;

    public function __construct($db = null, $apiInfoId = null, array $config = [])
    {
        $this->db = $db;
        $this->apiInfoId = $apiInfoId;
        $this->environment = $this->readConfigValue($config, 'environment', 'ZTL_ENVIRONMENT', $this->environment);
        $this->clientId = $this->readConfigValue($config, 'clientId', 'ZTL_CLIENT_ID', $this->clientId);
        $this->clientSecret = $this->readConfigValue($config, 'clientSecret', 'ZTL_CLIENT_SECRET', $this->clientSecret);
        $this->apiBaseUrl = $this->readConfigValue($config, 'apiBaseUrl', 'ZTL_API_BASE_URL', '');
        $this->authBaseUrl = $this->readConfigValue($config, 'authBaseUrl', 'ZTL_AUTH_BASE_URL', '');
        $this->onboardingBaseUrl = $this->readConfigValue($config, 'onboardingBaseUrl', 'ZTL_ONBOARDING_BASE_URL', '');
        $this->tokenEndpoint = $this->readConfigValue($config, 'tokenEndpoint', 'ZTL_TOKEN_ENDPOINT', $this->tokenEndpoint);
        $this->onboardingEndpoint = $this->readConfigValue($config, 'onboardingEndpoint', 'ZTL_ONBOARDING_ENDPOINT', $this->onboardingEndpoint);
        $this->consentEndpoint = $this->readConfigValue($config, 'consentEndpoint', 'ZTL_CONSENT_ENDPOINT', $this->consentEndpoint);
        $this->accountsEndpoint = $this->readConfigValue($config, 'accountsEndpoint', 'ZTL_ACCOUNTS_ENDPOINT', $this->accountsEndpoint);
        $this->companiesEndpoint = $this->readConfigValue($config, 'companiesEndpoint', 'ZTL_COMPANIES_ENDPOINT', $this->companiesEndpoint);
        $this->tokenAudience = $this->readConfigValue($config, 'tokenAudience', 'ZTL_TOKEN_AUDIENCE', $this->tokenAudience);
        $this->tokenScope = $this->readConfigValue($config, 'tokenScope', 'ZTL_TOKEN_SCOPE', $this->tokenScope);
        $this->verifySsl = $this->readBoolConfigValue($config, 'verifySsl', 'ZTL_VERIFY_SSL', $this->verifySsl);
        $this->timeout = (int) $this->readConfigValue($config, 'timeout', 'ZTL_TIMEOUT', (string) $this->timeout);
        if ($this->timeout <= 0) {
            $this->timeout = 45;
        }
        $this->storagePath = $config['storagePath'] ?? (__DIR__ . DIRECTORY_SEPARATOR . 'ztl_settle_agreements.json');
        $formDefaults = isset($config['formDefaults']) && is_array($config['formDefaults']) ? $config['formDefaults'] : [];
        $this->country = $this->readConfigValue($formDefaults, 'country', 'ZTL_DEFAULT_COUNTRY', $this->country);
        $this->organizationNumber = $this->readConfigValue($formDefaults, 'organizationNumber', 'ZTL_DEFAULT_ORGANIZATION_NUMBER', $this->organizationNumber);
        $this->onboardingRedirectUrl = $this->readConfigValue($formDefaults, 'onboardingRedirectUrl', 'ZTL_ONBOARDING_REDIRECT_URL', $this->onboardingRedirectUrl);
        $this->userId = $this->readConfigValue($formDefaults, 'userId', 'ZTL_DEFAULT_USER_ID', $this->userId);
        $this->bic = $this->readConfigValue($formDefaults, 'bic', 'ZTL_DEFAULT_BIC', $this->bic);
        $this->bankBranch = $this->readConfigValue($formDefaults, 'bankBranch', 'ZTL_DEFAULT_BANK_BRANCH', $this->bankBranch);
        $this->consentCallbackUrl = $this->readConfigValue($formDefaults, 'consentCallbackUrl', 'ZTL_SCA_CALLBACK_URL', $this->consentCallbackUrl);
        $this->preferredScaMethod = $this->readConfigValue($formDefaults, 'preferredScaMethod', 'ZTL_DEFAULT_SCA_METHOD', $this->preferredScaMethod);

        $paymentDefaults = isset($config['paymentDefaults']) && is_array($config['paymentDefaults']) ? $config['paymentDefaults'] : [];
        $this->paymentCallbackUrl = $this->readConfigValue($paymentDefaults, 'callbackUrl', 'ZTL_PAYMENT_CALLBACK_URL', $this->consentCallbackUrl);
        $this->paymentPreferredScaMethod = $this->readConfigValue($paymentDefaults, 'preferredScaMethod', 'ZTL_PAYMENT_SCA_METHOD', $this->paymentPreferredScaMethod);
        $this->paymentFromAccountId = $this->readConfigValue($paymentDefaults, 'fromAccountId', 'ZTL_DEFAULT_PAYMENT_FROM_ACCOUNT_ID', $this->paymentFromAccountId);
        $this->paymentToName = $this->readConfigValue($paymentDefaults, 'toName', 'ZTL_DEFAULT_PAYMENT_TO_NAME', $this->paymentToName);
        $this->paymentToBban = $this->readConfigValue($paymentDefaults, 'toBban', 'ZTL_DEFAULT_PAYMENT_TO_BBAN', $this->paymentToBban);
        $this->paymentToIban = $this->readConfigValue($paymentDefaults, 'toIban', 'ZTL_DEFAULT_PAYMENT_TO_IBAN', $this->paymentToIban);
        $this->paymentToBic = $this->readConfigValue($paymentDefaults, 'toBic', 'ZTL_DEFAULT_PAYMENT_TO_BIC', $this->paymentToBic);
        $this->paymentToCountry = $this->readConfigValue($paymentDefaults, 'toCountry', 'ZTL_DEFAULT_PAYMENT_TO_COUNTRY', $this->paymentToCountry);
        $this->paymentToClearingCode = $this->readConfigValue($paymentDefaults, 'toClearingCode', 'ZTL_DEFAULT_PAYMENT_TO_CLEARING_CODE', $this->paymentToClearingCode);
        $this->paymentCurrency = $this->readConfigValue($paymentDefaults, 'currency', 'ZTL_DEFAULT_PAYMENT_CURRENCY', $this->paymentCurrency);
        $this->paymentAmount = $this->readConfigValue($paymentDefaults, 'amount', 'ZTL_DEFAULT_PAYMENT_AMOUNT', $this->paymentAmount);
        $this->paymentDueDate = $this->readConfigValue($paymentDefaults, 'dueDate', 'ZTL_PAYMENT_DUE_DATE', date('Y-m-d'));
        $this->paymentRemittance = $this->readConfigValue($paymentDefaults, 'remittance', 'ZTL_DEFAULT_PAYMENT_REMITTANCE', $this->paymentRemittance);
        $this->paymentPurposeCode = $this->readConfigValue($paymentDefaults, 'purposeCode', 'ZTL_DEFAULT_PAYMENT_PURPOSE_CODE', $this->paymentPurposeCode);
        $this->paymentEndToEndId = $this->readConfigValue($paymentDefaults, 'endToEndId', 'ZTL_DEFAULT_PAYMENT_END_TO_END_ID', $this->paymentEndToEndId);
        $this->paymentFromAccountBic = $this->readConfigValue($paymentDefaults, 'fromAccountBic', 'ZTL_DEFAULT_PAYMENT_FROM_ACCOUNT_BIC', $this->paymentFromAccountBic);
        $this->paymentFromAddressStreetName = $this->readConfigValue($paymentDefaults, 'fromAddressStreetName', 'ZTL_DEFAULT_PAYMENT_FROM_ADDRESS_STREET', $this->paymentFromAddressStreetName);
        $this->paymentFromAddressBuildingNumber = $this->readConfigValue($paymentDefaults, 'fromAddressBuildingNumber', 'ZTL_DEFAULT_PAYMENT_FROM_ADDRESS_BUILDING', $this->paymentFromAddressBuildingNumber);
        $this->paymentFromAddressCity = $this->readConfigValue($paymentDefaults, 'fromAddressCity', 'ZTL_DEFAULT_PAYMENT_FROM_ADDRESS_CITY', $this->paymentFromAddressCity);
        $this->paymentFromAddressPostCode = $this->readConfigValue($paymentDefaults, 'fromAddressPostCode', 'ZTL_DEFAULT_PAYMENT_FROM_ADDRESS_POSTCODE', $this->paymentFromAddressPostCode);
        $this->paymentFromAddressCountry = $this->readConfigValue($paymentDefaults, 'fromAddressCountry', 'ZTL_DEFAULT_PAYMENT_FROM_ADDRESS_COUNTRY', $this->paymentFromAddressCountry);
        $this->paymentFromTelephoneNumber = $this->readConfigValue($paymentDefaults, 'fromTelephoneNumber', 'ZTL_DEFAULT_PAYMENT_FROM_TELEPHONE', $this->paymentFromTelephoneNumber);
        $this->paymentToAddressStreetName = $this->readConfigValue($paymentDefaults, 'toAddressStreetName', 'ZTL_DEFAULT_PAYMENT_TO_ADDRESS_STREET', $this->paymentToAddressStreetName);
        $this->paymentToAddressBuildingNumber = $this->readConfigValue($paymentDefaults, 'toAddressBuildingNumber', 'ZTL_DEFAULT_PAYMENT_TO_ADDRESS_BUILDING', $this->paymentToAddressBuildingNumber);
        $this->paymentToAddressCity = $this->readConfigValue($paymentDefaults, 'toAddressCity', 'ZTL_DEFAULT_PAYMENT_TO_ADDRESS_CITY', $this->paymentToAddressCity);
        $this->paymentToAddressPostCode = $this->readConfigValue($paymentDefaults, 'toAddressPostCode', 'ZTL_DEFAULT_PAYMENT_TO_ADDRESS_POSTCODE', $this->paymentToAddressPostCode);
        $this->paymentToAddressCountry = $this->readConfigValue($paymentDefaults, 'toAddressCountry', 'ZTL_DEFAULT_PAYMENT_TO_ADDRESS_COUNTRY', $this->paymentToAddressCountry);
        $this->paymentToTelephoneNumber = $this->readConfigValue($paymentDefaults, 'toTelephoneNumber', 'ZTL_DEFAULT_PAYMENT_TO_TELEPHONE', $this->paymentToTelephoneNumber);
        $this->paymentAdditionalInformationType = $this->readConfigValue($paymentDefaults, 'additionalInformationType', 'ZTL_DEFAULT_PAYMENT_ADDITIONAL_INFO_TYPE', $this->paymentAdditionalInformationType);
        $this->paymentAdditionalInformationValue = $this->readConfigValue($paymentDefaults, 'additionalInformationValue', 'ZTL_DEFAULT_PAYMENT_ADDITIONAL_INFO_VALUE', $this->paymentAdditionalInformationValue);
        $this->paymentRegulatoryReportingCode = $this->readConfigValue($paymentDefaults, 'regulatoryReportingCode', 'ZTL_DEFAULT_PAYMENT_REG_REPORTING_CODE', $this->paymentRegulatoryReportingCode);
        $this->paymentRegulatoryReportingInformation = $this->readConfigValue($paymentDefaults, 'regulatoryReportingInformation', 'ZTL_DEFAULT_PAYMENT_REG_REPORTING_INFO', $this->paymentRegulatoryReportingInformation);
    }

    public static function psuFromServer(array $server)
    {
        return self::normalizePsu([
            'ipAddress' => self::firstIpAddress(self::serverValue($server, 'HTTP_X_FORWARDED_FOR', self::serverValue($server, 'REMOTE_ADDR', ''))),
            'userAgent' => self::serverValue($server, 'HTTP_USER_AGENT', ''),
            'accept' => self::serverValue($server, 'HTTP_ACCEPT', ''),
            'acceptLanguage' => self::serverValue($server, 'HTTP_ACCEPT_LANGUAGE', ''),
        ]);
    }

    public static function normalizePsu(array $psu)
    {
        $ipAddress = trim((string) ($psu['ipAddress'] ?? ''));
        $userAgent = trim((string) ($psu['userAgent'] ?? ''));
        $accept = trim((string) ($psu['accept'] ?? ''));
        $acceptLanguage = trim((string) ($psu['acceptLanguage'] ?? ''));

        $ipAddress = self::firstIpAddress($ipAddress);
        if (!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            $ipAddress = '';
        }

        return [
            'ipAddress' => $ipAddress,
            'userAgent' => $userAgent,
            'accept' => $accept,
            'acceptLanguage' => $acceptLanguage,
        ];
    }

    public function setCountry($country)
    {
        $country = strtoupper(trim((string) $country));
        if ($country !== '') {
            $this->country = $country;
        }
    }

    public function setOrganizationNumber($organizationNumber)
    {
        $organizationNumber = trim((string) $organizationNumber);
        if ($organizationNumber !== '') {
            $this->organizationNumber = $organizationNumber;
        }
    }

    public function setOnboardingRedirectUrl($redirectUrl)
    {
        $this->onboardingRedirectUrl = trim((string) $redirectUrl);
    }

    public function setUserId($userId)
    {
        $userId = trim((string) $userId);
        if ($userId !== '') {
            $this->userId = $userId;
        }
    }

    public function setBic($bic)
    {
        $bic = strtoupper(trim((string) $bic));
        if ($bic !== '') {
            $this->bic = $bic;
        }
    }

    public function setBankBranch($bankBranch)
    {
        $this->bankBranch = trim((string) $bankBranch);
    }

    public function setConsentCallbackUrl($callbackUrl)
    {
        $this->consentCallbackUrl = trim((string) $callbackUrl);
    }

    public function setPreferredScaMethod($preferredScaMethod)
    {
        $preferredScaMethod = trim((string) $preferredScaMethod);
        if (in_array($preferredScaMethod, ['Redirect', 'Qr'], true)) {
            $this->preferredScaMethod = $preferredScaMethod;
        }
    }

    public function setAccounts(?array $accounts = null)
    {
        $this->accounts = is_array($accounts) ? $accounts : [];
    }

    public function setPaymentFormValues(array $values)
    {
        $this->paymentCallbackUrl = trim((string) ($values['paymentCallbackUrl'] ?? $this->paymentCallbackUrl));
        $preferredScaMethod = trim((string) ($values['paymentPreferredScaMethod'] ?? $this->paymentPreferredScaMethod));
        if (in_array($preferredScaMethod, ['Redirect', 'Qr'], true)) {
            $this->paymentPreferredScaMethod = $preferredScaMethod;
        }
        $this->paymentFromAccountId = trim((string) ($values['paymentFromAccountId'] ?? $this->paymentFromAccountId));
        $this->paymentToName = trim((string) ($values['paymentToName'] ?? $this->paymentToName));
        $this->paymentToBban = trim((string) ($values['paymentToBban'] ?? $this->paymentToBban));
        $this->paymentToIban = trim((string) ($values['paymentToIban'] ?? $this->paymentToIban));
        $this->paymentToBic = strtoupper(trim((string) ($values['paymentToBic'] ?? $this->paymentToBic)));
        $this->paymentToCountry = strtoupper(trim((string) ($values['paymentToCountry'] ?? $this->paymentToCountry)));
        $this->paymentToClearingCode = trim((string) ($values['paymentToClearingCode'] ?? $this->paymentToClearingCode));
        $this->paymentCurrency = strtoupper(trim((string) ($values['paymentCurrency'] ?? $this->paymentCurrency)));
        if ($this->paymentCurrency === '') {
            $this->paymentCurrency = 'NOK';
        }
        $this->paymentAmount = trim((string) ($values['paymentAmount'] ?? $this->paymentAmount));
        $this->paymentDueDate = trim((string) ($values['paymentDueDate'] ?? $this->paymentDueDate));
        $this->paymentRemittance = trim((string) ($values['paymentRemittance'] ?? $this->paymentRemittance));
        $this->paymentPurposeCode = strtoupper(trim((string) ($values['paymentPurposeCode'] ?? $this->paymentPurposeCode)));
        if ($this->paymentPurposeCode === '') {
            $this->paymentPurposeCode = 'OTHR';
        }
        $this->paymentEndToEndId = trim((string) ($values['paymentEndToEndId'] ?? $this->paymentEndToEndId));
        $this->paymentFromAccountBic = strtoupper(trim((string) ($values['paymentFromAccountBic'] ?? $this->paymentFromAccountBic)));
        $this->paymentFromAddressStreetName = trim((string) ($values['paymentFromAddressStreetName'] ?? $this->paymentFromAddressStreetName));
        $this->paymentFromAddressBuildingNumber = trim((string) ($values['paymentFromAddressBuildingNumber'] ?? $this->paymentFromAddressBuildingNumber));
        $this->paymentFromAddressCity = trim((string) ($values['paymentFromAddressCity'] ?? $this->paymentFromAddressCity));
        $this->paymentFromAddressPostCode = trim((string) ($values['paymentFromAddressPostCode'] ?? $this->paymentFromAddressPostCode));
        $this->paymentFromAddressCountry = strtoupper(trim((string) ($values['paymentFromAddressCountry'] ?? ''))) ?: $this->paymentFromAddressCountry;
        $this->paymentFromTelephoneNumber = trim((string) ($values['paymentFromTelephoneNumber'] ?? $this->paymentFromTelephoneNumber));
        $this->paymentToAddressStreetName = trim((string) ($values['paymentToAddressStreetName'] ?? '')) ?: $this->paymentToAddressStreetName;
        $this->paymentToAddressBuildingNumber = trim((string) ($values['paymentToAddressBuildingNumber'] ?? '')) ?: $this->paymentToAddressBuildingNumber;
        $this->paymentToAddressCity = trim((string) ($values['paymentToAddressCity'] ?? '')) ?: $this->paymentToAddressCity;
        $this->paymentToAddressPostCode = trim((string) ($values['paymentToAddressPostCode'] ?? '')) ?: $this->paymentToAddressPostCode;
        $this->paymentToAddressCountry = strtoupper(trim((string) ($values['paymentToAddressCountry'] ?? ''))) ?: $this->paymentToAddressCountry;
        $this->paymentToTelephoneNumber = trim((string) ($values['paymentToTelephoneNumber'] ?? $this->paymentToTelephoneNumber));
        $this->paymentAdditionalInformationType = trim((string) ($values['paymentAdditionalInformationType'] ?? $this->paymentAdditionalInformationType));
        $this->paymentAdditionalInformationValue = trim((string) ($values['paymentAdditionalInformationValue'] ?? $this->paymentAdditionalInformationValue));
        $this->paymentRegulatoryReportingCode = trim((string) ($values['paymentRegulatoryReportingCode'] ?? $this->paymentRegulatoryReportingCode));
        $this->paymentRegulatoryReportingInformation = trim((string) ($values['paymentRegulatoryReportingInformation'] ?? $this->paymentRegulatoryReportingInformation));
    }

    public function setPsu(array $psu)
    {
        $this->psu = self::normalizePsu($psu);
    }

    public function setConsentId($consentId)
    {
        $this->consentId = trim((string) $consentId);
    }

    public function setFormValues(array $values)
    {
        $this->setCountry($values['country'] ?? $this->country);
        $this->setOrganizationNumber($values['organizationNumber'] ?? $this->organizationNumber);
        $this->setOnboardingRedirectUrl($values['onboardingRedirectUrl'] ?? $this->onboardingRedirectUrl);
        $this->setUserId($values['userId'] ?? $this->userId);
        $this->setBic($values['bic'] ?? $this->bic);
        $this->setBankBranch($values['bankBranch'] ?? $this->bankBranch);
        $this->setConsentCallbackUrl($values['consentCallbackUrl'] ?? $this->consentCallbackUrl);
        $this->setPreferredScaMethod($values['preferredScaMethod'] ?? $this->preferredScaMethod);
    }

    public function getFormValues()
    {
        return [
            'country' => $this->country,
            'organizationNumber' => $this->organizationNumber,
            'onboardingRedirectUrl' => $this->onboardingRedirectUrl,
            'userId' => $this->userId,
            'bic' => $this->bic,
            'bankBranch' => $this->bankBranch,
            'consentCallbackUrl' => $this->consentCallbackUrl,
            'preferredScaMethod' => $this->preferredScaMethod,
            'paymentCallbackUrl' => $this->paymentCallbackUrl,
            'paymentPreferredScaMethod' => $this->paymentPreferredScaMethod,
            'paymentFromAccountId' => $this->paymentFromAccountId,
            'paymentToName' => $this->paymentToName,
            'paymentToBban' => $this->paymentToBban,
            'paymentToIban' => $this->paymentToIban,
            'paymentToBic' => $this->paymentToBic,
            'paymentToCountry' => $this->paymentToCountry,
            'paymentToClearingCode' => $this->paymentToClearingCode,
            'paymentCurrency' => $this->paymentCurrency,
            'paymentAmount' => $this->paymentAmount,
            'paymentDueDate' => $this->paymentDueDate,
            'paymentRemittance' => $this->paymentRemittance,
            'paymentPurposeCode' => $this->paymentPurposeCode,
            'paymentEndToEndId' => $this->paymentEndToEndId,
            'paymentFromAccountBic' => $this->paymentFromAccountBic,
            'paymentFromAddressStreetName' => $this->paymentFromAddressStreetName,
            'paymentFromAddressBuildingNumber' => $this->paymentFromAddressBuildingNumber,
            'paymentFromAddressCity' => $this->paymentFromAddressCity,
            'paymentFromAddressPostCode' => $this->paymentFromAddressPostCode,
            'paymentFromAddressCountry' => $this->paymentFromAddressCountry,
            'paymentFromTelephoneNumber' => $this->paymentFromTelephoneNumber,
            'paymentToAddressStreetName' => $this->paymentToAddressStreetName,
            'paymentToAddressBuildingNumber' => $this->paymentToAddressBuildingNumber,
            'paymentToAddressCity' => $this->paymentToAddressCity,
            'paymentToAddressPostCode' => $this->paymentToAddressPostCode,
            'paymentToAddressCountry' => $this->paymentToAddressCountry,
            'paymentToTelephoneNumber' => $this->paymentToTelephoneNumber,
            'paymentAdditionalInformationType' => $this->paymentAdditionalInformationType,
            'paymentAdditionalInformationValue' => $this->paymentAdditionalInformationValue,
            'paymentRegulatoryReportingCode' => $this->paymentRegulatoryReportingCode,
            'paymentRegulatoryReportingInformation' => $this->paymentRegulatoryReportingInformation,
        ];
    }

    public function getSummary()
    {
        $records = $this->all();
        $onboardingCount = 0;
        $consentCount = 0;
        $paymentCount = 0;
        $bulkPaymentCount = 0;
        $lastPaymentStatus = null;
        $lastConsentStatus = null;
        $lastBulkPaymentStatus = null;

        foreach ($records as $record) {
            if (in_array(($record['type'] ?? ''), ['onboarding', 'onboarding_registration', 'onboarding_cancellation'], true)) {
                $onboardingCount++;
            }
            if (($record['type'] ?? '') === 'consent') {
                $consentCount++;
                $lastConsentStatus = $record['status'] ?? null;
            }
            if (($record['type'] ?? '') === 'payment') {
                $paymentCount++;
                $lastPaymentStatus = $record['status'] ?? null;
            }
            if (($record['type'] ?? '') === 'bulk_payment') {
                $bulkPaymentCount++;
                $lastBulkPaymentStatus = $record['bulkStatus'] ?? null;
            }
        }

        return [
            'onboardings' => $onboardingCount,
            'consents' => $consentCount,
            'payments' => $paymentCount,
            'bulkPayments' => $bulkPaymentCount,
            'lastConsentStatus' => $lastConsentStatus,
            'lastPaymentStatus' => $lastPaymentStatus,
            'lastBulkPaymentStatus' => $lastBulkPaymentStatus,
        ];
    }

    public function getDebugInfo()
    {
        return $this->debugInfo;
    }

    public function getLastResult()
    {
        return $this->lastResult;
    }

    public function getLastAuthErrors()
    {
        return $this->lastAuthErrors;
    }

    private function sendStartOnboardingRequest($country, $organizationNumber, $redirectUrl = '')
    {
        $payload = [
            'country' => strtoupper(trim((string) $country)),
            'organizationNumber' => trim((string) $organizationNumber),
        ];

        if (trim((string) $redirectUrl) !== '') {
            $payload['redirectUrl'] = trim((string) $redirectUrl);
        }

        return $this->request('POST', $this->onboardingEndpoint, $payload, [], $this->onboardingBaseUrl);
    }

    private function sendGetOnboardingStatusRequest($onboardingId)
    {
        $onboardingId = rawurlencode(trim((string) $onboardingId));
        return $this->request('GET', rtrim($this->onboardingEndpoint, '/') . '/' . $onboardingId . '/status', null, [], $this->onboardingBaseUrl);
    }

    private function sendOnboardingRegistrationRequest($country, $organizationNumber)
    {
        $payload = [
            'country' => strtoupper(trim((string) $country)),
            'organizationNumber' => trim((string) $organizationNumber),
        ];

        return $this->request('POST', rtrim($this->onboardingEndpoint, '/') . '/registration', $payload, [], $this->onboardingBaseUrl);
    }

    private function sendCancelOnboardingRequest($onboardingId)
    {
        $onboardingId = rawurlencode(trim((string) $onboardingId));
        return $this->request('POST', rtrim($this->onboardingEndpoint, '/') . '/' . $onboardingId . '/cancel', null, [], $this->onboardingBaseUrl);
    }

    private function sendCreateConsentRequest(array $consent, array $psu = [])
    {
        $payload = [
            'userId' => trim((string) ($consent['userId'] ?? '')),
            'bic' => strtoupper(trim((string) ($consent['bic'] ?? ''))),
            'organizationNumber' => trim((string) ($consent['organizationNumber'] ?? '')),
            'preferredScaMethod' => trim((string) ($consent['preferredScaMethod'] ?? 'Redirect')),
        ];

        foreach (['bankBranch', 'callbackUrl'] as $optionalField) {
            if (isset($consent[$optionalField]) && trim((string) $consent[$optionalField]) !== '') {
                $payload[$optionalField] = trim((string) $consent[$optionalField]);
            }
        }

        return $this->request('POST', $this->consentEndpoint, $payload, $this->psuHeaders($psu), $this->apiBaseUrl);
    }

    private function buildPaymentInitiationPayload()
    {
        $fromAccount = $this->findAccountById($this->paymentFromAccountId);
        $fromCurrency = strtoupper(trim((string) ($fromAccount['currency'] ?? $this->paymentCurrency)));
        if ($fromCurrency === '') {
            $fromCurrency = 'NOK';
        }

        $fromAccountPayload = [];
        if (!empty($fromAccount['bban'])) {
            $fromAccountPayload = [
                'type' => 'bban',
                'bban' => trim((string) $fromAccount['bban']),
            ];
        } elseif (!empty($fromAccount['iban'])) {
            $fromAccountPayload = [
                'type' => 'iban',
                'iban' => trim((string) $fromAccount['iban']),
            ];
        }

        if ($fromAccountPayload === []) {
            $fallbackFrom = trim((string) $this->paymentFromAccountId);
            if ($fallbackFrom === '') {
                throw new Exception('Unable to resolve from account BBAN/IBAN from the selected account. Fetch accounts again and re-select account.');
            }

            $fromAccountPayload = [
                'type' => 'bban',
                'bban' => $fallbackFrom,
            ];
        }

        $toAccountPayload = [];
        if ($this->paymentToBban !== '') {
            $toAccountPayload = [
                'type' => 'bban',
                'bban' => $this->paymentToBban,
            ];
        } else {
            $toAccountPayload = [
                'type' => 'iban',
                'iban' => $this->paymentToIban,
            ];
        }

        $normalizedDueDate = $this->normalizeDate($this->paymentDueDate);
        $normalizedAmount = $this->normalizeAmount($this->paymentAmount);
        $fromContactName = trim((string) ($fromAccount['ownerName'] ?? $fromAccount['name'] ?? ''))
            ?: 'Organization ' . $this->organizationNumber;

        $payload = [
            'metadata' => [
                'endToEndId' => $this->paymentEndToEndId !== '' ? $this->paymentEndToEndId : $this->generateEndToEndId(),
            ],
            'from' => [
                'account' => $fromAccountPayload,
                'contactInformation' => [
                    'name' => $fromContactName,
                ],
                'currency' => $fromCurrency,
                'organizationNumber' => $this->normalizeOrganizationNumber($this->organizationNumber),
            ],
            'to' => [
                'account' => $toAccountPayload,
                'contactInformation' => [
                    'name' => $this->paymentToName,
                ],
            ],
            'amount' => [
                'amount' => $normalizedAmount,
                'currency' => $this->paymentCurrency,
            ],
            'dueDate' => $normalizedDueDate,
        ];

        if ($this->paymentRemittance !== '') {
            $payload['remittanceInformation'] = [
                'type' => 'message',
                'value' => $this->paymentRemittance,
            ];
        }

        if (in_array($this->paymentPurposeCode, ['SUPP', 'TAXS', 'SALA', 'VATX', 'OTHR'], true)) {
            $payload['purposeCode'] = $this->paymentPurposeCode;
        }

        return $this->prunePayload($payload);
    }

    private function buildCrossBorderPaymentPayload()
    {
        $fromAccount = $this->findAccountById($this->paymentFromAccountId);
        $fromCurrency = strtoupper(trim((string) ($fromAccount['currency'] ?? $this->paymentCurrency)));
        if ($fromCurrency === '') {
            $fromCurrency = 'NOK';
        }

        $fromBban = trim((string) ($fromAccount['bban'] ?? ''));
        if ($fromBban === '') {
            $fromBban = trim((string) $this->paymentFromAccountId);
        }
        $this->requireValue($fromBban, 'From account BBAN is required to initiate a cross-border payment.');

        $fromBic = strtoupper(trim((string) ($this->paymentFromAccountBic !== '' ? $this->paymentFromAccountBic : ($fromAccount['bic'] ?? $this->bic))));
        $this->requireValue($fromBic, 'From account BIC is required to initiate a cross-border payment.');

        $toAccountPayload = [];
        if ($this->paymentToIban !== '') {
            $toAccountPayload = [
                'type' => 'iban',
                'bic' => $this->paymentToBic,
                'iban' => $this->paymentToIban,
            ];
        } else {
            $toAccountPayload = [
                'type' => 'bban',
                'bic' => $this->paymentToBic,
                'bban' => $this->paymentToBban,
            ];
            if ($this->paymentToClearingCode !== '') {
                $toAccountPayload['clearingCode'] = $this->paymentToClearingCode;
            }
        }

        $fromContactName = trim((string) ($fromAccount['ownerName'] ?? $fromAccount['name'] ?? ''))
            ?: 'Organization ' . $this->organizationNumber;

        $payload = [
            'metadata' => [
                'endToEndId' => $this->paymentEndToEndId !== '' ? $this->paymentEndToEndId : $this->generateEndToEndId(),
            ],
            'from' => [
                'account' => [
                    'type' => 'bban',
                    'bic' => $fromBic,
                    'bban' => $fromBban,
                ],
                'contactInformation' => [
                    'name' => $fromContactName,
                    'address' => [
                        'streetName' => $this->paymentFromAddressStreetName,
                        'buildingNumber' => $this->paymentFromAddressBuildingNumber,
                        'city' => $this->paymentFromAddressCity,
                        'postCode' => $this->paymentFromAddressPostCode,
                        'country' => $this->paymentFromAddressCountry,
                    ],
                    'telephoneNumber' => $this->paymentFromTelephoneNumber,
                ],
                'currency' => $fromCurrency,
                'organizationNumber' => $this->normalizeOrganizationNumber($this->organizationNumber),
            ],
            'to' => [
                'account' => $toAccountPayload,
                'contactInformation' => [
                    'name' => $this->paymentToName,
                    'address' => [
                        'streetName' => $this->paymentToAddressStreetName,
                        'buildingNumber' => $this->paymentToAddressBuildingNumber,
                        'city' => $this->paymentToAddressCity,
                        'postCode' => $this->paymentToAddressPostCode,
                        'country' => $this->paymentToAddressCountry,
                    ],
                    'telephoneNumber' => $this->paymentToTelephoneNumber,
                ],
            ],
            'amount' => [
                'amount' => $this->normalizeAmount($this->paymentAmount),
                'currency' => $this->paymentCurrency,
            ],
            'dueDate' => $this->normalizeDate($this->paymentDueDate),
            'remittanceInformation' => [
                'message' => $this->paymentRemittance,
            ],
        ];

        if ($this->paymentAdditionalInformationType !== '' && $this->paymentAdditionalInformationValue !== '') {
            $payload['remittanceInformation']['additionalInformation'] = [
                [
                    'type' => $this->paymentAdditionalInformationType,
                    'value' => $this->paymentAdditionalInformationValue,
                ]
            ];
        }

        if ($this->paymentRegulatoryReportingCode !== '') {
            $payload['regulatoryReporting'] = [
                'code' => $this->paymentRegulatoryReportingCode,
                'information' => $this->paymentRegulatoryReportingInformation,
            ];
        }

        return $this->prunePayload($payload);
    }

    private function normalizeDate($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return date('Y-m-d');
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $value, $matches)) {
            $month = (int) $matches[1];
            $day = (int) $matches[2];
            $year = (int) $matches[3];
            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
            if (checkdate($day, $month, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $day, $month);
            }
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return date('Y-m-d');
        }

        return date('Y-m-d', $timestamp);
    }

    private function normalizeAmount($value)
    {
        $value = trim((string) $value);
        $value = str_replace(',', '.', $value);
        if ($value === '' || !is_numeric($value)) {
            return '1.00';
        }

        return number_format((float) $value, 2, '.', '');
    }

    private function normalizeOrganizationNumber($value)
    {
        $value = trim((string) $value);
        if ($value !== '' && ctype_digit($value)) {
            return (int) $value;
        }

        return $value;
    }

    private function findAccountById($accountId)
    {
        $accountId = trim((string) $accountId);
        if ($accountId === '' || !isset($this->accounts['accounts']) || !is_array($this->accounts['accounts'])) {
            return [];
        }

        foreach ($this->accounts['accounts'] as $account) {
            if (!is_array($account)) {
                continue;
            }
            if (trim((string) ($account['id'] ?? '')) === $accountId) {
                return $account;
            }
        }

        return [];
    }

    private function generateEndToEndId()
    {
        try {
            return bin2hex(random_bytes(16));
        } catch (Exception) {
            return uniqid('ztl', true);
        }
    }

    private function prunePayload($value)
    {
        if (!is_array($value)) {
            return $value;
        }

        if ($value === []) {
            return [];
        }

        $isList = array_keys($value) === range(0, count($value) - 1);
        $result = [];

        foreach ($value as $key => $item) {
            $item = $this->prunePayload($item);
            if ($item === null || $item === '' || $item === []) {
                continue;
            }
            if ($isList) {
                $result[] = $item;
            } else {
                $result[$key] = $item;
            }
        }

        return $result;
    }

    private function recordPaymentInitiation(array $payment, array $input)
    {
        $body = $payment['body'] ?? $payment;
        $status = is_array($body) ? ($body['paymentStatus']['status'] ?? ($body['status'] ?? null)) : null;

        $this->append([
            'type' => 'payment',
            'createdAt' => gmdate('c'),
            'input' => $this->redactEmpty($input),
            'paymentId' => is_array($body) ? ($body['paymentId'] ?? null) : null,
            'status' => $status,
            'ztlRequestId' => $payment['ztlRequestId'] ?? null,
            'response' => $body,
        ]);
    }

    private function recordPaymentApproval(array $approval, array $input)
    {
        $body = $approval['body'] ?? $approval;

        $this->append([
            'type' => 'payment_approval',
            'createdAt' => gmdate('c'),
            'input' => $this->redactEmpty($input),
            'approvalId' => is_array($body) ? ($body['id'] ?? null) : null,
            'status' => is_array($body) ? ($body['status'] ?? null) : null,
            'scaType' => is_array($body) ? ($body['sca']['type'] ?? null) : null,
            'scaUrl' => is_array($body) ? ($body['sca']['url'] ?? ($body['scaUrl'] ?? ($body['scaurl'] ?? null))) : null,
            'ztlRequestId' => $approval['ztlRequestId'] ?? null,
            'response' => $body,
        ]);
    }

    private function recordPaymentCancellation(array $cancellation, array $input)
    {
        $body = $cancellation['body'] ?? $cancellation;

        $this->append([
            'type' => 'payment_cancellation',
            'createdAt' => gmdate('c'),
            'input' => $this->redactEmpty($input),
            'cancellationId' => is_array($body) ? ($body['id'] ?? null) : null,
            'status' => is_array($body) ? ($body['currentPaymentStatus']['status'] ?? ($body['cancellationRequestStatus'] ?? null)) : null,
            'cancellationRequestStatus' => is_array($body) ? ($body['cancellationRequestStatus'] ?? null) : null,
            'reason' => is_array($body) ? ($body['reason'] ?? null) : null,
            'scaType' => is_array($body) ? ($body['sca']['type'] ?? null) : null,
            'scaUrl' => is_array($body) ? ($body['sca']['url'] ?? null) : null,
            'ztlRequestId' => $cancellation['ztlRequestId'] ?? null,
            'response' => $body,
        ]);
    }

    private function recordBulkPaymentInitiation(array $result, array $input)
    {
        $body = $result['body'] ?? $result;

        $transactions = [];
        if (is_array($body) && isset($body['transactions']) && is_array($body['transactions'])) {
            foreach ($body['transactions'] as $tx) {
                $transactions[] = [
                    'id' => $tx['id'] ?? null,
                    'endToEndId' => $tx['endToEndId'] ?? null,
                ];
            }
        }

        $this->append([
            'type' => 'bulk_payment',
            'createdAt' => gmdate('c'),
            'input' => $this->redactEmpty($input),
            'bulkId' => is_array($body) ? ($body['id'] ?? null) : null,
            'bulkStatus' => is_array($body) ? ($body['bulkStatus'] ?? null) : null,
            'transactionCount' => count($transactions),
            'transactions' => $transactions,
            'scaType' => is_array($body) ? ($body['sca']['type'] ?? null) : null,
            'scaUrl' => is_array($body) ? ($body['sca']['url'] ?? null) : null,
            'ztlRequestId' => $result['ztlRequestId'] ?? null,
            'response' => $body,
        ]);
    }

    private function ensureAccessToken()
    {
        if ($this->accessToken !== '' && (time() + 30) < $this->tokenExpiresAt) {
            return;
        }

        $result = $this->fetchAccessToken();
        if (($result['ok'] ?? false) !== true) {
            throw new ZtlApiException($result['error'] ?? 'Unable to fetch access token for ZTL.');
        }

        $this->accessToken = $result['access_token'];
        $this->tokenExpiresAt = (int) ($result['expires_at'] ?? (time() + 300));
    }

    private function fetchAccessToken()
    {
        $this->lastAuthErrors = [];
        $clientId = trim((string) $this->clientId);
        $clientSecret = trim((string) $this->clientSecret);
        if ($clientId === '' || $clientSecret === '') {
            return ['ok' => false, 'error' => 'Missing ZTL credentials. Configure clientId and clientSecret in ztl_config.php.'];
        }

        $url = rtrim($this->authBaseUrl, '/') . '/' . ltrim($this->tokenEndpoint, '/');
        $fields = [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ];
        if ($this->tokenScope !== '') {
            $fields['scope'] = $this->tokenScope;
        }
        if ($this->tokenAudience !== '') {
            $fields['audience'] = $this->tokenAudience;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields, '', '&'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        if (!$this->verifySsl) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $error !== '') {
            return ['ok' => false, 'error' => 'ZTL auth request failed: ' . $error];
        }

        $decoded = json_decode($response, true);
        if ($status < 200 || $status >= 300) {
            $message = is_array($decoded) ? ($decoded['error_description'] ?? ($decoded['error'] ?? 'Authentication failed.')) : 'Authentication failed.';
            return ['ok' => false, 'error' => 'ZTL auth failed (' . $status . '): ' . $message];
        }

        if (!is_array($decoded) || empty($decoded['access_token'])) {
            return ['ok' => false, 'error' => 'ZTL auth succeeded but no access_token was returned.'];
        }

        $expiresIn = isset($decoded['expires_in']) ? (int) $decoded['expires_in'] : 300;
        return [
            'ok' => true,
            'access_token' => (string) $decoded['access_token'],
            'expires_at' => time() + max(60, $expiresIn),
        ];
    }

    private function request($method, $endpoint, $payload = null, array $extraHeaders = [], $baseUrl = null)
    {
        $this->ensureAccessToken();
        $baseUrl = $baseUrl ?: $this->apiBaseUrl;
        $url = rtrim($baseUrl, '/') . '/' . ltrim($endpoint, '/');
        $method = strtoupper(trim((string) $method));

        $headers = array_merge(
            [
                'Authorization: Bearer ' . $this->accessToken,
                'Accept: application/json',
            ],
            $extraHeaders
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HEADER, true);

        if (!$this->verifySsl) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        if ($payload !== null) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        if ($response === false || $error !== '') {
            throw new ZtlApiException('ZTL request failed: ' . $error, $status);
        }

        $rawHeaders = substr($response, 0, $headerSize);
        $rawBody = substr($response, $headerSize);
        $decodedBody = json_decode($rawBody, true);
        $body = is_array($decodedBody) ? $decodedBody : null;
        $ztlRequestId = $this->extractHeaderValue($rawHeaders, 'x-request-id');

        $this->debugInfo = [
            'method' => $method,
            'url' => $url,
            'payload' => $payload,
            'status' => $status,
            'headers' => $headers,
            'response' => $body !== null ? $body : $rawBody,
            'ztlRequestId' => $ztlRequestId,
        ];

        if ($status < 200 || $status >= 300) {
            $message = $this->extractErrorMessage($body, $rawBody, $status);
            throw new ZtlApiException($message, $status, $ztlRequestId, $body !== null ? $body : $rawBody);
        }

        return [
            'status' => $status,
            'body' => $body,
            'rawBody' => $rawBody,
            'ztlRequestId' => $ztlRequestId,
        ];
    }

    /**
     * Send a raw HTTP request (for binary responses like PDF).
     * Does not attempt JSON decoding of the response body.
     */
    private function requestRaw($method, $endpoint, $payload = null, array $extraHeaders = [], $baseUrl = null)
    {
        $this->ensureAccessToken();
        $baseUrl = $baseUrl ?: $this->apiBaseUrl;
        $url = rtrim($baseUrl, '/') . '/' . ltrim($endpoint, '/');
        $method = strtoupper(trim((string) $method));

        $headers = array_merge(
            [
                'Authorization: Bearer ' . $this->accessToken,
            ],
            $extraHeaders
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HEADER, true);

        if (!$this->verifySsl) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        if ($payload !== null) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($response === false || $error !== '') {
            throw new ZtlApiException('ZTL request failed: ' . $error, $status);
        }

        $rawHeaders = substr($response, 0, $headerSize);
        $rawBody = substr($response, $headerSize);
        $ztlRequestId = $this->extractHeaderValue($rawHeaders, 'x-request-id');

        $this->debugInfo = [
            'method' => $method,
            'url' => $url,
            'payload' => $payload,
            'status' => $status,
            'headers' => $headers,
            'contentType' => $contentType,
            'responseSize' => strlen($rawBody),
            'ztlRequestId' => $ztlRequestId,
        ];

        if ($status < 200 || $status >= 300) {
            $decodedBody = json_decode($rawBody, true);
            $body = is_array($decodedBody) ? $decodedBody : null;
            $message = $this->extractErrorMessage($body, $rawBody, $status);
            throw new ZtlApiException($message, $status, $ztlRequestId, $body !== null ? $body : $rawBody);
        }

        return [
            'status' => $status,
            'rawBody' => $rawBody,
            'contentType' => (string) $contentType,
            'ztlRequestId' => $ztlRequestId,
        ];
    }

    private function psuHeaders(array $psu)
    {
        $headers = [];
        $psu = self::normalizePsu($psu);

        if (($psu['ipAddress'] ?? '') !== '') {
            $headers[] = 'PSU-IP-Address: ' . $psu['ipAddress'];
        }
        if (($psu['userAgent'] ?? '') !== '') {
            $headers[] = 'PSU-User-Agent: ' . $psu['userAgent'];
        }
        if (($psu['accept'] ?? '') !== '') {
            $headers[] = 'PSU-Accept: ' . $psu['accept'];
        }
        if (($psu['acceptLanguage'] ?? '') !== '') {
            $headers[] = 'PSU-Accept-Language: ' . $psu['acceptLanguage'];
        }

        return $headers;
    }

    private function extractHeaderValue($rawHeaders, $headerName)
    {
        $headerName = strtolower(trim((string) $headerName));
        foreach (preg_split('/\r\n|\r|\n/', (string) $rawHeaders) as $line) {
            $parts = explode(':', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }
            if (strtolower(trim($parts[0])) === $headerName) {
                return trim($parts[1]);
            }
        }

        return null;
    }

    private function extractErrorMessage($body, $rawBody, $status)
    {
        if (is_array($body)) {
            foreach (['message', 'detail', 'title', 'error_description', 'error'] as $key) {
                if (!empty($body[$key]) && is_string($body[$key])) {
                    return trim((string) $body[$key]);
                }
            }
        }

        $rawBody = trim((string) $rawBody);
        if ($rawBody !== '') {
            return $rawBody;
        }

        return 'The request could not be completed. Please try again.';
    }

    private function recordOnboarding(array $onboarding, array $input)
    {
        $this->append([
            'type' => 'onboarding',
            'createdAt' => gmdate('c'),
            'input' => $this->redactEmpty($input),
            'onboardingId' => $onboarding['body']['id'] ?? ($onboarding['body']['flowId'] ?? null),
            'onboardingUrl' => $onboarding['body']['url'] ?? ($onboarding['body']['onboardingUrl'] ?? null),
            'ztlRequestId' => $onboarding['ztlRequestId'] ?? null,
            'response' => $onboarding['body'] ?? $onboarding,
        ]);
    }

    private function recordOnboardingRegistration(array $registration, array $input)
    {
        $this->append([
            'type' => 'onboarding_registration',
            'createdAt' => gmdate('c'),
            'input' => $this->redactEmpty($input),
            'country' => strtoupper(trim((string) ($input['country'] ?? ''))),
            'organizationNumber' => trim((string) ($input['organizationNumber'] ?? '')),
            'statusCode' => $registration['status'] ?? null,
            'ztlRequestId' => $registration['ztlRequestId'] ?? null,
            'response' => $registration['body'] ?? $registration,
        ]);
    }

    private function recordOnboardingCancellation($onboardingId, array $cancellation, array $input)
    {
        $this->append([
            'type' => 'onboarding_cancellation',
            'createdAt' => gmdate('c'),
            'input' => $this->redactEmpty($input),
            'onboardingId' => trim((string) $onboardingId),
            'statusCode' => $cancellation['status'] ?? null,
            'ztlRequestId' => $cancellation['ztlRequestId'] ?? null,
            'response' => $cancellation['body'] ?? $cancellation,
        ]);
    }

    private function recordConsent(array $consent, array $input)
    {
        $body = $consent['body'] ?? $consent;
        $this->append([
            'type' => 'consent',
            'createdAt' => gmdate('c'),
            'input' => $this->redactEmpty($input),
            'consentId' => $body['id'] ?? null,
            'status' => $body['status'] ?? null,
            'scaType' => $body['sca']['type'] ?? null,
            'scaUrl' => $body['sca']['url'] ?? ($body['scaUrl'] ?? ($body['scaurl'] ?? null)),
            'ztlRequestId' => $consent['ztlRequestId'] ?? null,
            'response' => $body,
        ]);
    }

    private function all()
    {
        if (!is_file($this->storagePath)) {
            return [];
        }

        $contents = file_get_contents($this->storagePath);
        if ($contents === false || trim($contents) === '') {
            return [];
        }

        $records = json_decode($contents, true);
        return is_array($records) ? $records : [];
    }

    private function enrichCompaniesWithKnownCountryCodes(array $companies)
    {
        $countryMap = $this->getKnownCompanyCountryCodes();

        foreach ($companies as $index => $company) {
            if (!is_array($company)) {
                continue;
            }

            $organizationNumber = trim((string) ($company['organizationNumber'] ?? ''));
            if ($organizationNumber === '') {
                continue;
            }

            $countryCode = strtoupper(trim((string) ($company['countryCode'] ?? ($company['country'] ?? ''))));
            if ($countryCode === '' && isset($countryMap[$organizationNumber])) {
                $countryCode = $countryMap[$organizationNumber];
            }

            $companies[$index]['countryCode'] = $countryCode ?: 'DK';
        }

        return $companies;
    }

    private function getKnownCompanyCountryCodes()
    {
        $countryMap = [];
        foreach ($this->all() as $record) {
            if (!is_array($record)) {
                continue;
            }

            $organizationNumber = trim((string) ($record['organizationNumber'] ?? ($record['input']['organizationNumber'] ?? '')));
            $countryCode = strtoupper(trim((string) ($record['country'] ?? ($record['input']['country'] ?? ''))));

            if ($organizationNumber !== '' && $countryCode !== '') {
                $countryMap[$organizationNumber] = $countryCode;
            }
        }

        return $countryMap;
    }

    public function getCompanySubscriptionStatus($organizationNumber)
    {
        $organizationNumber = $this->normalizeOrganizationNumber($organizationNumber);
        $this->requireValue($organizationNumber, 'Organization number is required to check company subscription status.');

        $companiesResult = $this->getOnboardedCompanies();
        $companies = $companiesResult['response'] ?? [];

        foreach ($companies as $company) {
            $companyOrganizationNumber = $this->normalizeOrganizationNumber($company['organizationNumber'] ?? '');
            if ($companyOrganizationNumber !== $organizationNumber) {
                continue;
            }

            return [
                'found' => true,
                'activeSubscription' => !empty($company['activeSubscription']),
                'company' => $company,
            ];
        }

        return [
            'found' => false,
            'activeSubscription' => false,
            'company' => null,
        ];
    }

    public function findCompanySubscriptionStatus(array $companies, $organizationNumber)
    {
        $organizationNumber = $this->normalizeOrganizationNumber($organizationNumber);
        $this->requireValue($organizationNumber, 'Organization number is required to check company subscription status.');

        foreach ($companies as $company) {
            if (!is_array($company)) {
                continue;
            }

            $companyOrganizationNumber = $this->normalizeOrganizationNumber($company['organizationNumber'] ?? '');
            if ($companyOrganizationNumber !== $organizationNumber) {
                continue;
            }

            return [
                'found' => true,
                'activeSubscription' => $this->toBool($company['activeSubscription'] ?? false),
                'company' => $company,
            ];
        }

        return [
            'found' => false,
            'activeSubscription' => false,
            'company' => null,
        ];
    }

    private function append(array $record)
    {
        $records = $this->all();
        $records[] = $record;
        file_put_contents($this->storagePath, json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function redactEmpty(array $input)
    {
        $result = [];
        foreach ($input as $key => $value) {
            if ($value === null || $value === '' || $value === []) {
                continue;
            }
            $result[$key] = $value;
        }

        return $result;
    }

    private function readConfigValue(array $config, $key, $envName, $default = '')
    {
        if (array_key_exists($key, $config) && $config[$key] !== null && $config[$key] !== '') {
            return $config[$key];
        }

        $envValue = getenv($envName);
        if ($envValue !== false && $envValue !== '') {
            return $envValue;
        }

        return $default;
    }

    private function readBoolConfigValue(array $config, $key, $envName, $default = false)
    {
        if (array_key_exists($key, $config) && $config[$key] !== null && $config[$key] !== '') {
            return filter_var($config[$key], FILTER_VALIDATE_BOOLEAN);
        }

        $envValue = getenv($envName);
        if ($envValue !== false && $envValue !== '') {
            return filter_var($envValue, FILTER_VALIDATE_BOOLEAN);
        }

        return (bool) $default;
    }

    private function requireValue($value, $message)
    {
        if ($value === null || $value === '' || $value === []) {
            throw new Exception($message);
        }
    }

    private static function serverValue(array $server, $key, $default = '')
    {
        if (!isset($server[$key])) {
            return $default;
        }

        $value = $server[$key];
        return is_string($value) ? $value : $default;
    }

    private static function firstIpAddress($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $parts = explode(',', $value);
        return trim((string) ($parts[0] ?? ''));
    }

    private function toBool($value)
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            if (in_array($normalized, ['1', 'true', 'yes', 'y', 'active'], true)) {
                return true;
            }
            if (in_array($normalized, ['0', 'false', 'no', 'n', 'inactive'], true)) {
                return false;
            }
        }

        return !empty($value);
    }
}

// Shared constants and utility functions used by both ztl_landing.php and ztl_settle.php.
// Both plugin controllers include this file via require_once.

const ZTL_IBAN_BIC_RANGES = [
    'SE' => [
        'len' => 24,
        'offset' => 4,
        'width' => 4,
        'ranges' => [
            [1100, 1199, 'NDEASESS'],
            [1200, 1399, 'DABASESX'],
            [1400, 2099, 'NDEASESS'],
            [2300, 2399, 'AABORSES'],
            [3000, 3399, 'NDEASESS'],
            [3410, 4999, 'NDEASESS'],
            [5000, 5999, 'ESSESESS'],
            [6000, 6999, 'HANDSESS'],
            [7000, 7999, 'SWEDSESS'],
            [8000, 8999, 'SWEDSESS'],
            [9020, 9069, 'ELLFSESS'],
            [9100, 9109, 'NDNTSE21'],
            [9120, 9149, 'ESSESESS'],
            [9150, 9169, 'SKIASESS'],
            [9170, 9179, 'IKANSE21'],
            [9180, 9189, 'DABASESX'],
            [9190, 9199, 'DNBASESX'],
            [9230, 9239, 'MARGSE22'],
            [9250, 9259, 'SBABSE2X'],
            [9260, 9269, 'DNBASESX'],
            [9270, 9279, 'ICABSE2X'],
            [9280, 9289, 'RESUSE21'],
            [9300, 9349, 'SPOBSE2X'],
            [9400, 9449, 'FORXSE21'],
            [9500, 9549, 'NDNTSE21'],
        ],
    ],
    'NO' => [
        'len' => 15,
        'offset' => 4,
        'width' => 4,
        'ranges' => [
            [1503, 1599, 'DNBANOKK'],
            [3201, 3399, 'HANDNOKK'],
            [4580, 4599, 'SWEDSESS'],
            [6001, 6399, 'NDEANOKK'],
            [7001, 7599, 'DNBANOKK'],
            [9710, 9799, 'NDEANOKK'],
        ],
    ],
    'DK' => [
        'len' => 18,
        'offset' => 4,
        'width' => 4,
        'ranges' => [
            [20, 29, 'NDEADKKK'],
            [30, 39, 'DABADKKK'],
            [40, 49, 'JYBADKKK'],
            [50, 59, 'SABORPKOKK'],
        ],
    ],
];

const ZTL_AUTHORIZED_CONSENT_STATUSES = ['AUTHORIZED', 'VALID'];

function ztl_iban_to_bic(string $iban): string
{
    $iban = strtoupper(preg_replace('/\s+/', '', $iban));
    $spec = ZTL_IBAN_BIC_RANGES[substr($iban, 0, 2)] ?? null;
    if (!$spec || strlen($iban) !== $spec['len']) {
        return '';
    }
    $key = (int) substr($iban, $spec['offset'], $spec['width']);
    foreach ($spec['ranges'] as [$lo, $hi, $bic]) {
        if ($key >= $lo && $key <= $hi) {
            return $bic;
        }
    }
    return '';
}

function ztl_consent_is_valid(?array $consent): bool
{
    if (empty($consent['id']) || !in_array($consent['status'] ?? '', ZTL_AUTHORIZED_CONSENT_STATUSES, true)) {
        return false;
    }
    $validUntil = $consent['validUntil'] ?? '';
    if ($validUntil === '') {
        return true;
    }
    $expiry = strtotime($validUntil);
    return $expiry === false || $expiry >= time();
}

function ztl_find_account(?array $accounts, string $accountId): ?array
{
    foreach ($accounts['accounts'] ?? [] as $acct) {
        if (($acct['id'] ?? '') === $accountId) {
            return $acct;
        }
    }
    return null;
}

function ztl_extract_sca_url(?array $r): string
{
    return $r ? trim($r['scaUrl'] ?? $r['scaurl'] ?? $r['sca']['url'] ?? '') : '';
}

function ztl_humanize_error_code(string $code): string
{
    return ucfirst(strtolower(trim(preg_replace('/(?<!^)([A-Z])/', ' $1', $code)))) . '.';
}

function ztl_extract_error_message(array $body): string
{
    if (isset($body[0]) && is_array($body[0])) {
        $messages = [];
        foreach ($body as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            if (!empty($entry['reason']) && is_string($entry['reason'])) {
                $messages[] = rtrim(trim($entry['reason']), '.') . '.';
            } elseif (!empty($entry['code']) && is_string($entry['code'])) {
                $messages[] = ztl_humanize_error_code($entry['code']);
            }
        }
        return implode(' ', array_unique($messages));
    }
    foreach (['reason', 'message', 'detail', 'title'] as $key) {
        if (!empty($body[$key]) && is_string($body[$key])) {
            return rtrim(trim($body[$key]), '.') . '.';
        }
    }
    if (!empty($body['code']) && is_string($body['code'])) {
        return ztl_humanize_error_code($body['code']);
    }
    return '';
}

function ztl_format_error(Throwable $e): string
{
    if ($e instanceof ZtlApiException) {
        $body = $e->getResponseBody();
        if (is_array($body) && ($msg = ztl_extract_error_message($body)) !== '') {
            return $msg;
        }
    }
    $raw = $e->getMessage();
    if ($raw !== '' && ($raw[0] === '[' || $raw[0] === '{')) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded) && ($msg = ztl_extract_error_message($decoded)) !== '') {
            return $msg;
        }
    }
    return $raw !== '' ? $raw : 'An unexpected error occurred. Please try again.';
}
