<?php

trait ZtlPaymentsModuleTrait
{
    public function startPayment()
    {
        $this->requireValue($this->consentId, 'Consent ID is required to initiate a payment.');
        $this->requireValue($this->paymentFromAccountId, 'From account is required to initiate a payment.');
        $this->requireValue($this->paymentToName, 'Recipient name is required to initiate a payment.');
        $this->requireValue($this->paymentAmount, 'Payment amount is required to initiate a payment.');
        $this->requireValue($this->paymentCurrency, 'Payment currency is required to initiate a payment.');
        $this->requireValue($this->paymentDueDate, 'Payment due date is required to initiate a payment.');

        if ($this->paymentToBban === '' && $this->paymentToIban === '') {
            throw new Exception('Destination BBAN or IBAN is required to initiate a payment.');
        }

        $result = $this->request(
            'POST',
            $this->paymentsEndpoint,
            $this->buildPaymentInitiationPayload(),
            array_merge(['consent-id: ' . $this->consentId], $this->psuHeaders($this->psu)),
            $this->apiBaseUrl
        );

        $this->recordPaymentInitiation($result, $this->getFormValues());
        $body = $result['body'] ?? null;
        $paymentStatus = is_array($body) ? ($body['paymentStatus']['status'] ?? ($body['status'] ?? 'unknown')) : 'unknown';

        $this->lastResult = [
            'ok' => true,
            'type' => 'payment',
            'message' => 'Payment was initiated. Current status: ' . $paymentStatus . '.',
            'response' => $body,
            'ztlRequestId' => $result['ztlRequestId'] ?? null,
        ];
        return $this->lastResult;
    }

    public function startCrossBorderPayment()
    {
        $this->requireValue($this->consentId, 'Consent ID is required to initiate a cross-border payment.');
        $this->requireValue($this->paymentFromAccountId, 'From account is required to initiate a cross-border payment.');
        $this->requireValue($this->paymentToName, 'Recipient name is required to initiate a cross-border payment.');
        $this->requireValue($this->paymentAmount, 'Payment amount is required to initiate a cross-border payment.');
        $this->requireValue($this->paymentCurrency, 'Payment currency is required to initiate a cross-border payment.');
        $this->requireValue($this->paymentDueDate, 'Payment due date is required to initiate a cross-border payment.');
        $this->requireValue($this->paymentRemittance, 'Remittance message is required to initiate a cross-border payment.');
        $this->requireValue($this->paymentToBic, 'Recipient BIC is required to initiate a cross-border payment.');

        if ($this->paymentToBban === '' && $this->paymentToIban === '') {
            throw new Exception('Destination BBAN or IBAN is required to initiate a cross-border payment.');
        }

        $this->requireValue($this->paymentFromAddressStreetName, 'From address street name is required for cross-border payment.');
        $this->requireValue($this->paymentFromAddressBuildingNumber, 'From address building number is required for cross-border payment.');
        $this->requireValue($this->paymentFromAddressCity, 'From address city is required for cross-border payment.');
        $this->requireValue($this->paymentFromAddressPostCode, 'From address post code is required for cross-border payment.');
        $this->requireValue($this->paymentFromAddressCountry, 'From address country is required for cross-border payment.');
        $this->requireValue($this->paymentToAddressStreetName, 'Recipient address street name is required for cross-border payment.');
        $this->requireValue($this->paymentToAddressBuildingNumber, 'Recipient address building number is required for cross-border payment.');
        $this->requireValue($this->paymentToAddressCity, 'Recipient address city is required for cross-border payment.');
        $this->requireValue($this->paymentToAddressPostCode, 'Recipient address post code is required for cross-border payment.');
        $this->requireValue($this->paymentToAddressCountry, 'Recipient address country is required for cross-border payment.');

        $result = $this->request(
            'POST',
            $this->paymentsEndpoint . '/cross-border',
            $this->buildCrossBorderPaymentPayload(),
            array_merge(['consent-id: ' . $this->consentId], $this->psuHeaders($this->psu)),
            $this->apiBaseUrl
        );

        $this->recordPaymentInitiation($result, $this->getFormValues());
        $body = $result['body'] ?? null;
        $paymentStatus = is_array($body) ? ($body['paymentStatus']['status'] ?? ($body['status'] ?? 'unknown')) : 'unknown';

        $this->lastResult = [
            'ok' => true,
            'type' => 'cross_border_payment',
            'message' => 'Cross-border payment was initiated. Current status: ' . $paymentStatus . '.',
            'response' => $body,
            'ztlRequestId' => $result['ztlRequestId'] ?? null,
        ];
        return $this->lastResult;
    }

