<?php
/**
 * ZTL Landing Page Controller.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/modules/ztl/ZtlSettle.class.php';

$configFile = __DIR__ . '/ztl_config.php';
$config = is_file($configFile) ? require $configFile : [];

define('ZTL_LANDING_URL', $config['landingUrl']);
define('ZTL_SETTLE_URL', $config['settleUrl']);

// Prevent browser from caching this page
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

const ZTL_FORM_DEFAULTS = [
    'country' => '',
    'organizationNumber' => '',
    'userId' => 'TX99999',
    'bic' => 'DNBANOKK',
    'bankBranch' => '',
    'preferredScaMethod' => 'Redirect',
];

// Landing-specific helpers

function ztl_normalize_onboarding(?array $o): ?array
{
    if (!$o) {
        return null;
    }
    $o['id'] = $o['id'] ?? $o['flowId'] ?? null;
    $o['url'] = $o['url'] ?? $o['onboardingUrl'] ?? null;
    return $o;
}

function ztl_company_name(?array $company): string
{
    foreach (['name', 'companyName', 'organizationName', 'organisationName', 'legalName'] as $key) {
        if (!empty($company[$key])) {
            return trim((string) $company[$key]);
        }
    }
    return 'Onboarded Company';
}

function ztl_redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function ztl_customer_onboarding_defaults($db): array
{
    $customerId = (int) ($_SESSION['customerid'] ?? 0);
    if ($customerId <= 0 || !is_object($db) || !method_exists($db, 'GetRow')) {
        return [];
    }

    try {
        $row = $db->GetRow(
            'SELECT company_orgno, country_code FROM public.customer WHERE customerid = ?',
            [$customerId]
        );
    } catch (Throwable $e) {
        return [];
    }

    if (!is_array($row)) {
        return [];
    }

    $defaults = [];
    $country = strtoupper(trim((string) ($row['country_code'] ?? $row['COUNTRY_CODE'] ?? '')));
    $organizationNumber = trim((string) ($row['company_orgno'] ?? $row['COMPANY_ORGNO'] ?? ''));

    if ($country !== '') {
        $defaults['country'] = $country;
    }
    if ($organizationNumber !== '') {
        $defaults['organizationNumber'] = $organizationNumber;
    }

    return $defaults;
}

$service = new ZtlSettle($db ?? null, null, $config);
$defaults = $service->getFormValues();
$customerDefaults = ztl_customer_onboarding_defaults($db ?? null);
$currentCustomerId = (int) ($_SESSION['customerid'] ?? 0);
$sessionForm = $_SESSION['ztl_form'] ?? [];
if ((int) ($_SESSION['ztl_form_customerid'] ?? 0) !== $currentCustomerId) {
    $sessionForm = [];
}

// Build form values
$form = ['onboardingRedirectUrl' => ZTL_LANDING_URL, 'consentCallbackUrl' => ZTL_LANDING_URL];
foreach (ZTL_FORM_DEFAULTS as $key => $fallback) {
    $form[$key] = $_REQUEST[$key]
        ?? $sessionForm[$key]
        ?? $customerDefaults[$key]
        ?? $defaults[$key]
        ?? $fallback;
}

foreach (['country', 'organizationNumber'] as $key) {
    if (trim((string) $form[$key]) === '' && !empty($customerDefaults[$key])) {
        $form[$key] = $customerDefaults[$key];
    }
}

$_SESSION['ztl_form'] = $form;
$_SESSION['ztl_form_customerid'] = $currentCustomerId;

$serverPsu = ZtlSettle::psuFromServer($_SERVER);
$psu = ZtlSettle::normalizePsu([
    'ipAddress' => $_REQUEST['psuIpAddress'] ?? $serverPsu['ipAddress'],
    'userAgent' => $_REQUEST['psuUserAgent'] ?? $serverPsu['userAgent'],
    'accept' => $_REQUEST['psuAccept'] ?? $serverPsu['accept'],
    'acceptLanguage' => $_REQUEST['psuAcceptLanguage'] ?? $serverPsu['acceptLanguage'],
]);
$_SESSION['ztl_psu'] = $psu;

$service->setFormValues($form);
$service->setPsu($psu);

// Session state

$onboarding = ztl_normalize_onboarding($_SESSION['ztl_onboarding'] ?? null);
$onboardingStatus = $_SESSION['ztl_onboarding_status'] ?? null;
$companies = $_SESSION['ztl_companies'] ?? [];
$showCompanyPicker = !empty($_SESSION['ztl_show_company_picker']);
$consent = $_SESSION['ztl_consent'] ?? null;
$accounts = $_SESSION['ztl_accounts'] ?? null;
$selectedFromAccountId = $_SESSION['ztl_selected_from_account'] ?? '';
$message = null;

$service->setConsentId($consent['id'] ?? '');
$service->setAccounts($accounts);

// SCA consent callback

if (isset($_GET['status']) && !empty($consent['id'])) {
    try {
        $resp = $service->getConsentStatus($consent['id'])['response'] ?? null;
        if ($resp) {
            $consent['status'] = $resp['status'] ?? $consent['status'] ?? null;
            $consent['validUntil'] = $resp['validUntil'] ?? $consent['validUntil'] ?? null;
            $_SESSION['ztl_consent'] = $consent;
        }
        $message = ['Consent status refreshed: ' . ($consent['status'] ?? 'unknown'), 'success'];
    } catch (Throwable $e) {
        $message = ['Could not refresh status: ' . ztl_format_error($e), 'error'];
    }
}

// POST actions

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        if (isset($_POST['start_onboarding'])) {
            $r = $service->startOnboarding();
            $onboarding = ztl_normalize_onboarding($r['response'] ?? null);
            $_SESSION['ztl_onboarding'] = $onboarding;
            $_SESSION['ztl_onboarding_status'] = null;
            $onboardingStatus = null;

            if (!empty($onboarding['url'])) {
                ztl_redirect($onboarding['url']);
            }
            $message = [$r['message'], 'success'];

        } elseif (isset($_POST['check_onboarding'])) {
            $id = trim($_POST['onboardingId'] ?? $onboarding['id'] ?? '');
            if ($id === '') {
                throw new Exception('Onboarding ID is required to check onboarding status.');
            }
            $r = $service->getOnboardingStatus($id);
            $onboardingStatus = $r['response'] ?? null;
            $_SESSION['ztl_onboarding_status'] = $onboardingStatus;
            $message = [$r['message'], 'success'];

        } elseif (isset($_POST['use_company'])) {
            $orgNum = $_POST['companyOrganizationNumber'] ?? '';
            if ($orgNum !== '') {
                $form['country'] = $_POST['companyCountryCode'] ?? '';
                $form['organizationNumber'] = $orgNum;

                $_SESSION['ztl_form'] = $form;
                $_SESSION['ztl_onboarding_status'] = ['status' => 'Accepted'];
                $_SESSION['ztl_show_company_picker'] = false;

                $onboardingStatus = $_SESSION['ztl_onboarding_status'];
                $showCompanyPicker = false;
                $message = ["Company {$orgNum} selected. You can now proceed to bank consent.", 'success'];
            }

        } elseif (isset($_POST['use_another_company']) || isset($_POST['refresh_companies'])) {
            $r = $service->getOnboardedCompanies();
            $companies = $r['response'] ?? [];
            $_SESSION['ztl_companies'] = $companies;
            $_SESSION['ztl_show_company_picker'] = true;
            $showCompanyPicker = true;
            $message = isset($_POST['use_another_company'])
                ? ['Select an onboarded company or start a new onboarding.', 'success']
                : [$r['message'], 'success'];

        } elseif (isset($_POST['activate_subscription'])) {
            $country = $_POST['companyCountryCode'] ?? $form['country'];
            $orgNum = $_POST['companyOrganizationNumber'] ?? $_POST['organizationNumber'] ?? $form['organizationNumber'];
            $r = $service->activateCompanySubscription($country, $orgNum);
            $companies = $service->getOnboardedCompanies()['response'] ?? [];
            $_SESSION['ztl_companies'] = $companies;
            $message = [$r['message'], 'success'];

        } elseif (isset($_POST['create_consent'])) {
            $form['userId'] = trim($_POST['userId'] ?? $form['userId']);
            $form['bic'] = strtoupper(trim($_POST['bic'] ?? $form['bic']));
            $form['bankBranch'] = trim($_POST['bankBranch'] ?? $form['bankBranch']);
            $form['preferredScaMethod'] = trim($_POST['preferredScaMethod'] ?? $form['preferredScaMethod']);
            $_SESSION['ztl_form'] = $form;
            $service->setFormValues($form);

            if (ztl_consent_is_valid($consent)) {
                $message = ['Existing consent is still valid. No new consent needed.', 'success'];
            } else {
                $r = $service->createConsent();
                $consent = $r['response'] ?? null;
                $_SESSION['ztl_consent'] = $consent;
                $service->setConsentId($consent['id'] ?? '');

                // Consent changed: reset downstream account state.
                $_SESSION['ztl_accounts'] = null;
                $_SESSION['ztl_selected_from_account'] = '';
                $accounts = null;
                $selectedFromAccountId = '';

                $scaUrl = ztl_extract_sca_url($consent);
                if ($scaUrl !== '') {
                    ztl_redirect($scaUrl);
                }
                $message = [$r['message'], 'success'];
            }

        } elseif (isset($_POST['check_consent_status'])) {
            $id = trim($_POST['consentId'] ?? $consent['id'] ?? '');
            if ($id === '') {
                throw new Exception('Consent ID is required to check consent status.');
            }
            $r = $service->getConsentStatus($id);
            $resp = $r['response'] ?? null;
            if ($resp && $consent) {
                $consent['status'] = $resp['status'] ?? $consent['status'] ?? null;
                $consent['validUntil'] = $resp['validUntil'] ?? $consent['validUntil'] ?? null;
                $_SESSION['ztl_consent'] = $consent;
                $service->setConsentId($consent['id'] ?? '');
            }
            $message = [$r['message'], 'success'];

        } elseif (isset($_POST['fetch_accounts'])) {
            $id = trim($consent['id'] ?? '');
            if ($id === '') {
                throw new Exception('A valid consent ID is required to fetch accounts.');
            }
            $r = $service->getAccounts($id);
            $accounts = $r['response'] ?? null;
            $_SESSION['ztl_accounts'] = $accounts;
            $service->setAccounts($accounts);
            $message = [$r['message'], 'success'];

        } elseif (isset($_POST['select_from_account'])) {
            $selectedFromAccountId = trim($_POST['fromAccountId'] ?? '');
            if ($selectedFromAccountId === '') {
                throw new Exception('Please select a sender account.');
            }
            $_SESSION['ztl_selected_from_account'] = $selectedFromAccountId;
            ztl_redirect(ZTL_SETTLE_URL);

        } elseif (isset($_POST['change_from_account'])) {
            $_SESSION['ztl_selected_from_account'] = '';
            $selectedFromAccountId = '';
            $message = ['You can now select a different sender account.', 'success'];

        } elseif (isset($_POST['reset_session'])) {
            foreach (array_keys($_SESSION) as $key) {
                if (str_starts_with($key, 'ztl_')) {
                    unset($_SESSION[$key]);
                }
            }

            $form = ['onboardingRedirectUrl' => ZTL_LANDING_URL, 'consentCallbackUrl' => ZTL_LANDING_URL];
            foreach (ZTL_FORM_DEFAULTS as $key => $fallback) {
                $form[$key] = $customerDefaults[$key] ?? $defaults[$key] ?? $fallback;
            }
            $_SESSION['ztl_form'] = $form;
            $_SESSION['ztl_form_customerid'] = $currentCustomerId;
            $service->setFormValues($form);

            $onboarding = $onboardingStatus = $consent = $accounts = null;
            $companies = [];
            $showCompanyPicker = false;
            $selectedFromAccountId = '';

            $message = ['Session reset. You can now start a fresh onboarding.', 'success'];
        }

    } catch (Throwable $e) {
        $errorMsg = ztl_format_error($e);
        $type = 'error';

        // Recover from "company already exists" on initial onboarding.
        $isConflict = isset($_POST['start_onboarding'])
            && $e instanceof ZtlApiException
            && $e->getStatusCode() === 409;

        if ($isConflict) {
            $orgNum = $form['organizationNumber'];
            try {
                $service->activateCompanySubscription($form['country'], $orgNum);
                try {
                    $companies = $service->getOnboardedCompanies()['response'] ?? [];
                    $_SESSION['ztl_companies'] = $companies;
                } catch (Throwable) {
                }
                $_SESSION['ztl_onboarding_status'] = ['status' => 'Accepted'];
                $onboardingStatus = $_SESSION['ztl_onboarding_status'];
                $errorMsg = "Company {$orgNum} is already onboarded. Subscription re-activated.";
                $type = 'warning';
            } catch (Throwable) {
                $errorMsg = "Company {$orgNum} already exists but could not be activated. "
                    . 'Please use a different organization number and start full onboarding.';
            }
        }

        $message = [$errorMsg, $type];
    }
}

// Derived state

$formOrgNumber = trim($form['organizationNumber']);
$onboardedCompany = null;

if ($formOrgNumber !== '' && !$showCompanyPicker) {
    foreach ($companies as $c) {
        if (is_array($c) && trim($c['organizationNumber'] ?? '') === $formOrgNumber) {
            $onboardedCompany = $c;
            break;
        }
    }
}

$hasOnboardedCompany = $onboardedCompany !== null;
$onboardedCompanyName = ztl_company_name($onboardedCompany);
$displayCompanyForm = !$hasOnboardedCompany || $showCompanyPicker;
$consentAuthorized = ztl_consent_is_valid($consent);

// Auto-fetch accounts once consent is authorized.
if ($consentAuthorized && !$accounts && !empty($consent['id'])) {
    try {
        $accounts = $service->getAccounts($consent['id'])['response'] ?? null;
        $_SESSION['ztl_accounts'] = $accounts;
        $service->setAccounts($accounts);
    } catch (Throwable $e) {
        $message ??= ['Could not fetch accounts: ' . ztl_format_error($e), 'error'];
    }
}

// Drop the selected account if it's gone from the latest account list.
if ($selectedFromAccountId !== '' && $accounts && !ztl_find_account($accounts, $selectedFromAccountId)) {
    $selectedFromAccountId = '';
    $_SESSION['ztl_selected_from_account'] = '';
}

$setupComplete = $consentAuthorized && $selectedFromAccountId !== '';

// Auto-bypass: if setup is already complete on a fresh GET, send directly to settle.
if ($setupComplete && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    ztl_redirect(ZTL_SETTLE_URL);
}

// Render

[$messageText, $messageType] = is_array($message)
    ? [(string) ($message[0] ?? ''), (string) ($message[1] ?? '')]
    : ['', ''];

if (isset($smarty) && is_object($smarty)) {
    $smarty->assign([
        'page_title' => 'ZTL Setup',
        'page_subtitle' => 'Company onboarding and bank consent setup',
        'form' => $form,
        'psu' => $psu,
        'onboarding' => $onboarding,
        'onboardingStatus' => $onboardingStatus,
        'companies' => $companies,
        'hasOnboardedCompany' => $hasOnboardedCompany,
        'onboardedCompanyName' => $onboardedCompanyName,
        'showCompanyPicker' => $showCompanyPicker,
        'displayCompanyForm' => $displayCompanyForm,
        'consent' => $consent,
        'consentAuthorized' => $consentAuthorized,
        'accounts' => $accounts,
        'selectedFromAccountId' => $selectedFromAccountId,
        'setupComplete' => $setupComplete,
        'settle_url' => ZTL_SETTLE_URL,
        'messageText' => $messageText,
        'messageType' => $messageType,
    ]);
    $smarty->display('ztl_landing.tpl');
    return;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['status' => 'landing_setup', 'consentAuthorized' => $consentAuthorized, 'setupComplete' => $setupComplete], JSON_PRETTY_PRINT);
