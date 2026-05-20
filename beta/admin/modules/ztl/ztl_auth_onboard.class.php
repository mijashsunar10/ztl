<?php

trait ZtlAuthOnboardModuleTrait
{
    public function startOnboarding()
    {
        $this->requireValue($this->country, 'Country is required to start onboarding.');
        $this->requireValue($this->organizationNumber, 'Organization number is required to start onboarding.');

        $result = $this->sendStartOnboardingRequest($this->country, $this->organizationNumber, $this->onboardingRedirectUrl);
        $this->recordOnboarding($result, $this->getFormValues());
        $this->lastResult = [
            'ok' => true,
            'type' => 'onboarding',
            'message' => 'Onboarding was started. Send the user to the returned onboarding URL.',
            'response' => $result['body'] ?? null,
            'ztlRequestId' => $result['ztlRequestId'] ?? null,
        ];
        return $this->lastResult;
    }

    public function getOnboardingStatus($onboardingId)
    {
        $result = $this->sendGetOnboardingStatusRequest($onboardingId);
        $this->lastResult = [
            'ok' => true,
            'type' => 'onboarding_status',
            'message' => 'Onboarding status was refreshed.',
            'response' => $result['body'] ?? null,
            'ztlRequestId' => $result['ztlRequestId'] ?? null,
        ];
        return $this->lastResult;
    }

    public function registerOnboardingCompany()
    {
        $this->requireValue($this->country, 'Country is required to register company onboarding.');
        $this->requireValue($this->organizationNumber, 'Organization number is required to register company onboarding.');

        $result = $this->sendOnboardingRegistrationRequest($this->country, $this->organizationNumber);
        $this->recordOnboardingRegistration($result, $this->getFormValues());
        $this->lastResult = [
            'ok' => true,
            'type' => 'onboarding_registration',
            'message' => 'Onboarding registration (AIS only) was completed. WARNING: This does NOT enable payments. To enable payments, use "Start Onboarding" (full onboarding with KYC/signing) instead.',
            'response' => $result['body'] ?? ['statusCode' => ($result['status'] ?? null)],
            'ztlRequestId' => $result['ztlRequestId'] ?? null,
        ];
        return $this->lastResult;
    }

    public function getOnboardings()
    {
        // Try the standard v2 API path on the main API base URL
        $result = $this->request('GET', '/api/v2/onboarding', null, [], $this->apiBaseUrl);
        $onboardings = $result['body'] ?? [];
        if (!is_array($onboardings)) {
            $onboardings = [];
        }

        $this->lastResult = [
            'ok' => true,
            'type' => 'onboarding_list',
            'message' => 'Onboarding entries loaded. Found ' . count($onboardings) . ' entries.',
            'response' => $onboardings,
            'ztlRequestId' => $result['ztlRequestId'] ?? null,
        ];
        return $this->lastResult;
    }

    public function cancelOnboarding($onboardingId)
    {
        $onboardingId = trim((string) $onboardingId);
        $this->requireValue($onboardingId, 'Onboarding ID is required to cancel onboarding.');

        $result = $this->sendCancelOnboardingRequest($onboardingId);
        $this->recordOnboardingCancellation($onboardingId, $result, $this->getFormValues());
        $this->lastResult = [
            'ok' => true,
            'type' => 'onboarding_cancellation',
            'message' => 'Onboarding cancellation was sent.',
            'response' => $result['body'] ?? [
                'onboardingId' => $onboardingId,
                'statusCode' => ($result['status'] ?? null),
                'cancelled' => true,
            ],
            'ztlRequestId' => $result['ztlRequestId'] ?? null,
        ];
        return $this->lastResult;
    }

    public function getOnboardedCompanies()
    {
        $result = $this->request('GET', $this->companiesEndpoint, null, [], $this->apiBaseUrl);
        $companies = $result['body'] ?? [];
        if (!is_array($companies)) {
            $companies = [];
        }
        foreach (['companies', 'items', 'data', 'results'] as $listKey) {
            if (isset($companies[$listKey]) && is_array($companies[$listKey])) {
                $companies = $companies[$listKey];
                break;
            }
        }
        if (isset($companies['organizationNumber'])) {
            $companies = [$companies];
        }
        $companies = $this->enrichCompaniesWithKnownCountryCodes($companies);

        $this->lastResult = [
            'ok' => true,
            'type' => 'companies',
            'message' => 'Onboarded companies loaded. Found ' . count($companies) . ' company entries.',
            'response' => $companies,
            'ztlRequestId' => $result['ztlRequestId'] ?? null,
        ];
        return $this->lastResult;
    }

    public function cancelCompanySubscription($countryCode, $organizationNumber)
    {
        $countryCode = strtoupper(trim((string) $countryCode));
        $organizationNumber = trim((string) $organizationNumber);
        $this->requireValue($countryCode, 'Country code is required to cancel an onboarded company subscription.');
        $this->requireValue($organizationNumber, 'Organization number is required to cancel an onboarded company subscription.');

        $endpoint = rtrim($this->companiesEndpoint, '/') . '/subscription/cancel/' . rawurlencode($countryCode) . '/' . rawurlencode($organizationNumber);
        $result = $this->request('PATCH', $endpoint, null, [], $this->apiBaseUrl);

        $this->lastResult = [
            'ok' => true,
            'type' => 'company_cancellation',
            'message' => 'Company subscription cancellation was sent for ' . $organizationNumber . '.',
            'response' => $result['body'] ?? [
                'countryCode' => $countryCode,
                'organizationNumber' => $organizationNumber,
                'statusCode' => $result['status'] ?? null,
                'cancelled' => true,
            ],
            'ztlRequestId' => $result['ztlRequestId'] ?? null,
        ];
        return $this->lastResult;
    }

    public function activateCompanySubscription($countryCode, $organizationNumber)
    {
        $countryCode = strtoupper(trim((string) $countryCode));
        $organizationNumber = trim((string) $organizationNumber);
        $this->requireValue($countryCode, 'Country code is required to activate an onboarded company subscription.');
        $this->requireValue($organizationNumber, 'Organization number is required to activate an onboarded company subscription.');

        $endpoint = rtrim($this->companiesEndpoint, '/') . '/subscription/activate/' . rawurlencode($countryCode) . '/' . rawurlencode($organizationNumber);
        $result = $this->request('PATCH', $endpoint, null, [], $this->apiBaseUrl);

        $this->lastResult = [
            'ok' => true,
            'type' => 'company_activation',
            'message' => 'Company subscription activation was sent for ' . $organizationNumber . '.',
            'response' => $result['body'] ?? [
                'countryCode' => $countryCode,
                'organizationNumber' => $organizationNumber,
                'statusCode' => $result['status'] ?? null,
                'activated' => true,
            ],
            'ztlRequestId' => $result['ztlRequestId'] ?? null,
        ];
        return $this->lastResult;
    }
}
