<?php

trait ZtlAccountsModuleTrait
{
    public function getAccounts($consentId)
    {
        $consentId = trim((string) $consentId);
        $this->requireValue($consentId, 'Consent ID is required to fetch accounts.');

        $headers = array_merge(
            ['consent-id: ' . $consentId],
            $this->psuHeaders($this->psu)
        );

        $result = $this->request('GET', $this->accountsEndpoint, null, $headers, $this->apiBaseUrl);

        $body = $result['body'] ?? null;
        $this->lastResult = [
            'ok' => true,
            'type' => 'accounts',
            'message' => 'Accounts fetched successfully. Found ' . count($body['accounts'] ?? []) . ' account(s).',
            'response' => $body,
            'ztlRequestId' => $result['ztlRequestId'] ?? null,
        ];
        return $this->lastResult;
    }

    public function getAccountBalance($consentId, $accountId)
    {
        $consentId = trim((string) $consentId);
        $accountId = trim((string) $accountId);
        $this->requireValue($consentId, 'Consent ID is required to fetch account balance.');
        $this->requireValue($accountId, 'Account ID is required to fetch account balance.');

        $headers = array_merge(
            ['consent-id: ' . $consentId],
            $this->psuHeaders($this->psu)
        );

        $endpoint = rtrim($this->accountsEndpoint, '/') . '/' . rawurlencode($accountId) . '/balance';
        $result = $this->request('GET', $endpoint, null, $headers, $this->apiBaseUrl);

        $body = $result['body'] ?? null;
        $this->lastResult = [
            'ok' => true,
            'type' => 'balance',
            'message' => 'Balance fetched for account ' . $accountId . '.',
            'response' => $body,
            'ztlRequestId' => $result['ztlRequestId'] ?? null,
        ];
        return $this->lastResult;
    }

    public function getAccountEntries($consentId, $accountId, $fromDate, $toDate)
    {
        $consentId = trim((string) $consentId);
        $accountId = trim((string) $accountId);
        $fromDate = trim((string) $fromDate);
        $toDate = trim((string) $toDate);
        $this->requireValue($consentId, 'Consent ID is required to fetch account entries.');
        $this->requireValue($accountId, 'Account ID is required to fetch account entries.');
        $this->requireValue($fromDate, 'From date is required to fetch account entries.');
        $this->requireValue($toDate, 'To date is required to fetch account entries.');

        $headers = array_merge(
            ['consent-id: ' . $consentId],
            $this->psuHeaders($this->psu)
        );

        $endpoint = rtrim($this->accountsEndpoint, '/') . '/' . rawurlencode($accountId)
            . '/entries?fromDate=' . rawurlencode($fromDate) . '&toDate=' . rawurlencode($toDate);
        $result = $this->request('GET', $endpoint, null, $headers, $this->apiBaseUrl);

        $body = $result['body'] ?? null;
        $this->lastResult = [
            'ok' => true,
            'type' => 'entries',
            'message' => 'Entries fetched for account ' . $accountId . '. Found ' . count($body['accountEntries'] ?? []) . ' entries.',
            'response' => $body,
            'ztlRequestId' => $result['ztlRequestId'] ?? null,
        ];
        return $this->lastResult;
    }

    public function getAccountEntryDetails($consentId, $accountId, $transactionId)
    {
        $consentId = trim((string) $consentId);
        $accountId = trim((string) $accountId);
        $transactionId = trim((string) $transactionId);
        $this->requireValue($consentId, 'Consent ID is required to fetch entry details.');
        $this->requireValue($accountId, 'Account ID is required to fetch entry details.');
        $this->requireValue($transactionId, 'Transaction ID is required to fetch entry details.');

        $headers = array_merge(
            ['consent-id: ' . $consentId],
            $this->psuHeaders($this->psu)
        );

        $endpoint = rtrim($this->accountsEndpoint, '/') . '/' . rawurlencode($accountId)
            . '/entries/' . rawurlencode($transactionId) . '/details';
        $result = $this->request('GET', $endpoint, null, $headers, $this->apiBaseUrl);

        $body = $result['body'] ?? null;
        $this->lastResult = [
            'ok' => true,
            'type' => 'entry_details',
            'message' => 'Entry details fetched for transaction ' . $transactionId . '.',
            'response' => $body,
            'ztlRequestId' => $result['ztlRequestId'] ?? null,
        ];
        return $this->lastResult;
    }
}