    public function approvePayment($paymentIds)
    {
        $this->requireValue($this->consentId, 'Consent ID is required to approve a payment.');
        if (!is_array($paymentIds)) {
            $paymentIds = [trim((string) $paymentIds)];
        }
        $paymentIds = array_values(array_filter(array_map(fn($id) => trim((string) $id), $paymentIds)));
        $this->requireValue($paymentIds, 'At least one payment ID is required to approve payments.');
        $this->requireValue($this->paymentCallbackUrl, 'Payment callback URL is required to approve a payment.');

        $result = $this->request(
            'POST',
            $this->paymentsEndpoint . '/approve',
            [
                'payments' => $paymentIds,
                'callbackUrl' => $this->paymentCallbackUrl,
                'preferredScaMethod' => $this->paymentPreferredScaMethod,
            ],
            array_merge(['consent-id: ' . $this->consentId], $this->psuHeaders($this->psu)),
            $this->apiBaseUrl
        );

        $this->recordPaymentApproval($result, $this->getFormValues());
        $body = $result['body'] ?? null;
        $count = count($paymentIds);
        $this->lastResult = [
            'ok' => true,
            'type' => 'payment_approval',
            'message' => 'Payment approval has been started for ' . $count . ' payment' . ($count !== 1 ? 's' : '') . '.',
            'response' => $body,
            'ztlRequestId' => $result['ztlRequestId'] ?? null,
        ];
        return $this->lastResult;
    }

    public function getPaymentStatus($paymentId)
    {
        $paymentId = trim((string) $paymentId);
        $this->requireValue($paymentId, 'Payment ID is required to check payment status.');

        $result = $this->request('GET', $this->paymentsEndpoint . '/' . rawurlencode($paymentId) . '/status', null, [], $this->apiBaseUrl);
        $body = $result['body'] ?? null;

        $this->lastResult = [
            'ok' => true,
            'type' => 'payment_status',
            'message' => 'Payment status refreshed. Current status: ' . ($body['status'] ?? 'unknown') . '.',
            'response' => $body,
            'ztlRequestId' => $result['ztlRequestId'] ?? null,
        ];
        return $this->lastResult;
    }

    public function getPaymentApprovalStatus($approvalId)
    {
        $this->requireValue($this->consentId, 'Consent ID is required to check approval status.');
        $approvalId = trim((string) $approvalId);
        $this->requireValue($approvalId, 'Approval ID is required to check approval status.');

        $result = $this->request('GET', $this->paymentsEndpoint . '/approve/' . rawurlencode($approvalId), null, array_merge(['consent-id: ' . $this->consentId], $this->psuHeaders($this->psu)), $this->apiBaseUrl);
        $body = $result['body'] ?? null;

        $this->lastResult = [
            'ok' => true,
            'type' => 'payment_approval_status',
            'message' => 'Payment approval status refreshed. Current status: ' . ($body['status'] ?? 'unknown') . '.',
            'response' => $body,
            'ztlRequestId' => $result['ztlRequestId'] ?? null,
        ];
        return $this->lastResult;
    }

    public function cancelPayment($paymentId)
    {
        $this->requireValue($this->consentId, 'Consent ID is required to cancel a payment.');
        $paymentId = trim((string) $paymentId);
        $this->requireValue($paymentId, 'Payment ID is required to cancel a payment.');
        $this->requireValue($this->paymentCallbackUrl, 'Payment callback URL is required to cancel a payment.');

        $result = $this->request(
            'POST',
            $this->paymentsEndpoint . '/cancel',
            [
                'paymentId' => $paymentId,
                'callbackUrl' => $this->paymentCallbackUrl,
                'preferredScaMethod' => $this->paymentPreferredScaMethod,
            ],
            array_merge(['consent-id: ' . $this->consentId], $this->psuHeaders($this->psu)),
            $this->apiBaseUrl
        );

        $this->recordPaymentCancellation($result, $this->getFormValues());
        $body = $result['body'] ?? null;
        $this->lastResult = [
            'ok' => true,
            'type' => 'payment_cancellation',
            'message' => 'Payment cancellation has been started.',
            'response' => $body,
            'ztlRequestId' => $result['ztlRequestId'] ?? null,
        ];
        return $this->lastResult;
    }

    /**
     * Initiate bulk payment(s) with started approval/signing.
     * Exclusively supported for banks that do not support single payments/approval (PAYMENT_BULK).
     * Currently supported by Danske Bank and Handelsbanken Sweden.
     *
     * @param array $payments Array of payment objects. Each must have a 'type' key ('domestic' or 'cross-border')
     *                        plus the standard payment payload fields (from, to, amount, dueDate, etc.).
     * @return array
     */
    public function startBulkPayment(array $payments)
    {
        $this->requireValue($this->consentId, 'Consent ID is required to initiate a bulk payment.');
        $this->requireValue($this->paymentCallbackUrl, 'Callback URL is required to initiate a bulk payment.');

        if (empty($payments)) {
            throw new Exception('At least one payment is required to initiate a bulk payment.');
        }

        $payload = [
            'payments' => $payments,
            'callbackUrl' => $this->paymentCallbackUrl,
        ];

        $result = $this->request(
            'POST',
            $this->paymentsEndpoint . '/bulk',
            $payload,
            array_merge(['consent-id: ' . $this->consentId], $this->psuHeaders($this->psu)),
            $this->apiBaseUrl
        );

        $this->recordBulkPaymentInitiation($result, $this->getFormValues());
        $body = $result['body'] ?? null;
        $bulkId = is_array($body) ? ($body['id'] ?? null) : null;
        $transactionCount = is_array($body) && isset($body['transactions']) ? count($body['transactions']) : 0;

        $this->lastResult = [
            'ok' => true,
            'type' => 'bulk_payment',
            'message' => 'Bulk payment initiated successfully. Bulk ID: ' . ($bulkId ?? 'unknown') . '. Transactions: ' . $transactionCount . '.',
            'response' => $body,
            'ztlRequestId' => $result['ztlRequestId'] ?? null,
        ];
        return $this->lastResult;
    }

