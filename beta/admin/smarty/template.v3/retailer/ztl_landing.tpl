

<style>
    /*  Reset  */
    .ztl-land *,
    .ztl-land *::before,
    .ztl-land *::after { box-sizing: border-box; margin: 0; padding: 0; }

    /*  Theme tokens (inherits from sg-modern-theme if present)  */
    .ztl-land {
        --zl-bg:        var(--sg-surface-bg,  var(--sg-surface-2, #383D43));
        --zl-card:      var(--sg-surface-panel, var(--sg-surface, #272b30));
        --zl-card-alt:  var(--sg-surface-3,  #4B535E);
        --zl-hover:     color-mix(in srgb, var(--sg-text, #F2F4F6) 6%, transparent);
        --zl-text:      var(--sg-text-main,   var(--sg-text, #E4E6EB));
        --zl-text2:     var(--sg-text-muted,  #A0A4A8);
        --zl-text3:     var(--sg-text-muted,  #8E929B);
        --zl-bdr:       var(--sg-border-soft,  rgba(255,255,255,.12));
        --zl-bdr2:      var(--sg-border-hard,  var(--sg-border-strong, rgba(255,255,255,.18)));
        --zl-accent:    var(--sg-primary-action, #2169f3);
        --zl-accent-l:  color-mix(in srgb, var(--zl-accent) 14%, transparent);
        --zl-green:     var(--sg-success, #5DFF64);
        --zl-green-l:   color-mix(in srgb, var(--zl-green) 14%, transparent);
        --zl-amber:     var(--sg-warning, orange);
        --zl-amber-l:   color-mix(in srgb, var(--zl-amber) 14%, transparent);
        --zl-r:         8px;
        --zl-rl:        12px;
        --zl-font:      var(--sg-font, 'General Sans', system-ui, sans-serif);
        --zl-mono:      'SF Mono', 'Consolas', 'Menlo', monospace;
        --zl-shadow:    0 1px 3px rgba(0,0,0,.18), 0 1px 2px rgba(0,0,0,.12);
        --zl-shadow-lg: 0 4px 16px rgba(0,0,0,.28);
    }

    /* ── Layout ── */
    .ztl-land {
        font-family: var(--zl-font);
        background: var(--zl-bg);
        color: var(--zl-text);
        min-height: 100vh;
        padding: 2rem 1rem 4rem;
        -webkit-font-smoothing: antialiased;
    }
    .zl-wrap { max-width: 960px; margin: 0 auto; }

    /* ── Page header ── */
    .zl-hdr {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }
    .zl-hdr h1 {
        font-size: 22px;
        font-weight: 700;
        letter-spacing: -.3px;
        line-height: 1.3;
    }
    .zl-hdr-sub {
        font-size: 13px;
        color: var(--zl-text2);
        margin-top: 2px;
    }

    /* ── Gate card ── */
    .zl-gate {
        background: var(--zl-card);
        border: 1px solid var(--zl-bdr2);
        border-radius: var(--zl-rl);
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: var(--zl-shadow);
    }
    .zl-gate-title {
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 2px solid var(--zl-bdr);
    }
    .zl-gate .zl-info {
        border-left: 3px solid var(--zl-amber);
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 12px;
        color: var(--zl-text2);
        margin: 10px 0;
        background: var(--zl-amber-l);
    }
    .zl-gate .zl-info.success {
        border-left-color: var(--zl-green);
        background: var(--zl-green-l);
    }
    .zl-gate .zl-info.error {
        border-left-color: #ef4444;
        background: color-mix(in srgb, #ef4444 10%, transparent);
    }
    .zl-gate-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
        margin-bottom: 12px;
    }
    .zl-gate-grid label {
        display: block;
        font-weight: 600;
        font-size: 12px;
        margin-bottom: 4px;
    }
    .zl-gate-grid input,
    .zl-gate-grid select {
        width: 100%;
        height: 32px;
        font-size: 12px;
        padding: 0 8px;
        border: 1px solid var(--zl-bdr);
        border-radius: var(--zl-r);
        background: var(--zl-card);
        color: var(--zl-text);
        font-family: var(--zl-font);
    }
    .zl-gate-btns {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 12px;
    }
    .zl-gate-btn {
        background: var(--zl-accent);
        color: #fff;
        border: none;
        border-radius: var(--zl-r);
        padding: 7px 16px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        font-family: var(--zl-font);
        transition: opacity .15s;
        text-decoration: none;
        display: inline-block;
    }
    .zl-gate-btn:hover { opacity: .85; }
    .zl-gate-btn.secondary {
        background: var(--zl-card-alt);
        color: var(--zl-text);
        border: 1px solid var(--zl-bdr);
    }
    .zl-gate-btn.success { background: var(--zl-green); }
    .zl-gate-btn.warning { background: var(--zl-amber); color: #1a1a1a; }
    .zl-gate-btn.large {
        font-size: 14px;
        padding: 10px 28px;
    }
    .zl-gate-code {
        font-family: var(--zl-mono);
        font-size: 11px;
    }
    .zl-status-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .3px;
    }
    .zl-sb-ok   { background: var(--zl-green-l); color: var(--zl-green); }
    .zl-sb-wait { background: var(--zl-amber-l); color: var(--zl-amber); }
    .zl-sb-err  { background: color-mix(in srgb, #ef4444 12%, transparent); color: #ef4444; }
    .zl-companies-tbl {
        width: 100%;
        border-collapse: collapse;
        margin: 10px 0;
        font-size: 12px;
    }
    .zl-companies-tbl th {
        background: var(--zl-accent);
        color: #fff;
        padding: 8px 10px;
        text-align: left;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .3px;
    }
    .zl-companies-tbl td {
        padding: 8px 10px;
        border-bottom: 1px solid var(--zl-bdr);
    }

    /* Setup complete banner */
    .zl-ready-banner {
        background: var(--zl-green-l);
        border: 1px solid color-mix(in srgb, var(--zl-green) 30%, transparent);
        border-radius: var(--zl-rl);
        padding: 1.25rem 1.5rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .zl-ready-text h2 {
        font-size: 15px;
        font-weight: 700;
        color: var(--zl-green);
        margin-bottom: 4px;
    }
    .zl-ready-text p {
        font-size: 12px;
        color: var(--zl-text2);
    }

    /* ── Responsive ── */
    @media (max-width: 640px) {
        .zl-ready-banner { flex-direction: column; align-items: flex-start; }
    }
</style>

<div class="ztl-land sg-modern-theme">
    <div class="zl-wrap sg-page-container">

        {* ── Page header ── *}
        <div class="zl-hdr">
            <div>
                <h1 class="sg-page-title">{$page_title|default:"ZTL Setup"}</h1>
                <p class="zl-hdr-sub">{$page_subtitle|default:"Company onboarding and bank consent setup"}</p>
            </div>
        </div>

        {* ── Flash message ── *}
        {if $messageText|default:''}
            <div class="zl-gate" style="padding: 10px 14px; margin-bottom: 12px; border-left: 4px solid {if $messageType == 'error'}#ef4444{elseif $messageType == 'warning'}var(--zl-amber){else}var(--zl-green){/if};">
                <span style="font-size: 13px;">{$messageText|escape}</span>
            </div>
        {/if}

{* 
   Setup complete banner — shown when consent is authorized and account is selected
   *}
{if $setupComplete|default:false}
    <div class="zl-ready-banner">
        <div class="zl-ready-text">
            <h2>Setup Complete</h2>
            <p>Consent is authorized and a sender account is selected. You can now proceed to payment settlement.</p>
        </div>
        <a href="{$settle_url|default:'#'|escape}" class="zl-gate-btn success large">Go to Settlement &rarr;</a>
    </div>
{/if}

{* COMPANY ONBOARDING — hidden once company is selected *}
{if !$consentAuthorized|default:false && !$hasOnboardedCompany|default:false}

        <div class="zl-gate">
            <div class="zl-gate-title">Company Onboarding</div>
            <form method="post" action="">
                <input type="hidden" name="page" value="ztl_landing" />
                <div class="zl-gate-grid">
                    <div>
                        <label for="zl_country">Country</label>
                        <input type="text" name="country" id="zl_country" value="{$form.country|default:''|escape}" placeholder="DK, NO, or SE" maxlength="2" required />
                    </div>
                    <div>
                        <label for="zl_orgnum">Organization Number</label>
                        <input type="text" name="organizationNumber" id="zl_orgnum" value="{$form.organizationNumber|default:''|escape}" required />
                    </div>
                </div>
                <div class="zl-gate-btns">
                    <button type="submit" name="start_onboarding" value="1" class="zl-gate-btn">Start Onboarding</button>
                </div>
            </form>

            {if $onboarding|default:false}
                <div class="zl-info">
                    <strong>Onboarding ID:</strong> <span class="zl-gate-code">{$onboarding.id|default:''|escape}</span>
                </div>
                {if $onboarding.url|default:''}
                    <div style="margin-top: 8px;">
                        <a href="{$onboarding.url|default:''|escape}" target="_blank" rel="noopener" class="zl-gate-btn">Open Onboarding</a>
                    </div>
                {/if}
                <form method="post" action="" style="margin-top: 8px;">
                    <input type="hidden" name="page" value="ztl_landing" />
                    <input type="hidden" name="onboardingId" value="{$onboarding.id|default:''|escape}" />
                    <button type="submit" name="check_onboarding" value="1" class="zl-gate-btn secondary">Refresh Onboarding Status</button>
                </form>
            {/if}

            {if $onboardingStatus|default:false}
                <div class="zl-info{if $onboardingStatus.status|default:'' == 'Accepted'} success{/if}">
                    <strong>Onboarding Status:</strong>
                    <span class="zl-status-badge {if $onboardingStatus.status|default:'' == 'Accepted'}zl-sb-ok{else}zl-sb-wait{/if}">{$onboardingStatus.status|default:'unknown'|escape}</span>
                </div>
            {/if}
        </div>

{/if}
{* END Company Onboarding *}

{* BANK CONSENT — shown after company is onboarded *}
{if $hasOnboardedCompany|default:false && !$consentAuthorized|default:false}

        {if !$consent|default:false}
        <div class="zl-gate">
            <div class="zl-gate-title">Bank Consent</div>
            <div class="zl-info">
                Authorize access to your bank accounts. Enter your bank details and click <strong>Create Consent</strong> to proceed.
            </div>
            <form method="post" action="" id="zlConsentForm">
                <input type="hidden" name="page" value="ztl_landing" />
                <input type="hidden" name="psuIpAddress" value="{$psu.ipAddress|default:''|escape}" />
                <input type="hidden" name="psuUserAgent" value="{$psu.userAgent|default:''|escape}" />
                <input type="hidden" name="psuAccept" value="{$psu.accept|default:''|escape}" />
                <input type="hidden" name="psuAcceptLanguage" value="{$psu.acceptLanguage|default:''|escape}" />
                <div class="zl-gate-grid">
                    <div>
                        <label for="zl_userId">Bank User ID</label>
                        <input type="text" name="userId" id="zl_userId" value="{$form.userId|default:''|escape}" required />
                    </div>
                    <div>
                        <label for="zl_bic">Bank BIC</label>
                        <input type="text" name="bic" id="zl_bic" value="{$form.bic|default:''|escape}" required />
                    </div>
                    <div>
                        <label for="zl_bankBranch">Bank Branch</label>
                        <input type="text" name="bankBranch" id="zl_bankBranch" value="{$form.bankBranch|default:''|escape}" placeholder="Optional" />
                    </div>
                    <input type="hidden" name="preferredScaMethod" value="Redirect" />
                </div>
                <div class="zl-gate-btns">
                    <button type="submit" name="create_consent" value="1" class="zl-gate-btn">Create Consent</button>
                </div>
            </form>
        </div>
        {/if}

        {*  2B: Consent Result (consent exists but not yet authorized)  *}
        {if $consent|default:false}
        <div class="zl-gate">
            <div class="zl-gate-title">Consent Status</div>
            <div class="zl-info{if $consent.status|default:'' == 'AUTHORIZED' || $consent.status|default:'' == 'VALID'} success{elseif $consent.status|default:'' == 'REJECTED'} error{/if}">
                <strong>Consent ID:</strong> <span class="zl-gate-code">{$consent.id|default:''|escape}</span><br />
                <strong>Status:</strong>
                {if $consent.status|default:'' == 'AUTHORIZED' || $consent.status|default:'' == 'VALID'}
                    <span class="zl-status-badge zl-sb-ok">{$consent.status|escape}</span>
                {elseif $consent.status|default:'' == 'AWAITING_AUTHORIZATION'}
                    <span class="zl-status-badge zl-sb-wait">{$consent.status|escape}</span>
                {elseif $consent.status|default:'' == 'REJECTED' || $consent.status|default:'' == 'EXPIRED'}
                    <span class="zl-status-badge zl-sb-err">{$consent.status|escape}</span>
                {else}
                    <span class="zl-gate-code">{$consent.status|default:'unknown'|escape}</span>
                {/if}
                {if $consent.validUntil|default:''}
                    <br /><strong>Valid Until:</strong> <span class="zl-gate-code">{$consent.validUntil|escape}</span>
                {/if}
            </div>

            <div class="zl-gate-btns" style="margin-top: 8px;">
                <form method="post" action="" style="display: inline;">
                    <input type="hidden" name="page" value="ztl_landing" />
                    <input type="hidden" name="consentId" value="{$consent.id|default:''|escape}" />
                    <button type="submit" name="check_consent_status" value="1" class="zl-gate-btn secondary">Refresh Consent Status</button>
                </form>
            </div>

            {if $consent.status|default:'' == 'AWAITING_AUTHORIZATION'}
                <p style="margin-top: 8px; font-size: 11px; color: var(--zl-text3);">
                    Authorization is in progress. Click "Refresh Consent Status" to check if your bank has completed the process.
                </p>
            {/if}
        </div>
        {/if}

{/if}
{* END Bank Consent *}

{* SETTLEMENT SETUP — account fetch and selection *}
{if $consentAuthorized|default:false && !$accounts|default:false}
        <div class="zl-gate">
            <div class="zl-gate-title">Settlement Setup</div>
            <div class="zl-info success">
                Consent is authorized. Fetch your bank accounts to select a sender account.
            </div>
            <form method="post" action="">
                <input type="hidden" name="page" value="ztl_landing" />
                <div class="zl-gate-btns">
                    <button type="submit" name="fetch_accounts" value="1" class="zl-gate-btn success">Fetch Accounts</button>
                </div>
            </form>
        </div>
{/if}
{*  END Fetch Accounts *}

{if $hasOnboardedCompany|default:false && $consentAuthorized|default:false && $accounts|default:false}
    {if !$selectedFromAccountId|default:''}
        <div class="zl-gate">
            <div class="zl-gate-title">Settlement Setup</div>
            <div class="zl-info">
                Please select the bank account from which you want to send payments.
            </div>
            {if $accounts.accounts|default:false}
                <form method="post" action="">
                    <input type="hidden" name="page" value="ztl_landing" />
                    <div class="zl-gate-grid">
                        <div style="grid-column: 1 / -1;">
                            <label for="zl_fromAccountId">Sender Account</label>
                            <select name="fromAccountId" id="zl_fromAccountId" required style="max-width: 400px; height: auto; padding: 6px 8px;">
                                <option value="">Select an account...</option>
                                {foreach $accounts.accounts as $acct}
                                    <option value="{$acct.id|default:''|escape}">
                                        {$acct.name|default:'Account'|escape} ({$acct.currency|default:''|escape}) - {$acct.bban|default:$acct.iban|default:''|escape}
                                    </option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="zl-gate-btns">
                        <button type="submit" name="select_from_account" value="1" class="zl-gate-btn">Select Sender Account</button>
                    </div>
                </form>
            {else}
                <div class="zl-info error">
                    No accounts found for this consent.
                </div>
            {/if}
        </div>
    {else}
        {* Account selected — show summary and "Go to Settlement" *}
        <div class="zl-gate" style="padding: 12px 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <span style="font-size: 11px; color: var(--zl-text3); text-transform: uppercase; font-weight: 600;">Selected Sender Account</span><br/>
                    {assign var="foundAcct" value=null}
                    {foreach $accounts.accounts as $acct}
                        {if $acct.id == $selectedFromAccountId}
                            {assign var="foundAcct" value=$acct}
                        {/if}
                    {/foreach}
                    {if $foundAcct}
                        <strong style="font-size: 14px;">{$foundAcct.name|default:'Account'|escape} ({$foundAcct.currency|default:''|escape})</strong>
                        <span class="zl-gate-code" style="margin-left: 8px;">{$foundAcct.bban|default:$foundAcct.iban|default:''|escape}</span>
                    {else}
                        <strong class="zl-gate-code">{$selectedFromAccountId|escape}</strong>
                    {/if}
                </div>
                <form method="post" action="" style="margin: 0;">
                    <input type="hidden" name="page" value="ztl_landing" />
                    <button type="submit" name="change_from_account" value="1" class="zl-gate-btn secondary" style="padding: 4px 10px; font-size: 11px;">Change Account</button>
                </form>
            </div>
        </div>
    {/if}
{/if}
{* END Account Selection *}

{*
   STEP 4: Proceed to Settlement (shown when setup is complete)
 *}
{if $setupComplete|default:false}
    <div class="zl-gate" style="text-align: center; padding: 2rem;">
        <div class="zl-info success" style="margin-bottom: 1rem;">
            Bank consent is authorized and a sender account is ready.
            Recurring payments will use this stored consent — no re-authorization required.
        </div>
        <a href="{$settle_url|default:'#'|escape}" class="zl-gate-btn success large">
            Proceed to Payment Settlement &rarr;
        </a>
        <div style="margin-top: 12px;">
            <form method="post" action="" style="display:inline;">
                <input type="hidden" name="page" value="ztl_landing" />
                <button type="submit" name="reset_session" value="1" class="zl-gate-btn secondary" onclick="return confirm('Reset all ZTL session data?');" style="font-size: 11px;">Reset Session</button>
            </form>
        </div>
    </div>
{/if}

    </div>
</div>
