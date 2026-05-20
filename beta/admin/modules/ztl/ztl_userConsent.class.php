<?php

trait ZtlUserConsentModuleTrait
{
    public function createConsent()
    {
        $this->requireValue($this->userId, 'Bank user ID is required to create consent.');
        $this->requireValue($this->bic, 'Bank BIC is required to create consent.');
        $this->requireValue($this->consentCallbackUrl, 'Consent callback URL is required to create consent.');
        $this->requireValue($this->organizationNumber, 'Organization number is required to create consent.');
        $this->requireValue($this->psu['ipAddress'] ?? '', 'PSU IP address is required to create consent.');
        $this->requireValue($this->psu['userAgent'] ?? '', 'PSU user agent is required to create consent.');

        $result = $this->sendCreateConsentRequest([
            'userId' => $this->userId,
            'bic' => $this->bic,
            'bankBranch' => $this->bankBranch,
            'callbackUrl' => $this->consentCallbackUrl,
            'organizationNumber' => $this->organizationNumber,
            'preferredScaMethod' => $this->preferredScaMethod,
        ], $this->psu);

        $this->recordConsent($result, $this->getFormValues());
        $this->lastResult = [
            'ok' => true,
            'type' => 'consent',
            'message' => 'Consent was created. Send the user to the SCA URL to approve it.',
            'response' => $result['body'] ?? null,
            'ztlRequestId' => $result['ztlRequestId'] ?? null,
        ];
        return $this->lastResult;
    }

    public function getConsentStatus($consentId)
    {
        $consentId = trim((string) $consentId);
        $this->requireValue($consentId, 'Consent ID is required to check consent status.');

        $headers = array_merge(
            ['consent-id: ' . $consentId],
            $this->psuHeaders($this->psu)
        );

        $result = $this->request('GET', $this->consentEndpoint, null, $headers, $this->apiBaseUrl);

        $body = $result['body'] ?? null;
        $this->lastResult = [
            'ok' => true,
            'type' => 'consent_status',
            'message' => 'Consent status refreshed. Current status: ' . ($body['status'] ?? 'unknown'),
            'response' => $body,
            'ztlRequestId' => $result['ztlRequestId'] ?? null,
        ];
        return $this->lastResult;
    }
}