    /**
     * Build a single bulk-payment item from the current form values.
     * Returns an array suitable for inclusion in the startBulkPayment payments array.
     *
     * @param string $type 'domestic' or 'cross-border'
     * @return array
     */
    public function buildBulkPaymentItem($type = 'domestic')
    {
        $type = strtolower(trim((string) $type));
        if (!in_array($type, ['domestic', 'cross-border'], true)) {
            $type = 'domestic';
        }

        if ($type === 'cross-border') {
            $payloadBase = $this->buildCrossBorderPaymentPayload();
        } else {
            $payloadBase = $this->buildPaymentInitiationPayload();
        }

        $payloadBase['type'] = ($type === 'cross-border') ? 'cross-border' : 'domestic';

        return $payloadBase;
    }

    /**
     * Fetch the status of a bulk payment along with individual payment IDs.
     *
     * @param string $bulkId The bulk ID received when creating the bulk payment.
     * @return array
     */
    public function getBulkPaymentStatus($bulkId)
    {
        $bulkId = trim((string) $bulkId);
        $this->requireValue($bulkId, 'Bulk ID is required to fetch bulk payment status.');

        $result = $this->request(
            'GET',
            $this->paymentsEndpoint . '/bulk/' . rawurlencode($bulkId),
            null,
            [],
            $this->apiBaseUrl
        );

        $body = $result['body'] ?? null;
        $bulkStatus = is_array($body) ? ($body['bulkStatus'] ?? 'unknown') : 'unknown';
        $transactionCount = is_array($body) && isset($body['transactions']) ? count($body['transactions']) : 0;

        $this->lastResult = [
            'ok' => true,
            'type' => 'bulk_payment_status',
            'message' => 'Bulk payment status: ' . $bulkStatus . '. Transactions: ' . $transactionCount . '.',
            'response' => $body,
            'ztlRequestId' => $result['ztlRequestId'] ?? null,
        ];
        return $this->lastResult;
    }

    /**
     * Retrieve regulatory reporting codes for a specific country.
     * Currently only supported for NO and SE.
     *
     * @param string $countryCode ISO 3166 country code (e.g. 'NO', 'SE')
     * @return array
     */
    public function getRegulatoryReportingCodes($countryCode)
    {
        $countryCode = strtoupper(trim((string) $countryCode));
        $this->requireValue($countryCode, 'Country code is required to fetch regulatory reporting codes.');

        $result = $this->request(
            'GET',
            $this->paymentsEndpoint . '/regulatory-reporting/' . rawurlencode($countryCode),
            null,
            [],
            $this->apiBaseUrl
        );

        $body = $result['body'] ?? null;
        $codeCount = is_array($body) && isset($body['codes']) ? count($body['codes']) : 0;

        $this->lastResult = [
            'ok' => true,
            'type' => 'regulatory_reporting_codes',
            'message' => 'Regulatory reporting codes for ' . $countryCode . ': ' . $codeCount . ' code(s) found.',
            'response' => $body,
            'ztlRequestId' => $result['ztlRequestId'] ?? null,
        ];
        return $this->lastResult;
    }

    /**
     * Download a PDF report detailing the current progress of a cross-border payment.
     * Only available for cross-border payments with exchange in InProgress or Completed status.
     *
     * @param string $paymentId The payment ID
     * @return array Contains 'pdfData' (raw binary), 'pdfBase64', and 'pdfFilename'
     */
    public function getPaymentProgressPdf($paymentId)
    {
        $paymentId = trim((string) $paymentId);
        $this->requireValue($paymentId, 'Payment ID is required to download payment progress PDF.');

        $result = $this->requestRaw(
            'GET',
            $this->paymentsEndpoint . '/' . rawurlencode($paymentId) . '/progress-pdf',
            null,
            ['Accept: application/pdf'],
            $this->apiBaseUrl
        );

        $rawBody = $result['rawBody'] ?? '';
        $contentType = $result['contentType'] ?? '';

        $this->lastResult = [
            'ok' => true,
            'type' => 'payment_progress_pdf',
            'message' => 'Payment progress PDF downloaded successfully (' . strlen($rawBody) . ' bytes).',
            'response' => [
                'paymentId' => $paymentId,
                'contentType' => $contentType,
                'sizeBytes' => strlen($rawBody),
                'pdfBase64' => base64_encode($rawBody),
            ],
            'pdfData' => $rawBody,
            'ztlRequestId' => $result['ztlRequestId'] ?? null,
        ];
        return $this->lastResult;
    }
}
