<?php
/**
 * ZTL Settle Page Controller.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/modules/ztl/ZtlSettle.class.php';

$ztlConfig = is_file(__DIR__ . '/ztl_config.php') ? require __DIR__ . '/ztl_config.php' : [];

define('ZTL_SETTLE_URL', $ztlConfig['settleUrl']);
define('ZTL_LANDING_URL', $ztlConfig['landingUrl']);
define('ZTL_PAYMENTS_API', $ztlConfig['paymentsApiUrl']);

// Prevent browser from caching this page — stale cache causes 0-data glitch on navigation
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Settle-specific helpers

function ztl_extract_payment_id($payment): string
{
    if (!is_array($payment)) {
        return '';
    }
    return trim((string) ($payment['paymentId'] ?? $payment['id'] ?? ''));
}

function ztl_is_terminal_approval_status($status): bool
{
    return in_array(strtoupper(trim((string) $status)), [
        'ACCEPTED',
        'APPROVED',
        'AUTHORIZED',
        'COMPLETED',
        'VALID',
        'REJECTED',
        'CANCELLED',
        'CANCELED',
        'FAILED',
        'EXPIRED',
    ], true);
}

function ztl_fetch_outstanding_payments(): array
{
    $ch = curl_init(ZTL_PAYMENTS_API);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
    ]);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $status < 200 || $status >= 300) {
        return [];
    }
    $decoded = json_decode($response, true);
    return is_array($decoded) ? $decoded : [];
}

// Bootstrap

$service = new ZtlSettle($db ?? null, null, $ztlConfig);
$serviceFormDefaults = $service->getFormValues();
$formSessionDefaults = $_SESSION['ztl_form'] ?? [];

// Form values
$form = [
    'country' => $_REQUEST['country'] ?? $formSessionDefaults['country'] ?? $serviceFormDefaults['country'] ?? 'NO',
    'organizationNumber' => $_REQUEST['organizationNumber'] ?? $formSessionDefaults['organizationNumber'] ?? $serviceFormDefaults['organizationNumber'] ?? '',
    'onboardingRedirectUrl' => ZTL_SETTLE_URL,
    'userId' => $_REQUEST['userId'] ?? $formSessionDefaults['userId'] ?? $serviceFormDefaults['userId'] ?? 'TX99999',
    'bic' => $_REQUEST['bic'] ?? $formSessionDefaults['bic'] ?? $serviceFormDefaults['bic'] ?? 'DNBANOKK',
    'bankBranch' => $_REQUEST['bankBranch'] ?? $formSessionDefaults['bankBranch'] ?? $serviceFormDefaults['bankBranch'] ?? '',
    'consentCallbackUrl' => ZTL_SETTLE_URL,
    'preferredScaMethod' => $_REQUEST['preferredScaMethod'] ?? $formSessionDefaults['preferredScaMethod'] ?? $serviceFormDefaults['preferredScaMethod'] ?? 'Redirect',
];
$_SESSION['ztl_form'] = $form;

$paymentMode = $_REQUEST['paymentMode'] ?? $_SESSION['ztl_payment_mode'] ?? 'domestic';
if (!in_array($paymentMode, ['domestic', 'cross_border'], true)) {
    $paymentMode = 'domestic';
}
$_SESSION['ztl_payment_mode'] = $paymentMode;

$paymentSessionDefaults = $_SESSION['ztl_payment_form'] ?? [];
$paymentForm = [
    'paymentCallbackUrl' => ZTL_SETTLE_URL,
    'paymentPreferredScaMethod' => $_REQUEST['paymentPreferredScaMethod'] ?? $paymentSessionDefaults['paymentPreferredScaMethod'] ?? 'Redirect',
    'paymentFromAccountId' => $_REQUEST['paymentFromAccountId'] ?? $paymentSessionDefaults['paymentFromAccountId'] ?? '',
    'paymentFromAccountBic' => $_REQUEST['paymentFromAccountBic'] ?? $paymentSessionDefaults['paymentFromAccountBic'] ?? '',
    'paymentToName' => $_REQUEST['paymentToName'] ?? $paymentSessionDefaults['paymentToName'] ?? '',
    'paymentToBban' => $_REQUEST['paymentToBban'] ?? $paymentSessionDefaults['paymentToBban'] ?? '',
    'paymentToIban' => $_REQUEST['paymentToIban'] ?? $paymentSessionDefaults['paymentToIban'] ?? '',
    'paymentToBic' => $_REQUEST['paymentToBic'] ?? $paymentSessionDefaults['paymentToBic'] ?? '',
    'paymentToCountry' => $_REQUEST['paymentToCountry'] ?? $paymentSessionDefaults['paymentToCountry'] ?? '',
    'paymentToClearingCode' => $_REQUEST['paymentToClearingCode'] ?? $paymentSessionDefaults['paymentToClearingCode'] ?? '',
    'paymentCurrency' => $_REQUEST['paymentCurrency'] ?? $paymentSessionDefaults['paymentCurrency'] ?? 'NOK',
    'paymentAmount' => $_REQUEST['paymentAmount'] ?? $paymentSessionDefaults['paymentAmount'] ?? '',
    'paymentDueDate' => $_REQUEST['paymentDueDate'] ?? $paymentSessionDefaults['paymentDueDate'] ?? date('Y-m-d'),
    'paymentRemittance' => $_REQUEST['paymentRemittance'] ?? $paymentSessionDefaults['paymentRemittance'] ?? '',
    'paymentPurposeCode' => $_REQUEST['paymentPurposeCode'] ?? $paymentSessionDefaults['paymentPurposeCode'] ?? 'OTHR',
    'paymentEndToEndId' => $_REQUEST['paymentEndToEndId'] ?? $paymentSessionDefaults['paymentEndToEndId'] ?? '',
    'paymentFromAddressStreetName' => $_REQUEST['paymentFromAddressStreetName'] ?? $paymentSessionDefaults['paymentFromAddressStreetName'] ?? '',
    'paymentFromAddressBuildingNumber' => $_REQUEST['paymentFromAddressBuildingNumber'] ?? $paymentSessionDefaults['paymentFromAddressBuildingNumber'] ?? '',
    'paymentFromAddressCity' => $_REQUEST['paymentFromAddressCity'] ?? $paymentSessionDefaults['paymentFromAddressCity'] ?? '',
    'paymentFromAddressPostCode' => $_REQUEST['paymentFromAddressPostCode'] ?? $paymentSessionDefaults['paymentFromAddressPostCode'] ?? '',
    'paymentFromAddressCountry' => $_REQUEST['paymentFromAddressCountry'] ?? ($paymentSessionDefaults['paymentFromAddressCountry'] ?? '') ?: ($form['country'] ?: 'NO'),
    'paymentFromTelephoneNumber' => $_REQUEST['paymentFromTelephoneNumber'] ?? $paymentSessionDefaults['paymentFromTelephoneNumber'] ?? '',
    'paymentToAddressStreetName' => $_REQUEST['paymentToAddressStreetName'] ?? $paymentSessionDefaults['paymentToAddressStreetName'] ?? '',
    'paymentToAddressBuildingNumber' => $_REQUEST['paymentToAddressBuildingNumber'] ?? $paymentSessionDefaults['paymentToAddressBuildingNumber'] ?? '',
    'paymentToAddressCity' => $_REQUEST['paymentToAddressCity'] ?? $paymentSessionDefaults['paymentToAddressCity'] ?? '',
    'paymentToAddressPostCode' => $_REQUEST['paymentToAddressPostCode'] ?? $paymentSessionDefaults['paymentToAddressPostCode'] ?? '',
    'paymentToAddressCountry' => $_REQUEST['paymentToAddressCountry'] ?? $paymentSessionDefaults['paymentToAddressCountry'] ?? '',
    'paymentToTelephoneNumber' => $_REQUEST['paymentToTelephoneNumber'] ?? $paymentSessionDefaults['paymentToTelephoneNumber'] ?? '',
    'paymentAdditionalInformationType' => $_REQUEST['paymentAdditionalInformationType'] ?? $paymentSessionDefaults['paymentAdditionalInformationType'] ?? '',
    'paymentAdditionalInformationValue' => $_REQUEST['paymentAdditionalInformationValue'] ?? $paymentSessionDefaults['paymentAdditionalInformationValue'] ?? '',
    'paymentRegulatoryReportingCode' => $_REQUEST['paymentRegulatoryReportingCode'] ?? $paymentSessionDefaults['paymentRegulatoryReportingCode'] ?? '',
    'paymentRegulatoryReportingInformation' => $_REQUEST['paymentRegulatoryReportingInformation'] ?? $paymentSessionDefaults['paymentRegulatoryReportingInformation'] ?? '',
];
$_SESSION['ztl_payment_form'] = $paymentForm;

$serverPsu = ZtlSettle::psuFromServer($_SERVER);
$psu = ZtlSettle::normalizePsu([
    'ipAddress' => $_REQUEST['psuIpAddress'] ?? $serverPsu['ipAddress'],
    'userAgent' => $_REQUEST['psuUserAgent'] ?? $serverPsu['userAgent'],
    'accept' => $_REQUEST['psuAccept'] ?? $serverPsu['accept'],
    'acceptLanguage' => $_REQUEST['psuAcceptLanguage'] ?? $serverPsu['acceptLanguage'],
]);
$_SESSION['ztl_psu'] = $psu;

$service->setFormValues($form);
$service->setPaymentFormValues($paymentForm);
$service->setPsu($psu);

$summary = $service->getSummary();
$debugInfo = $service->getDebugInfo();
$lastResult = null;
$resultDebug = null;
$message = null;

// Session state
// Consent and account data are set by ztl_landing — read them here, never recreate them.

$consent = $_SESSION['ztl_consent'] ?? null;
$accounts = $_SESSION['ztl_accounts'] ?? null;
$selectedFromAccountId = $_SESSION['ztl_selected_from_account'] ?? '';
$accountBalances = $_SESSION['ztl_account_balances'] ?? null;
$payment = $_SESSION['ztl_payment'] ?? null;
$initiatedPayments = $_SESSION['ztl_payments'] ?? [];
$paymentStatus = $_SESSION['ztl_payment_status'] ?? null;
$paymentApproval = $_SESSION['ztl_payment_approval'] ?? null;
$paymentApprovalStatus = $_SESSION['ztl_payment_approval_status'] ?? null;
$paymentCancellation = $_SESSION['ztl_payment_cancellation'] ?? null;
$bulkPayment = $_SESSION['ztl_bulk_payment'] ?? null;
$bulkPaymentStatus = $_SESSION['ztl_bulk_payment_status'] ?? null;
$regulatoryReportingCodes = $_SESSION['ztl_regulatory_codes'] ?? null;
$regulatoryReportingCountry = $_SESSION['ztl_regulatory_codes_country'] ?? '';
$paymentPdf = $_SESSION['ztl_payment_pdf'] ?? null;
$pendingPayments = $_SESSION['ztl_pending_payments'] ?? [];
$showPaymentGate = !empty($_SESSION['ztl_show_payment_gate']);

// PRG flash message: pick up any message stored before a redirect
if (isset($_SESSION['ztl_flash_message'])) {
    $message = $_SESSION['ztl_flash_message'];
    unset($_SESSION['ztl_flash_message']);
}

$service->setConsentId($consent['id'] ?? '');
$service->setAccounts($accounts);

$consentAuthorized = ztl_consent_is_valid($consent);

// SCA callback

if (isset($_GET['status']) && $consent && !empty($consent['id'])) {
    try {
        $approvalId = is_array($paymentApproval) ? trim((string) ($paymentApproval['id'] ?? '')) : '';
        if ($approvalId !== '') {
            $lastResult = $service->getPaymentApprovalStatus($approvalId);
            $paymentApprovalStatus = $lastResult['response'] ?? null;
            $_SESSION['ztl_payment_approval_status'] = $paymentApprovalStatus;

            if (is_array($paymentApproval)) {
                $paymentApproval['status'] = is_array($paymentApprovalStatus)
                    ? ($paymentApprovalStatus['status'] ?? ($paymentApproval['status'] ?? null))
                    : ($paymentApproval['status'] ?? null);
                $_SESSION['ztl_payment_approval'] = $paymentApproval;
            }

            $paymentId = ztl_extract_payment_id($payment);
            if ($paymentId !== '') {
                try {
                    $paymentStatusResult = $service->getPaymentStatus($paymentId);
                    $paymentStatus = $paymentStatusResult['response'] ?? null;
                    $_SESSION['ztl_payment_status'] = $paymentStatus;
                    if (is_array($payment)) {
                        $payment['paymentStatus'] = $paymentStatus;
                        $_SESSION['ztl_payment'] = $payment;
                    }
                } catch (Throwable) {
                }
            }
            $message = ['Payment approval status refreshed: ' . (is_array($paymentApprovalStatus) ? ($paymentApprovalStatus['status'] ?? 'unknown') : 'unknown'), 'success'];
        } else {
            $lastResult = $service->getConsentStatus($consent['id']);
            $consentStatusResponse = $lastResult['response'] ?? null;
            if ($consentStatusResponse) {
                $consent['status'] = $consentStatusResponse['status'] ?? ($consent['status'] ?? null);
                if (isset($consentStatusResponse['validUntil'])) {
                    $consent['validUntil'] = $consentStatusResponse['validUntil'];
                }
                $_SESSION['ztl_consent'] = $consent;
            }
            $consentAuthorized = ztl_consent_is_valid($consent);
            $message = ['Consent status refreshed: ' . ($consent['status'] ?? 'unknown'), 'success'];
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
        if ($e instanceof ZtlApiException && $e->getZtlRequestId()) {
            $error .= ' ZTL request id: ' . $e->getZtlRequestId();
        }
        $message = ['Could not refresh callback status: ' . $error, 'error'];
    }
}

// POST handlers

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    try {

        // Retry payment initiation once on 403 by activating the company subscription.
        $initiateWithRetry = function (callable $fn, string $errorMessage) use ($service, $form): array {
            try {
                return $fn();
            } catch (ZtlApiException $e) {
                if ($e->getStatusCode() !== 403) {
                    throw $e;
                }
                try {
                    $service->activateCompanySubscription($form['country'], $form['organizationNumber']);
                    return $fn();
                } catch (Exception) {
                    throw new ZtlApiException($errorMessage, 403, $e->getZtlRequestId(), $e->getResponseBody());
                }
            }
        };

        if (isset($_POST['proceed_to_payment'])) {
            $pendingPayments = array_values(array_unique($_POST['payment_ids'] ?? []));
            $_SESSION['ztl_pending_payments'] = $pendingPayments;
            $_SESSION['ztl_show_payment_gate'] = true;
            $showPaymentGate = true;

            $_SESSION['ztl_flash_message'] = ['Payments selected. Review and confirm the settlement below.', 'success'];
            header('Location: ' . ZTL_SETTLE_URL);
            exit;

        } elseif (isset($_POST['initiate_payment'])) {
            $uniqueRefs = array_values(array_unique($pendingPayments));
            if (empty($uniqueRefs)) {
                throw new Exception('No payments selected.');
            }

            // Build a reference → API item map for per-payment data
            $refItemMap = [];
            foreach (ztl_fetch_outstanding_payments() as $item) {
                $ref = $item['payment_information']['reference'] ?? '';
                if ($ref !== '') {
                    $refItemMap[$ref] = $item;
                }
            }

            $dueDateOverride = trim((string) ($_POST['paymentDueDate'] ?? ''));
            $allInitiatedPayments = [];
            $allPaymentIds = [];

            foreach ($uniqueRefs as $ref) {
                $item = $refItemMap[$ref] ?? null;
                if ($item === null) {
                    continue;
                }
                $info = $item['payment_information'] ?? [];
                $details = (array) ($item['details'] ?? []);
                $itemTotal = array_sum(array_map(fn($d) => (float) ($d['amount'] ?? 0), $details));
                $currency = strtoupper(trim((string) ($info['currency_code'] ?? 'NOK')));
                $toIban = trim((string) ($info['iban'] ?? ''));
                $toBban = trim((string) ($info['bank_account_number'] ?? ''));
                $dueDate = $dueDateOverride ?: ($item['paymentDate'] ?? date('Y-m-d'));

                $service->setPaymentFormValues(array_merge($paymentForm, [
                    'paymentFromAccountId' => $selectedFromAccountId,
                    'paymentToName' => $details[0]['brandname'] ?? ('Payment ' . $ref),
                    'paymentToBban' => $toBban,
                    'paymentToIban' => $toIban,
                    'paymentCurrency' => $currency,
                    'paymentAmount' => (string) $itemTotal,
                    'paymentDueDate' => $dueDate,
                    'paymentRemittance' => $ref,
                    'paymentPurposeCode' => 'OTHR',
                    'paymentEndToEndId' => '',
                ]));

                $lastResult = $initiateWithRetry(
                    fn() => $service->startPayment(),
                    'Payments are not allowed for company ' . $form['organizationNumber'] . '. '
                    . 'This company was likely onboarded using "Register Company (AIS only)" which only enables Account Information Services. '
                    . 'To enable payments, complete full onboarding via the setup page.'
                );
                $paymentBody = $lastResult['response'] ?? null;
                if ($paymentBody !== null) {
                    $allInitiatedPayments[] = $paymentBody;
                }
                $pid = ztl_extract_payment_id($paymentBody ?? []);
                if ($pid !== '') {
                    $allPaymentIds[] = $pid;
                }
            }

            $initiatedPayments = $allInitiatedPayments;
            $_SESSION['ztl_payments'] = $allInitiatedPayments;
            $payment = $allInitiatedPayments[0] ?? null;
            $_SESSION['ztl_payment'] = $payment;
            $paymentStatus = is_array($payment) ? ($payment['paymentStatus'] ?? null) : null;
            $_SESSION['ztl_payment_status'] = $paymentStatus;
            $_SESSION['ztl_payment_approval'] = null;
            $_SESSION['ztl_payment_approval_status'] = null;
            $_SESSION['ztl_payment_cancellation'] = null;
            $paymentApproval = null;
            $paymentApprovalStatus = null;
            $paymentCancellation = null;

            if (!empty($allPaymentIds)) {
                $approvalResult = $service->approvePayment($allPaymentIds);
                $paymentApproval = $approvalResult['response'] ?? null;
                $_SESSION['ztl_payment_approval'] = $paymentApproval;
                $_SESSION['ztl_payment_approval_status'] = null;
                $lastResult = $approvalResult;

                $scaUrl = ztl_extract_sca_url($paymentApproval);
                if ($scaUrl !== '') {
                    header('Location: ' . $scaUrl);
                    exit;
                }
                $message = [$approvalResult['message'], 'success'];
            } else {
                $message = ['Payments initiated but no payment IDs were returned.', 'warning'];
            }

        } elseif (isset($_POST['initiate_cross_border_payment'])) {
            $uniqueRefs = array_values(array_unique($pendingPayments));
            if (empty($uniqueRefs)) {
                throw new Exception('No payments selected.');
            }

            $refItemMap = [];
            foreach (ztl_fetch_outstanding_payments() as $item) {
                $ref = $item['payment_information']['reference'] ?? '';
                if ($ref !== '') {
                    $refItemMap[$ref] = $item;
                }
            }

            $dueDateOverride = trim((string) ($_POST['paymentDueDate'] ?? ''));
            $allInitiatedPayments = [];
            $allPaymentIds = [];

            foreach ($uniqueRefs as $ref) {
                $item = $refItemMap[$ref] ?? null;
                if ($item === null) {
                    continue;
                }
                $info = $item['payment_information'] ?? [];
                $details = (array) ($item['details'] ?? []);
                $itemTotal = array_sum(array_map(fn($d) => (float) ($d['amount'] ?? 0), $details));
                $currency = strtoupper(trim((string) ($info['currency_code'] ?? 'NOK')));
                $toIban = trim((string) ($info['iban'] ?? ''));
                $recipientCountry = strlen($toIban) >= 2 ? strtoupper(substr(preg_replace('/\s+/', '', $toIban), 0, 2)) : '';
                $dueDate = $dueDateOverride ?: ($item['paymentDate'] ?? date('Y-m-d'));

                $senderAcct = ztl_find_account($accounts, $selectedFromAccountId);
                $senderBic = strtoupper($senderAcct['bic'] ?? '') ?: strtoupper(trim($form['bic']));
                $senderBban = trim((string) ($senderAcct['bban'] ?? $selectedFromAccountId));

                $service->setPaymentFormValues(array_merge($paymentForm, [
                    'paymentFromAccountId' => $selectedFromAccountId,
                    'paymentFromAccountBic' => $_POST['paymentFromAccountBic'] ?? $senderBic,
                    'paymentFromAddressStreetName' => $_POST['paymentFromAddressStreetName'] ?? '',
                    'paymentFromAddressBuildingNumber' => $_POST['paymentFromAddressBuildingNumber'] ?? '',
                    'paymentFromAddressCity' => $_POST['paymentFromAddressCity'] ?? '',
                    'paymentFromAddressPostCode' => $_POST['paymentFromAddressPostCode'] ?? '',
                    'paymentToName' => $details[0]['brandname'] ?? ('Payment ' . $ref),
                    'paymentToIban' => $toIban,
                    'paymentToBban' => '',
                    'paymentToBic' => ztl_iban_to_bic($toIban),
                    'paymentToCountry' => $recipientCountry,
                    'paymentCurrency' => $currency,
                    'paymentAmount' => (string) $itemTotal,
                    'paymentDueDate' => $dueDate,
                    'paymentRemittance' => $ref,
                    'paymentPurposeCode' => 'OTHR',
                    'paymentEndToEndId' => '',
                ]));

                $lastResult = $initiateWithRetry(
                    fn() => $service->startCrossBorderPayment(),
                    'Payments are not allowed for company ' . $form['organizationNumber'] . '. '
                    . 'Complete full onboarding via the setup page to enable payments.'
                );
                $paymentBody = $lastResult['response'] ?? null;
                if ($paymentBody !== null) {
                    $allInitiatedPayments[] = $paymentBody;
                }
                $pid = ztl_extract_payment_id($paymentBody ?? []);
                if ($pid !== '') {
                    $allPaymentIds[] = $pid;
                }
            }

            $initiatedPayments = $allInitiatedPayments;
            $_SESSION['ztl_payments'] = $allInitiatedPayments;
            $payment = $allInitiatedPayments[0] ?? null;
            $_SESSION['ztl_payment'] = $payment;
            $paymentStatus = is_array($payment) ? ($payment['paymentStatus'] ?? null) : null;
            $_SESSION['ztl_payment_status'] = $paymentStatus;
            $_SESSION['ztl_payment_approval'] = null;
            $_SESSION['ztl_payment_approval_status'] = null;
            $_SESSION['ztl_payment_cancellation'] = null;
            $paymentApproval = null;
            $paymentApprovalStatus = null;
            $paymentCancellation = null;

            if (!empty($allPaymentIds)) {
                $approvalResult = $service->approvePayment($allPaymentIds);
                $paymentApproval = $approvalResult['response'] ?? null;
                $_SESSION['ztl_payment_approval'] = $paymentApproval;
                $_SESSION['ztl_payment_approval_status'] = null;
                $lastResult = $approvalResult;

                $scaUrl = ztl_extract_sca_url($paymentApproval);
                if ($scaUrl !== '') {
                    header('Location: ' . $scaUrl);
                    exit;
                }
                $message = [$approvalResult['message'], 'success'];
            } else {
                $message = ['Cross-border payments initiated but no payment IDs were returned.', 'warning'];
            }

        } elseif (isset($_POST['approve_payment'])) {
            $paymentId = trim((string) ($_POST['paymentId'] ?? ''));
            if ($paymentId !== '') {
                $approveIds = [$paymentId];
            } else {
                $approveIds = array_values(array_filter(array_map(
                    fn($p) => ztl_extract_payment_id(is_array($p) ? $p : []),
                    $initiatedPayments ?: array_filter([$payment])
                )));
            }
            if (empty($approveIds)) {
                throw new Exception('Payment ID is required to approve a payment.');
            }
            $lastResult = $service->approvePayment($approveIds);
            $paymentApproval = $lastResult['response'] ?? null;
            $_SESSION['ztl_payment_approval'] = $paymentApproval;
            $_SESSION['ztl_payment_approval_status'] = null;
            $paymentApprovalStatus = null;
            $scaUrl = ztl_extract_sca_url($paymentApproval);
            if ($scaUrl !== '') {
                header('Location: ' . $scaUrl);
                exit;
            }
            $message = [$lastResult['message'], 'success'];

        } elseif (isset($_POST['refresh_payment_status'])) {
            $paymentId = $_POST['paymentId'] ?? ($payment['paymentId'] ?? '');
            if (trim((string) $paymentId) === '') {
                throw new Exception('Payment ID is required to refresh payment status.');
            }
            $lastResult = $service->getPaymentStatus($paymentId);
            $paymentStatus = $lastResult['response'] ?? null;
            $_SESSION['ztl_payment_status'] = $paymentStatus;
            if (is_array($payment)) {
                $payment['paymentStatus'] = $paymentStatus;
                $_SESSION['ztl_payment'] = $payment;
            }
            $message = [$lastResult['message'], 'success'];

        } elseif (isset($_POST['refresh_payment_approval_status'])) {
            $approvalId = $_POST['approvalId'] ?? ($paymentApproval['id'] ?? '');
            if (trim((string) $approvalId) === '') {
                throw new Exception('Approval ID is required to refresh payment approval status.');
            }
            $lastResult = $service->getPaymentApprovalStatus($approvalId);
            $paymentApprovalStatus = $lastResult['response'] ?? null;
            $_SESSION['ztl_payment_approval_status'] = $paymentApprovalStatus;
            if (is_array($paymentApproval)) {
                $paymentApproval['status'] = $paymentApprovalStatus['status'] ?? ($paymentApproval['status'] ?? null);
                $_SESSION['ztl_payment_approval'] = $paymentApproval;
            }
            $message = [$lastResult['message'], 'success'];

        } elseif (isset($_POST['cancel_payment'])) {
            $paymentId = $_POST['paymentId'] ?? ($payment['paymentId'] ?? '');
            if (trim((string) $paymentId) === '') {
                throw new Exception('Payment ID is required to cancel a payment.');
            }
            $lastResult = $service->cancelPayment($paymentId);
            $paymentCancellation = $lastResult['response'] ?? null;
            $_SESSION['ztl_payment_cancellation'] = $paymentCancellation;
            $message = [$lastResult['message'], 'success'];

        } elseif (isset($_POST['initiate_bulk_payment'])) {
            $bulkPaymentType = $_POST['bulkPaymentType'] ?? ($paymentMode === 'cross_border' ? 'cross-border' : 'domestic');
            $bulkPaymentItem = $service->buildBulkPaymentItem($bulkPaymentType);
            $lastResult = $service->startBulkPayment([$bulkPaymentItem]);
            $bulkPayment = $lastResult['response'] ?? null;
            $_SESSION['ztl_bulk_payment'] = $bulkPayment;
            $_SESSION['ztl_bulk_payment_status'] = null;
            $bulkPaymentStatus = null;
            $message = [$lastResult['message'], 'success'];

        } elseif (isset($_POST['refresh_bulk_payment_status'])) {
            $bulkId = $_POST['bulkId'] ?? ($_SESSION['ztl_bulk_payment']['id'] ?? '');
            if (trim((string) $bulkId) === '') {
                throw new Exception('Bulk ID is required to refresh bulk payment status.');
            }
            $lastResult = $service->getBulkPaymentStatus($bulkId);
            $bulkPaymentStatus = $lastResult['response'] ?? null;
            $_SESSION['ztl_bulk_payment_status'] = $bulkPaymentStatus;
            $message = [$lastResult['message'], 'success'];

        } elseif (isset($_POST['fetch_regulatory_codes'])) {
            $regCountryCode = strtoupper(trim((string) ($_POST['regCountryCode'] ?? ($form['country'] ?? 'NO'))));
            $lastResult = $service->getRegulatoryReportingCodes($regCountryCode);
            $regulatoryReportingCodes = $lastResult['response'] ?? null;
            $_SESSION['ztl_regulatory_codes'] = $regulatoryReportingCodes;
            $_SESSION['ztl_regulatory_codes_country'] = $regCountryCode;
            $regulatoryReportingCountry = $regCountryCode;
            $message = [$lastResult['message'], 'success'];

        } elseif (isset($_POST['download_payment_pdf'])) {
            $pdfPaymentId = $_POST['pdfPaymentId'] ?? ($payment['paymentId'] ?? '');
            if (trim((string) $pdfPaymentId) === '') {
                throw new Exception('Payment ID is required to download progress PDF.');
            }
            $lastResult = $service->getPaymentProgressPdf($pdfPaymentId);
            $pdfData = $lastResult['pdfData'] ?? '';
            if (strlen($pdfData) > 0) {
                $_SESSION['ztl_payment_pdf'] = [
                    'paymentId' => $pdfPaymentId,
                    'pdfBase64' => base64_encode($pdfData),
                    'sizeBytes' => strlen($pdfData),
                    'contentType' => $lastResult['response']['contentType'] ?? 'application/pdf',
                ];
            }
            $paymentPdf = $_SESSION['ztl_payment_pdf'] ?? null;
            $message = [$lastResult['message'], 'success'];

        } elseif (isset($_POST['serve_payment_pdf'])) {
            $pdfSession = $_SESSION['ztl_payment_pdf'] ?? null;
            if ($pdfSession && !empty($pdfSession['pdfBase64'])) {
                $decoded = base64_decode($pdfSession['pdfBase64']);
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="payment_progress_' . ($pdfSession['paymentId'] ?? 'unknown') . '.pdf"');
                header('Content-Length: ' . strlen($decoded));
                echo $decoded;
                exit;
            }
            $message = ['No PDF data available. Download the PDF first.', 'warning'];

        } elseif (isset($_POST['reset_session'])) {
            foreach (array_keys($_SESSION) as $key) {
                if (str_starts_with($key, 'ztl_')) {
                    unset($_SESSION[$key]);
                }
            }
            $consent = null;
            $accounts = null;
            $accountBalances = null;
            $selectedFromAccountId = '';
            $payment = null;
            $initiatedPayments = [];
            $paymentStatus = null;
            $paymentApproval = null;
            $paymentApprovalStatus = null;
            $paymentCancellation = null;
            $bulkPayment = null;
            $bulkPaymentStatus = null;
            $pendingPayments = [];
            $showPaymentGate = false;
            $consentAuthorized = false;

            $form = [
                'country' => $serviceFormDefaults['country'] ?? 'NO',
                'organizationNumber' => $serviceFormDefaults['organizationNumber'] ?? '',
                'onboardingRedirectUrl' => ZTL_SETTLE_URL,
                'userId' => $serviceFormDefaults['userId'] ?? 'TX99999',
                'bic' => $serviceFormDefaults['bic'] ?? 'DNBANOKK',
                'bankBranch' => $serviceFormDefaults['bankBranch'] ?? '',
                'consentCallbackUrl' => ZTL_SETTLE_URL,
                'preferredScaMethod' => $serviceFormDefaults['preferredScaMethod'] ?? 'Redirect',
            ];
            $_SESSION['ztl_form'] = $form;
            $message = ['All ZTL session data has been reset. Return to the setup page to re-authorize.', 'success'];
        }

        $summary = $service->getSummary();
        $debugInfo = $service->getDebugInfo();

    } catch (Throwable $e) {
        $error = ztl_format_error($e);
        if ($e instanceof ZtlApiException && $e->getZtlRequestId()) {
            $error .= ' (ZTL request id: ' . $e->getZtlRequestId() . ')';
        }
        $message = [$error, 'error'];
    }
}

$resultDebug = $lastResult ? json_encode($lastResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;

$service->setFormValues($form);
$service->setPaymentFormValues($paymentForm);

// Outstanding payments

$apiData = ztl_fetch_outstanding_payments();
$paymentDates = [];
$pageCurrency = null;
$bankAccountNumber = '';

foreach ($apiData as $item) {
    $info = $item['payment_information'] ?? [];
    $details = (array) ($item['details'] ?? []);
    $dateStr = $item['paymentDate'] ?? null;

    if ($pageCurrency === null && !empty($info['currency_code'])) {
        $pageCurrency = strtoupper($info['currency_code']);
    }

    // Capture bank account number once (it's the same across all blocks)
    if ($bankAccountNumber === '' && !empty($info['bank_account_number'])) {
        $bankAccountNumber = $info['bank_account_number'];
    }

    $payments = [];
    foreach ($details as $d) {
        $payments[] = [
            'reference' => $info['reference'] ?? null,
            'brandname' => $d['brandname'] ?? '',
            'amount' => (float) ($d['amount'] ?? 0),
            'currency' => $info['currency_code'] ?? null,
            'dueDate' => $dateStr,
        ];
    }

    $ref = $info['reference'] ?? '';

    $paymentDates[] = [
        'date' => $dateStr,
        'label' => $ref ? 'Ref: ' . $ref : 'No Reference',
        'reference' => $ref,
        'payments' => $payments,
    ];
}

// Payment prefill (after selection and account is ready)

$paymentPrefill = null;

if ($showPaymentGate && $selectedFromAccountId !== '' && !$payment && !empty($pendingPayments)) {
    $uniqueRefs = array_unique($pendingPayments);
    $grandTotal = 0.0;
    $firstItem = null;

    foreach ($apiData as $item) {
        if (!in_array($item['payment_information']['reference'] ?? '', $uniqueRefs, true)) {
            continue;
        }
        $details = (array) ($item['details'] ?? []);
        $grandTotal += array_sum(array_map(fn($d) => (float) ($d['amount'] ?? 0), $details));
        $firstItem ??= $item;
    }

    if ($firstItem !== null) {
        $info = $firstItem['payment_information'] ?? [];
        $details = (array) ($firstItem['details'] ?? []);
        $ref = $info['reference'] ?? '';
        $toIban = $info['iban'] ?? '';
        $currency = strtoupper($info['currency_code'] ?? 'NOK');

        $senderAcct = ztl_find_account($accounts, $selectedFromAccountId);
        $senderCurrency = strtoupper($senderAcct['currency'] ?? '');
        $senderBic = strtoupper($senderAcct['bic'] ?? '') ?: strtoupper(trim($form['bic']));

        $isDomestic = $currency === 'SEK' && $senderCurrency === 'SEK';
        $paymentMode = $isDomestic ? 'domestic' : 'cross_border';
        $cleanIban = strtoupper(preg_replace('/\s+/', '', $toIban));
        $recipientCountry = strlen($cleanIban) >= 2 ? substr($cleanIban, 0, 2) : '';

        $paymentPrefill = [
            'paymentFromAccountId' => $selectedFromAccountId,
            'paymentToName' => $details[0]['brandname'] ?? ('Payment ' . $ref),
            'paymentToBban' => $isDomestic ? ($info['bank_account_number'] ?? '') : '',
            'paymentCurrency' => $currency,
            'paymentAmount' => (string) $grandTotal,
            'paymentDueDate' => $firstItem['paymentDate'] ?? date('Y-m-d'),
            'paymentRemittance' => $ref,
            'paymentPurposeCode' => 'OTHR',
            'paymentPreferredScaMethod' => $form['preferredScaMethod'],
            'paymentEndToEndId' => '',
            'paymentToIban' => $isDomestic ? '' : $toIban,
            'paymentToBic' => $isDomestic ? '' : ztl_iban_to_bic($toIban),
            'paymentToCountry' => $isDomestic ? '' : $recipientCountry,
            'paymentToClearingCode' => '',
            'paymentFromAccountBic' => $isDomestic ? '' : $senderBic,
            'paymentFromAddressStreetName' => '',
            'paymentFromAddressBuildingNumber' => '',
            'paymentFromAddressCity' => '',
            'paymentFromAddressPostCode' => '',
        ];
    }
}

// Derived display state

$consentScaUrl = ztl_extract_sca_url($consent);
$paymentApprovalScaUrl = ztl_extract_sca_url($paymentApproval);
$paymentApprovalStatusScaUrl = ztl_extract_sca_url($paymentApprovalStatus);
$showPaymentApprovalSca = $paymentApprovalScaUrl !== ''
    && is_array($paymentApproval)
    && !ztl_is_terminal_approval_status($paymentApproval['status'] ?? '');
$showPaymentApprovalStatusSca = $paymentApprovalStatusScaUrl !== ''
    && is_array($paymentApprovalStatus)
    && !ztl_is_terminal_approval_status($paymentApprovalStatus['status'] ?? '');

[$messageText, $messageType] = is_array($message)
    ? [(string) ($message[0] ?? ''), (string) ($message[1] ?? '')]
    : ['', ''];

// No valid consent on a fresh page load → redirect straight to setup.
if (!$consentAuthorized && ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . ZTL_LANDING_URL);
    exit;
}

if (isset($smarty) && is_object($smarty)) {
    $smarty->assign([
        'messageText' => $messageText,
        'messageType' => $messageType,
        'summary' => $summary,
        'debugInfo' => $debugInfo,
        'result' => $lastResult,
        'resultDebug' => $resultDebug,
        'form' => $service->getFormValues(),
        'psu' => $psu,
        'landing_url' => ZTL_LANDING_URL,
        'consent' => $consent,
        'consentAuthorized' => $consentAuthorized,
        'consentScaUrl' => $consentScaUrl,
        'accounts' => $accounts,
        'accountBalances' => $accountBalances,
        'selectedFromAccountId' => $selectedFromAccountId,
        'payment_dates' => $paymentDates,
        'currency' => $pageCurrency ?? 'NOK',
        'locale' => 'nb-NO',
        'bankAccountNumber' => $bankAccountNumber,
        'pendingPayments' => $pendingPayments,
        'showPaymentGate' => $showPaymentGate,
        'paymentPrefill' => $paymentPrefill,
        'paymentMode' => $paymentMode,
        'payment' => $payment,
        'initiatedPayments' => $initiatedPayments,
        'paymentStatus' => $paymentStatus,
        'paymentApproval' => $paymentApproval,
        'paymentApprovalStatus' => $paymentApprovalStatus,
        'paymentApprovalScaUrl' => $paymentApprovalScaUrl,
        'paymentApprovalStatusScaUrl' => $paymentApprovalStatusScaUrl,
        'showPaymentApprovalSca' => $showPaymentApprovalSca,
        'showPaymentApprovalStatusSca' => $showPaymentApprovalStatusSca,
        'paymentCancellation' => $paymentCancellation,
        'bulkPayment' => $bulkPayment,
        'bulkPaymentStatus' => $bulkPaymentStatus,
        'regulatoryReportingCodes' => $regulatoryReportingCodes,
        'regulatoryReportingCountry' => $regulatoryReportingCountry,
        'paymentPdf' => $paymentPdf,
    ]);

    $templatePath = defined('TPL_RETAILER_DIR')
        ? rtrim(TPL_RETAILER_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'ztl_settle.tpl'
        : __DIR__ . DIRECTORY_SEPARATOR . 'ztl_settle.tpl';
    $smarty->display($templatePath);
    return;
}
