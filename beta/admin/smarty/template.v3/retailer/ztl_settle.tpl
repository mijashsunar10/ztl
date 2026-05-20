<style>
/*  Settle page base styles  */
.ztl-settle *,
.ztl-settle *::before,
.ztl-settle *::after { box-sizing: border-box; }

.ztl-settle {
    --zs-bg:        var(--sg-surface-bg,  var(--sg-surface-2, #383D43));
    --zs-card:      var(--sg-surface-panel, var(--sg-surface, #272b30));
    --zs-card-alt:  var(--sg-surface-3,  #4B535E);
    --zs-hover:     color-mix(in srgb, var(--sg-text, #F2F4F6) 6%, transparent);
    --zs-text:      var(--sg-text-main,  var(--sg-text, #E4E6EB));
    --zs-text2:     var(--sg-text-muted, #A0A4A8);
    --zs-text3:     var(--sg-text-muted, #8E929B);
    --zs-bdr:       var(--sg-border-soft,  rgba(255,255,255,.12));
    --zs-bdr2:      var(--sg-border-hard,  var(--sg-border-strong, rgba(255,255,255,.18)));
    --zs-accent:    var(--sg-primary-action, #2169f3);
    --zs-accent-l:  color-mix(in srgb, var(--zs-accent) 14%, transparent);
    --zs-green:     var(--sg-success, #5DFF64);
    --zs-green-l:   color-mix(in srgb, var(--zs-green) 14%, transparent);
    --zs-amber:     var(--sg-warning, orange);
    --zs-amber-l:   color-mix(in srgb, var(--zs-amber) 14%, transparent);
    --zs-r:         8px;
    --zs-rl:        12px;
    --zs-font:      var(--sg-font, 'General Sans', system-ui, sans-serif);
    --zs-mono:      'SF Mono', 'Consolas', 'Menlo', monospace;
    --zs-shadow:    0 1px 3px rgba(0,0,0,.18), 0 1px 2px rgba(0,0,0,.12);
}

.ztl-settle {
    font-family: var(--zs-font);
    background: var(--zs-bg);
    color: var(--zs-text);
    min-height: 100vh;
    padding: 2rem 1rem 4rem;
    -webkit-font-smoothing: antialiased;
}
.zs-wrap { max-width: 1080px; margin: 0 auto; }

/* ── Page header ── */
.zs-hdr {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1.25rem;
}
.zs-hdr h1 { font-size: 22px; font-weight: 700; letter-spacing: -.3px; line-height: 1.3; }
.zs-hdr-sub { font-size: 13px; color: var(--zs-text2); margin-top: 2px; }
.zs-sel-count {
    font-size: 12px; color: var(--zs-text2);
    background: var(--zs-card); border: 1px solid var(--zs-bdr);
    border-radius: 20px; padding: 4px 12px; white-space: nowrap;
}

/*  Setup status bar  */
.zs-setup-bar {
    background: var(--zs-card);
    border: 1px solid var(--zs-bdr2);
    border-radius: var(--zs-rl);
    padding: .9rem 1.25rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: .75rem;
    flex-wrap: wrap;
    font-size: 13px;
    box-shadow: var(--zs-shadow);
}
.zs-setup-bar.not-ready {
    border-color: var(--zs-amber);
    background: var(--zs-amber-l);
}
.zs-setup-bar.ready {
    border-color: color-mix(in srgb, var(--zs-green) 30%, transparent);
    background: var(--zs-green-l);
}
.zs-setup-dot {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
}
.zs-setup-dot.ok  { background: var(--zs-green); }
.zs-setup-dot.bad { background: var(--zs-amber); }
.zs-badge {
    display: inline-block; padding: 2px 8px;
    border-radius: 12px; font-size: 10px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .3px;
}
.zs-b-ok   { background: var(--zs-green-l); color: var(--zs-green); }
.zs-b-wait { background: var(--zs-amber-l); color: var(--zs-amber); }
.zs-b-err  { background: color-mix(in srgb, #ef4444 12%, transparent); color: #ef4444; }

/* ── Cards ── */
.zs-card {
    background: var(--zs-card);
    border: 1px solid var(--zs-bdr2);
    border-radius: var(--zs-rl);
    padding: 1.5rem;
    margin-bottom: 1rem;
    box-shadow: var(--zs-shadow);
}
.zs-card-title {
    font-size: 15px; font-weight: 600;
    margin-bottom: 12px; padding-bottom: 8px;
    border-bottom: 2px solid var(--zs-bdr);
}

/* ── Grand total bar ── */
.zs-total {
    background: var(--zs-card);
    border: 1px solid var(--zs-bdr2);
    border-radius: var(--zs-rl);
    padding: 1.15rem 1.4rem;
    margin-bottom: 1rem;
    display: flex; align-items: center;
    justify-content: space-between;
    gap: 1rem; flex-wrap: wrap;
    box-shadow: var(--zs-shadow);
}
.zs-total-lbl { font-size: 11px; text-transform: uppercase; letter-spacing: .7px; color: var(--zs-text3); margin-bottom: 4px; font-weight: 600; }
.zs-total-amt { font-size: 28px; font-weight: 700; font-variant-numeric: tabular-nums; letter-spacing: -.5px; }
.zs-total-meta { font-size: 12px; color: var(--zs-text2); margin-top: 2px; }

/*  Buttons  */
.zs-btn {
    background: var(--zs-accent); color: #fff;
    border: none; border-radius: var(--zs-r);
    padding: 9px 20px; font-size: 13px; font-weight: 600;
    cursor: pointer; font-family: var(--zs-font);
    transition: opacity .15s; text-decoration: none;
    display: inline-block;
}
.zs-btn:hover { opacity: .87; }
.zs-btn.secondary { background: var(--zs-card-alt); color: var(--zs-text); border: 1px solid var(--zs-bdr); }
.zs-btn.success   { background: var(--zs-green); }
.zs-btn.warning   { background: var(--zs-amber); color: #1a1a1a; }
.zs-btn.small     { padding: 5px 13px; font-size: 12px; }
.zs-btn.submit    {
    background: var(--zs-accent); color: #fff;
    font-size: 14px; padding: 11px 26px;
    box-shadow: 0 2px 6px color-mix(in srgb, var(--zs-accent) 30%, transparent);
}
.zs-btn.submit:disabled { opacity: .4; cursor: not-allowed; }

/* ── Controls bar ── */
.zs-controls {
    display: flex; gap: 6px; margin-bottom: 1rem;
    align-items: center; flex-wrap: wrap;
}
.zs-ctrl {
    background: var(--zs-card); border: 1px solid var(--zs-bdr);
    border-radius: var(--zs-r); padding: 5px 13px;
    font-size: 12px; color: var(--zs-text); cursor: pointer;
    font-family: var(--zs-font); font-weight: 500;
    transition: background .12s, border-color .12s;
}
.zs-ctrl:hover { background: var(--zs-card-alt); border-color: var(--zs-bdr2); }
.zs-search-w { flex: 1; min-width: 140px; max-width: 240px; position: relative; margin-left: auto; }
.zs-search-ico { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 13px; height: 13px; opacity: .35; pointer-events: none; }
.zs-search {
    width: 100%; height: 32px; font-size: 12px;
    padding: 0 10px 0 28px; border: 1px solid var(--zs-bdr);
    border-radius: var(--zs-r); background: var(--zs-card);
    color: var(--zs-text); outline: none; font-family: var(--zs-font);
    transition: border-color .15s;
}
.zs-search:focus { border-color: var(--zs-accent); }

/* ── Date block ── */
.zs-date { border: 1px solid var(--zs-bdr); border-radius: var(--zs-rl); margin-bottom: 8px; overflow: hidden; background: var(--zs-card); transition: box-shadow .2s, border-color .2s; }
.zs-date.open { box-shadow: var(--zs-shadow); border-color: var(--zs-bdr2); }
.zs-dh { display: flex; align-items: center; gap: 10px; padding: 11px 16px; cursor: pointer; user-select: none; transition: background .1s; }
.zs-dh:hover { background: var(--zs-hover); }
.zs-chev { width: 16px; height: 16px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: transform .2s ease; color: var(--zs-text3); }
.zs-chev svg { width: 11px; height: 11px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
.zs-date.open .zs-chev { transform: rotate(90deg); }
.zs-dchk { width: 15px; height: 15px; flex-shrink: 0; accent-color: var(--zs-accent); cursor: pointer; }
.zs-dlbl { font-size: 13px; font-weight: 600; flex: 1; }
.zs-dbadge { font-size: 10px; font-weight: 600; color: var(--zs-text3); background: var(--zs-card-alt); padding: 2px 9px; border-radius: 20px; letter-spacing: .3px; }
.zs-dsum { font-size: 14px; font-weight: 700; font-variant-numeric: tabular-nums; min-width: 120px; text-align: right; flex-shrink: 0; }
.zs-db { display: none; border-top: 1px solid var(--zs-bdr); }
.zs-date.open .zs-db { display: block; }
.zs-colh { display: flex; align-items: center; gap: 10px; padding: 5px 16px 5px 44px; border-bottom: 1px solid var(--zs-bdr); background: var(--zs-card-alt); }
.zs-ch { font-size: 10px; text-transform: uppercase; letter-spacing: .5px; color: var(--zs-text3); font-weight: 600; }
.zs-ch-id   { min-width: 80px; flex-shrink: 0; }
.zs-ch-name { flex: 1; }
.zs-ch-amt  { min-width: 110px; text-align: right; flex-shrink: 0; }
.zs-row { display: flex; align-items: center; gap: 10px; padding: 8px 16px 8px 44px; border-bottom: 1px solid var(--zs-bdr); transition: background .08s; }
.zs-row:last-of-type { border-bottom: none; }
.zs-row:hover { background: var(--zs-hover); }
.zs-row.unchecked { opacity: .45; }
.zs-rchk-static { width: 14px; height: 14px; flex-shrink: 0; background: var(--zs-accent); border-radius: 3px; display: flex; align-items: center; justify-content: center; color: #fff; }
.zs-rchk-static svg { width: 10px; height: 10px; stroke: currentColor; fill: none; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; }
.zs-row.unchecked .zs-rchk-static { background: transparent; border: 1px solid var(--zs-bdr2); color: transparent; }
.zs-rid { font-size: 11px; color: var(--zs-text3); font-family: var(--zs-mono); min-width: 80px; flex-shrink: 0; }
.zs-rname { font-size: 13px; flex: 1; font-weight: 500; }
.zs-ramt { font-size: 13px; font-weight: 600; font-variant-numeric: tabular-nums; min-width: 110px; text-align: right; flex-shrink: 0; }
.zs-sub { display: flex; align-items: center; justify-content: space-between; padding: 7px 16px; background: var(--zs-card-alt); border-top: 1px solid var(--zs-bdr); }
.zs-sub-lbl { font-size: 11px; color: var(--zs-text2); }
.zs-sub-amt { font-size: 13px; font-weight: 700; font-variant-numeric: tabular-nums; }
.zs-empty { text-align: center; padding: 3rem; color: var(--zs-text2); font-size: 14px; }

/* ── Account number top bar ── */
.zs-acct-bar {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 20px; margin-bottom: 12px;
    background: var(--zs-card); border: 1px solid var(--zs-bdr2);
    border-left: 4px solid var(--zs-accent);
    border-radius: var(--zs-rl); font-size: 15px;
    box-shadow: var(--zs-shadow);
}
.zs-acct-bar-label { color: var(--zs-text2); font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: .6px; }
.zs-acct-bar-value { font-family: var(--zs-mono); font-weight: 700; font-size: 16px; color: var(--zs-text); letter-spacing: .5px; }
.zs-ref-badge { font-size: 10px; color: var(--zs-text3); font-family: var(--zs-mono); background: rgba(255,255,255,.06); padding: 2px 8px; border-radius: 10px; margin-left: 6px; white-space: nowrap; }

/* ── Payment gate (review & initiate) ── */
.zs-gate-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 12px; }
.zs-gate-grid label { display: block; font-weight: 600; font-size: 12px; margin-bottom: 4px; }
.zs-gate-grid input, .zs-gate-grid select { width: 100%; height: 32px; font-size: 12px; padding: 0 8px; border: 1px solid var(--zs-bdr); border-radius: var(--zs-r); background: var(--zs-card); color: var(--zs-text); font-family: var(--zs-font); }
.zs-info { border-left: 3px solid var(--zs-amber); padding: 8px 12px; border-radius: 4px; font-size: 12px; color: var(--zs-text2); margin: 10px 0; background: var(--zs-amber-l); }
.zs-info.success { border-left-color: var(--zs-green); background: var(--zs-green-l); }
.zs-info.error { border-left-color: #ef4444; background: color-mix(in srgb, #ef4444 10%, transparent); }
.zs-gate-code { font-family: var(--zs-mono); font-size: 11px; }
.zs-btns { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 12px; }

/* ── Payment result ── */
.zs-result { border-left: 4px solid var(--zs-green); padding: 15px; border-radius: var(--zs-r); margin-bottom: 1rem; background: var(--zs-card); border: 1px solid var(--zs-bdr); }
.zs-result.error { border-left-color: #ef4444; }
.zs-result.warning { border-left-color: var(--zs-amber); }
.zs-code { white-space: pre-wrap; word-break: break-word; }

/* ── Tabs ── */
.zs-tabs { display: flex; flex-wrap: wrap; gap: 8px; margin: 15px 0; }
.zs-tab-btn { background: var(--zs-card-alt); color: var(--zs-text); border: none; padding: 9px 16px; border-radius: var(--zs-r); font-weight: 600; cursor: pointer; }
.zs-tab-btn.active { background: var(--zs-accent); color: #fff; }
.zs-tab-panel { display: none; }
.zs-tab-panel.active { display: block; }

/* ── Status badges ── */
.zs-status-badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }
.zs-status-badge.authorized { background: #d1fae5; color: #065f46; }
.zs-status-badge.awaiting   { background: #fef3c7; color: #92400e; }
.zs-status-badge.rejected   { background: #fee2e2; color: #991b1b; }
.zs-status-badge.expired    { background: #e5e7eb; color: #374151; }
.zs-status-badge.completed  { background: #d1fae5; color: #065f46; }
.zs-status-badge.inprogress { background: #dbeafe; color: #1d4ed8; }

@media (max-width: 640px) {
    .zs-colh { display: none; }
    .zs-row { flex-wrap: wrap; padding: 10px 12px 10px 36px; gap: 4px 10px; }
    .zs-rid { min-width: auto; order: 1; }
    .zs-rname { min-width: 100%; order: 3; }
    .zs-ramt { order: 4; margin-left: auto; min-width: auto; }
    .zs-total-amt { font-size: 22px; }
}
</style>

<div class="ztl-settle sg-modern-theme">
<div class="zs-wrap sg-page-container">

{* ── Page header ── *}
<div class="zs-hdr">
    <div>
        <h1 class="sg-page-title">ZTL Settlement</h1>
        <p class="zs-hdr-sub">Select payments and execute settlement using your authorized bank consent</p>
    </div>
    <span class="zs-sel-count" id="zsSelCount"></span>
</div>

{* ── Flash message ── *}
{if $messageText|default:''}
    <div class="zs-card" style="padding: 10px 14px; margin-bottom: 12px; border-left: 4px solid {if $messageType == 'error'}#ef4444{elseif $messageType == 'warning'}var(--zs-amber){else}var(--zs-green){/if};">
        <span style="font-size: 13px;">{$messageText|escape}</span>
    </div>
{/if}

{* ── Consent & account setup status bar ── *}
{assign var="settle_consent_ok" value=false}
{if $consent && ($consent.status|default:'' == 'AUTHORIZED' || $consent.status|default:'' == 'VALID')}
    {assign var="settle_consent_ok" value=true}
{/if}

<div class="zs-setup-bar {if $settle_consent_ok && $selectedFromAccountId|default:''}ready{else}not-ready{/if}">
    <div class="zs-setup-dot {if $settle_consent_ok}ok{else}bad{/if}"></div>
    {if $settle_consent_ok}
        <strong>Consent:</strong>
        <span class="zs-badge zs-b-ok">{$consent.status|default:'AUTHORIZED'|escape}</span>
        {if $consent.validUntil|default:''}
            <span style="font-size: 11px; color: var(--zs-text2);">valid until {$consent.validUntil|escape}</span>
        {/if}
        &nbsp;|&nbsp;
        <strong>Account:</strong>
        {if $selectedFromAccountId|default:''}
            <span class="zs-badge zs-b-ok">Selected</span>
            <span style="font-size: 11px; color: var(--zs-text2); font-family: var(--zs-mono);">{$selectedFromAccountId|escape}</span>
        {else}
            <span class="zs-badge zs-b-wait">Not set</span>
            <a href="{$landing_url|default:'#'|escape}" style="font-size: 12px; color: var(--zs-accent); text-decoration: underline;">Go to setup page to select account</a>
        {/if}
    {else}
        <span style="font-weight: 600;">Setup required:</span>
        No authorized consent found.
        <a href="{$landing_url|default:'#'|escape}" style="font-size: 12px; color: var(--zs-accent); text-decoration: underline; margin-left: 4px;">
            Go to setup page (ztl_landing) &rarr;
        </a>
        <span style="margin-left: auto;">
            <form method="post" action="" style="display:inline;">
                <input type="hidden" name="page" value="ztl_settle" />
                <button type="submit" name="reset_session" value="1" class="zs-btn small secondary" onclick="return confirm('Reset all ZTL session data?');">Reset Session</button>
            </form>
        </span>
    {/if}
</div>

{*
   PAYMENT SELECTION — only shown when consent is authorized
 *}
{if $settle_consent_ok && $selectedFromAccountId|default:''}

    {* Grand total bar *}
    <div class="zs-total">
        <div>
            <div class="zs-total-lbl">{if $showPaymentGate|default:false}Settlement summary{else}Total selected{/if}</div>
            <div class="zs-total-amt" id="zsGrandTotal">{$currency|default:"NOK"} 0.00</div>
            <div class="zs-total-meta" id="zsGrandMeta"></div>
        </div>
        {if !$showPaymentGate|default:false}
        <button class="zs-btn submit" id="zsSubmitBtn" onclick="zsSubmit()">
            Confirm &amp; Settle
        </button>
        {/if}
    </div>

    {* Controls (hidden when frozen in payment gate) *}
    {if !$showPaymentGate|default:false}
    <div class="zs-controls">
        <button class="zs-ctrl" onclick="zsToggleAll(true)">Select all</button>
        <button class="zs-ctrl" onclick="zsToggleAll(false)">Deselect all</button>
        <button class="zs-ctrl" onclick="zsExpandAll(true)">Expand all</button>
        <button class="zs-ctrl" onclick="zsExpandAll(false)">Collapse all</button>
        <div class="zs-search-w">
            <svg class="zs-search-ico" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                <circle cx="6.5" cy="6.5" r="4.5" />
                <line x1="10" y1="10" x2="14" y2="14" />
            </svg>
            <input class="zs-search" type="text" id="zsSearchBox" placeholder="Search supplier name…" oninput="zsFilter(this.value)" />
        </div>
    </div>
    {/if}

    {* Hidden form for payment selection submission *}
    <form id="zsForm" method="POST" action="">
        <input type="hidden" name="_token" value="{$csrf_token|default:''}" />
        <input type="hidden" name="page" value="ztl_settle" />
        <div id="zsHidden"></div>
    </form>

   
    {if $payment|default:false}
        {assign var="activePaymentStatus" value=$paymentStatus|default:$payment.paymentStatus}
        {assign var="activePaymentId" value=$payment.paymentId|default:$payment.id|default:''}
        <div class="zs-card">
            <div class="zs-card-title">Payment Status</div>
            <div class="zs-result {if $activePaymentStatus.status|default:'' == 'Rejected'}error{elseif $activePaymentStatus.status|default:'' == 'Cancelled'}warning{/if}">
                <strong>Payment ID:</strong> <span class="zs-code">{$activePaymentId|escape}</span><br />
                <strong>Status:</strong>
                {if $activePaymentStatus.status|default:'' == 'Unsigned'}
                    <span class="zs-status-badge awaiting">{$activePaymentStatus.status|escape}</span>
                {elseif $activePaymentStatus.status|default:'' == 'InProgress'}
                    <span class="zs-status-badge inprogress">{$activePaymentStatus.status|escape}</span>
                {elseif $activePaymentStatus.status|default:'' == 'Completed'}
                    <span class="zs-status-badge completed">{$activePaymentStatus.status|escape}</span>
                {elseif $activePaymentStatus.status|default:'' == 'Rejected' || $activePaymentStatus.status|default:'' == 'Cancelled'}
                    <span class="zs-status-badge rejected">{$activePaymentStatus.status|escape}</span>
                {else}
                    <span class="zs-code">{$activePaymentStatus.status|default:'unknown'|escape}</span>
                {/if}
                {if $activePaymentStatus.statusReason|default:''}
                    <br /><strong>Reason:</strong> <span class="zs-code">{$activePaymentStatus.statusReason|escape}</span>
                {/if}
            </div>

            {if $paymentApproval|default:false}
                <div class="zs-info" style="margin-top: 8px;">
                    <strong>Approval ID:</strong> <span class="zs-gate-code">{$paymentApproval.id|default:''|escape}</span><br />
                    <strong>Approval Status:</strong> <span class="zs-gate-code">{$paymentApproval.status|default:'unknown'|escape}</span>
                    {if $showPaymentApprovalSca|default:false && !$approvalDone}
                        <br /><a href="{$paymentApprovalScaUrl|escape}" target="_blank" rel="noopener" class="zs-btn warning small" style="margin-top: 8px; display: inline-block;">
                            Open Payment Approval SCA
                        </a>
                    {/if}
                </div>
            {/if}

            {if $paymentApprovalStatus|default:false}
                <div class="zs-info" style="margin-top: 8px;">
                    <strong>Latest Approval Status:</strong> <span class="zs-gate-code">{$paymentApprovalStatus.status|default:'unknown'|escape}</span>
                    {if $showPaymentApprovalStatusSca|default:false && !$approvalDone}
                        <br /><a href="{$paymentApprovalStatusScaUrl|escape}" target="_blank" rel="noopener" class="zs-btn warning small" style="margin-top: 8px; display: inline-block;">
                            Open Latest Approval SCA
                        </a>
                    {/if}
                </div>
            {/if}

            {if $paymentCancellation|default:false}
                <div class="zs-info warning" style="margin-top: 8px;">
                    <strong>Cancellation:</strong>
                    <span class="zs-gate-code">{$paymentCancellation.cancellationRequestStatus|default:'unknown'|escape}</span>
                    {if $paymentCancellation.reason|default:''}
                        &mdash; {$paymentCancellation.reason|escape}
                    {/if}
                </div>
            {/if}

            {* Approval is "done" when the approval or its latest status is in a terminal accepted/completed state *}
            {assign var="approvalDone" value=false}
            {assign var="_apSt"  value=$paymentApproval.status|default:''|upper}
            {assign var="_apStL" value=$paymentApprovalStatus.status|default:''|upper}
            {assign var="_pmSt"  value=$activePaymentStatus.status|default:''}
            {if $_apSt  == 'ACCEPTED' || $_apSt  == 'APPROVED' || $_apSt  == 'AUTHORIZED' || $_apSt  == 'COMPLETED' || $_apSt  == 'VALID'
             || $_apStL == 'ACCEPTED' || $_apStL == 'APPROVED' || $_apStL == 'AUTHORIZED' || $_apStL == 'COMPLETED' || $_apStL == 'VALID'
             || $_pmSt  == 'Completed' || $_pmSt == 'InProgress'}
                {assign var="approvalDone" value=true}
            {/if}

            <div class="zs-btns" style="margin-top: 12px; flex-wrap: wrap;">
                {if $activePaymentId && !$approvalDone}
                    <form method="post" action="" style="display:inline;">
                        <input type="hidden" name="page" value="ztl_settle" />
                        <input type="hidden" name="paymentId" value="{$activePaymentId|escape}" />
                        <button type="submit" name="approve_payment" value="1" class="zs-btn small">Approve Payment</button>
                    </form>
                    <form method="post" action="" style="display:inline;">
                        <input type="hidden" name="page" value="ztl_settle" />
                        <input type="hidden" name="paymentId" value="{$activePaymentId|escape}" />
                        <button type="submit" name="cancel_payment" value="1" class="zs-btn small warning" onclick="return confirm('Cancel this payment?');">Cancel Payment</button>
                    </form>
                {/if}
            </div>

            {if ($paymentApproval.status|default:'') == 'APPROVED' || ($paymentApproval.status|default:'') == 'AUTHORIZED' || ($paymentApproval.status|default:'') == 'COMPLETED' || ($payment.paymentStatus.status|default:'') == 'Completed'}
            <div class="zs-btns" style="margin-top: 12px;">
                <form method="post" action="" style="display:inline;">
                    <input type="hidden" name="page" value="ztl_settle" />
                    <button type="submit" name="reset_session" value="1" class="zs-btn">Start New Settlement</button>
                </form>
            </div>
            {/if}
        </div>
    {/if}

    {* Payment gate — review & initiate (shown after payment selection, above list) *}
    {if $showPaymentGate|default:false && !$payment|default:false && $paymentPrefill|default:false}
    <div class="zs-card">
        <div class="zs-card-title">Review &amp; Initiate Settlement</div>
        <div class="zs-info success" style="margin-bottom: 15px;">
            Using your authorized consent. No re-authorization required.
        </div>
        <form method="post" action="">
            <input type="hidden" name="page" value="ztl_settle" />
            <input type="hidden" name="paymentMode" value="{$paymentMode|default:'domestic'|escape}" />
            <input type="hidden" name="paymentFromAccountId" value="{$paymentPrefill.paymentFromAccountId|default:$selectedFromAccountId|default:''|escape}" />
            <input type="hidden" name="paymentPreferredScaMethod" value="Redirect" />

            {* Banking details always passed as hidden — not displayed *}
            <input type="hidden" name="paymentToBban" value="{$paymentPrefill.paymentToBban|default:''|escape}" />
            <input type="hidden" name="paymentToIban" value="{$paymentPrefill.paymentToIban|default:''|escape}" />
            <input type="hidden" name="paymentToBic" value="{$paymentPrefill.paymentToBic|default:''|escape}" />
            <input type="hidden" name="paymentToCountry" value="{$paymentPrefill.paymentToCountry|default:''|escape}" />
            <input type="hidden" name="paymentToClearingCode" value="{$paymentPrefill.paymentToClearingCode|default:''|escape}" />

            {* Purpose Code and End-to-End ID are technical fields — passed as hidden *}
            <input type="hidden" name="paymentPurposeCode" value="{$paymentPrefill.paymentPurposeCode|default:'OTHR'|escape}" />
            <input type="hidden" name="paymentEndToEndId" value="{$paymentPrefill.paymentEndToEndId|default:''|escape}" />

            {* Fields the user needs to review and confirm *}
            <div class="zs-gate-grid">
                <div>
                    <label>Recipient</label>
                    <input type="text" name="paymentToName" value="{$paymentPrefill.paymentToName|default:''|escape}" readonly style="background: var(--zs-card-alt); cursor: default;" />
                </div>
                <div>
                    <label>Amount</label>
                    <input type="text" name="paymentAmount" value="{$paymentPrefill.paymentAmount|default:''|escape}" required readonly style="background: var(--zs-card-alt); cursor: default;" />
                </div>
                <div>
                    <label>Currency</label>
                    <input type="text" name="paymentCurrency" value="{$paymentPrefill.paymentCurrency|default:'NOK'|escape}" maxlength="3" required readonly style="background: var(--zs-card-alt); cursor: default;" />
                </div>
                <div>
                    <label>Due Date</label>
                    <input type="date" name="paymentDueDate" value="{$paymentPrefill.paymentDueDate|default:$smarty.now|date_format:'%Y-%m-%d'|escape}" required />
                </div>
                {if $paymentPrefill.paymentRemittance|default:''}
                <div>
                    <label>Message</label>
                    <input type="text" name="paymentRemittance" value="{$paymentPrefill.paymentRemittance|escape}" readonly style="background: var(--zs-card-alt); cursor: default;" />
                </div>
                {else}
                    <input type="hidden" name="paymentRemittance" value="" />
                {/if}
            </div>

            {* Cross-border sender details: only show fields the user must fill in.
               Pre-filled (readonly) fields are passed as hidden — no action needed from user. *}
            {if $paymentMode|default:'domestic' == 'cross_border'}
                {assign var="_cbNeedsInput" value=false}
                {if !$paymentPrefill.paymentFromAccountBic|default:''}        {assign var="_cbNeedsInput" value=true}{/if}
                {if !$paymentPrefill.paymentFromAddressStreetName|default:''}  {assign var="_cbNeedsInput" value=true}{/if}
                {if !$paymentPrefill.paymentFromAddressBuildingNumber|default:''}{assign var="_cbNeedsInput" value=true}{/if}
                {if !$paymentPrefill.paymentFromAddressCity|default:''}        {assign var="_cbNeedsInput" value=true}{/if}
                {if !$paymentPrefill.paymentFromAddressPostCode|default:''}    {assign var="_cbNeedsInput" value=true}{/if}

                {* Hidden pass-through for pre-filled fields *}
                {if $paymentPrefill.paymentFromAccountBic|default:''}
                    <input type="hidden" name="paymentFromAccountBic" value="{$paymentPrefill.paymentFromAccountBic|escape}" />
                {/if}
                {if $paymentPrefill.paymentFromAddressStreetName|default:''}
                    <input type="hidden" name="paymentFromAddressStreetName" value="{$paymentPrefill.paymentFromAddressStreetName|escape}" />
                {/if}
                {if $paymentPrefill.paymentFromAddressBuildingNumber|default:''}
                    <input type="hidden" name="paymentFromAddressBuildingNumber" value="{$paymentPrefill.paymentFromAddressBuildingNumber|escape}" />
                {/if}
                {if $paymentPrefill.paymentFromAddressCity|default:''}
                    <input type="hidden" name="paymentFromAddressCity" value="{$paymentPrefill.paymentFromAddressCity|escape}" />
                {/if}
                {if $paymentPrefill.paymentFromAddressPostCode|default:''}
                    <input type="hidden" name="paymentFromAddressPostCode" value="{$paymentPrefill.paymentFromAddressPostCode|escape}" />
                {/if}

                {* Only show sender details section if any field needs user input *}
                {if $_cbNeedsInput}
                <div class="zs-gate-grid" style="margin-top: 15px; background: rgba(0,0,0,0.04); padding: 15px; border-radius: 6px;">
                    <div style="grid-column: 1 / -1; font-weight: 600; font-size: 12px; text-transform: uppercase; color: var(--zs-text3);">Sender Details</div>
                    {if !$paymentPrefill.paymentFromAccountBic|default:''}
                    <div><label>From Account BIC</label><input type="text" name="paymentFromAccountBic" value="" /></div>
                    {/if}
                    {if !$paymentPrefill.paymentFromAddressStreetName|default:''}
                    <div><label>From Street</label><input type="text" name="paymentFromAddressStreetName" value="" /></div>
                    {/if}
                    {if !$paymentPrefill.paymentFromAddressBuildingNumber|default:''}
                    <div><label>From Building No.</label><input type="text" name="paymentFromAddressBuildingNumber" value="" /></div>
                    {/if}
                    {if !$paymentPrefill.paymentFromAddressCity|default:''}
                    <div><label>From City</label><input type="text" name="paymentFromAddressCity" value="" /></div>
                    {/if}
                    {if !$paymentPrefill.paymentFromAddressPostCode|default:''}
                    <div><label>From Post Code</label><input type="text" name="paymentFromAddressPostCode" value="" /></div>
                    {/if}
                </div>
                {/if}
            {else}
                <input type="hidden" name="paymentFromAccountBic" value="" />
                <input type="hidden" name="paymentFromAddressStreetName" value="" />
                <input type="hidden" name="paymentFromAddressBuildingNumber" value="" />
                <input type="hidden" name="paymentFromAddressCity" value="" />
                <input type="hidden" name="paymentFromAddressPostCode" value="" />
            {/if}

            <div class="zs-btns" style="margin-top: 20px;">
                {if $paymentMode|default:'domestic' == 'cross_border'}
                <button type="submit" name="initiate_cross_border_payment" value="1" class="zs-btn" style="font-size: 14px; padding: 10px 28px;"
                    onclick="return confirm('Initiate cross-border settlement using stored consent?');">
                    Initiate Cross-Border Settlement
                </button>
                {else}
                <button type="submit" name="initiate_payment" value="1" class="zs-btn" style="font-size: 14px; padding: 10px 28px;"
                    onclick="return confirm('Initiate settlement using stored consent?');">
                    Initiate Settlement
                </button>
                {/if}
            </div>
        </form>

        {* Settlement amount summary below form *}
        <div class="zs-total" style="margin-top: 1.5rem;">
            <div>
                <div class="zs-total-lbl">Settlement Amount</div>
                <div class="zs-total-amt">{$paymentPrefill.paymentCurrency|default:'NOK'} {$paymentPrefill.paymentAmount|default:'0.00'}</div>
                <div class="zs-total-meta">{$pendingPayments|@count} payment{if $pendingPayments|@count != 1}s{/if} selected</div>
            </div>
        </div>
    </div>
    {/if}

    {* Account number — shown once at the top *}
    {if $bankAccountNumber|default:''}
    <div class="zs-acct-bar">
        <span class="zs-acct-bar-label">Account Number</span>
        <span class="zs-acct-bar-value">{$bankAccountNumber|escape}</span>
    </div>
    {/if}

    {* Payment date list (rendered by JS below) *}
    <div id="zsDateList"></div>

{elseif $settle_consent_ok && !$selectedFromAccountId|default:''}
    <div class="zs-card">
        <div class="zs-info">
            Consent is authorized but no sender account is selected.
            <a href="{$landing_url|default:'#'|escape}" style="color: var(--zs-accent); text-decoration: underline;">Return to setup page</a> to select an account, then come back here to settle payments.
        </div>
    </div>
{/if}


</div>{* /zs-wrap *}
</div>{* /ztl-settle *}

{if $settle_consent_ok && $selectedFromAccountId|default:''}
<script>
(function () {
    'use strict';

    var RAW  = {$payment_dates|json_encode};
    var CUR  = '{$currency|default:"NOK"}';
    var LOC  = '{$locale|default:"nb-NO"}';
    var PENDING  = {$pendingPayments|default:[]|json_encode};
    var HAS_PENDING = PENDING && PENDING.length > 0;
    var FROZEN = {if $showPaymentGate|default:false}true{else}false{/if};

    var S = RAW.map(function (d, i) {
        return {
            date: d.date, label: d.label, reference: d.reference || '', open: false,
            payments: d.payments.map(function (p) {
                var isChecked = HAS_PENDING ? (PENDING.indexOf(p.reference) !== -1) : true;
                return { reference: p.reference, brandname: p.brandname || '', amount: parseFloat(p.amount), checked: isChecked };
            })
        };
    });

    function fmt(n) {
        return CUR + ' ' + n.toLocaleString(LOC, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function chev() { return '<svg viewBox="0 0 12 12"><polyline points="4,2 9,6 4,10"/></svg>'; }
    function checkIco() { return '<svg viewBox="0 0 12 12"><polyline points="2.5,6 5,8.5 9.5,3.5"/></svg>'; }

    function render(filter) {
        var fl = (filter || '').toLowerCase();
        var list = document.getElementById('zsDateList');
        list.innerHTML = '';
        var vis = false;

        S.forEach(function (date, di) {
            var basePayments = FROZEN
                ? date.payments.filter(function (p) { return p.checked; })
                : date.payments;
            var filtered = fl
                ? basePayments.filter(function (p) {
                    return (p.brandname && p.brandname.toLowerCase().indexOf(fl) !== -1)
                        || (p.reference && p.reference.toLowerCase().indexOf(fl) !== -1);
                })
                : basePayments;

            if (!filtered.length) return;
            vis = true;

            var isOpen = fl.length > 0 || date.open;
            var chkF   = filtered.filter(function (p) { return p.checked; });
            var dateSum = chkF.reduce(function (a, p) { return a + p.amount; }, 0);
            var allChk  = basePayments.every(function (p) { return p.checked; });
            var someChk = basePayments.some(function (p) { return p.checked; });

            var rows = filtered.map(function (p) {
                if (FROZEN && !p.checked) return '';
                return '<div class="zs-row' + (p.checked ? '' : ' unchecked') + '">'
                    + '<div class="zs-rchk-static">' + checkIco() + '</div>'
                    + '<span class="zs-rname">' + (p.brandname || '') + '</span>'
                    + '<span class="zs-ramt">' + fmt(p.amount) + '</span>'
                    + '</div>';
            }).join('');

            // Build supplier name summary for header (retailer sees WHO they're paying)
            var uniqueNames = [];
            filtered.forEach(function (p) {
                var name = (p.brandname || '').trim();
                if (name && uniqueNames.indexOf(name) === -1) uniqueNames.push(name);
            });
            var supplierSummary = '';
            if (isOpen || uniqueNames.length <= 3) {
                // When open or few names: show first 2-3, no "+more" clutter
                supplierSummary = uniqueNames.slice(0, 3).join(', ');
            } else {
                supplierSummary = uniqueNames.slice(0, 2).join(', ') + ' +' + (uniqueNames.length - 2) + ' more';
            }

            var colH = '<div class="zs-colh">'
                + '<span class="zs-ch zs-ch-name">Supplier / Brand</span>'
                + '<span class="zs-ch zs-ch-amt">Amount</span>'
                + '</div>';

            var el = document.createElement('div');
            el.className = 'zs-date' + (isOpen ? ' open' : '');
            el.setAttribute('data-di', di);

            var headerHtml = '<div class="zs-dh" onclick="zsHdrClick(event,' + di + ')">'
                + '<div class="zs-chev">' + chev() + '</div>';
            if (!FROZEN) {
                headerHtml += '<input type="checkbox" class="zs-dchk" data-di="' + di + '"'
                    + (allChk ? ' checked' : '')
                    + ' onclick="event.stopPropagation(); zsDateChk(' + di + ', this)" />';
            }
            headerHtml += '<span class="zs-dlbl"><strong>' + supplierSummary + '</strong></span>'
                + '<span class="zs-dsum"><strong>' + fmt(dateSum) + '</strong></span>'
                + '</div>';

            var subLabel = FROZEN
                ? (chkF.length + ' payment' + (chkF.length !== 1 ? 's' : ''))
                : (chkF.length + ' of ' + filtered.length + ' selected');

            el.innerHTML = headerHtml
                + '<div class="zs-db">' + colH + rows
                + '<div class="zs-sub">'
                + '<span class="zs-sub-lbl">' + subLabel + '</span>'
                + '<span class="zs-sub-amt">' + fmt(dateSum) + '</span>'
                + '</div></div>';

            list.appendChild(el);

            if (!FROZEN && someChk && !allChk) {
                var chkEl = el.querySelector('.zs-dchk');
                if (chkEl) chkEl.indeterminate = true;
            }
        });

        if (!vis) {
            list.innerHTML = '<div class="zs-empty">No payments match your search.</div>';
        }
        totals();
    }

    window.zsHdrClick = function (e, di) {
        if (e.target.tagName === 'INPUT') return;
        S[di].open = !S[di].open;
        var sb = document.getElementById('zsSearchBox');
        render(sb ? sb.value : '');
    };
    window.zsDateChk = function (di, cb) {
        if (FROZEN) return;
        var v = cb.checked;
        S[di].payments.forEach(function (p) { p.checked = v; });
        var sb = document.getElementById('zsSearchBox');
        render(sb ? sb.value : '');
    };
    window.zsToggleAll = function (v) {
        if (FROZEN) return;
        S.forEach(function (d) { d.payments.forEach(function (p) { p.checked = v; }); });
        var sb = document.getElementById('zsSearchBox');
        render(sb ? sb.value : '');
    };
    window.zsExpandAll = function (v) {
        if (FROZEN) return;
        S.forEach(function (d) { d.open = v; });
        var sb = document.getElementById('zsSearchBox');
        render(sb ? sb.value : '');
    };
    window.zsFilter = function (v) {
        if (FROZEN) return;
        render(v);
    };

    window.zsSubmit = function () {
        var c = document.getElementById('zsHidden');
        c.innerHTML = '';
        var n = 0;
        S.forEach(function (d) {
            d.payments.forEach(function (p) {
                if (!p.checked) return;
                var inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = 'payment_ids[]'; inp.value = p.reference;
                c.appendChild(inp); n++;
            });
        });
        if (n === 0) { alert('Please select at least one payment before submitting.'); return; }

        var actionInp = document.createElement('input');
        actionInp.type = 'hidden'; actionInp.name = 'proceed_to_payment'; actionInp.value = '1';
        document.getElementById('zsForm').appendChild(actionInp);
        document.getElementById('zsForm').submit();
    };

    function totals() {
        var t = 0, chkDates = 0, allDates = S.length;
        S.forEach(function (d) {
            var dateHasChecked = false;
            d.payments.forEach(function (p) {
                if (p.checked) { t += p.amount; dateHasChecked = true; }
            });
            if (dateHasChecked) chkDates++;
        });
        document.getElementById('zsGrandTotal').textContent = fmt(t);
        var metaEl = document.getElementById('zsGrandMeta');
        if (metaEl) {
            metaEl.textContent = FROZEN
                ? chkDates + ' payment' + (chkDates !== 1 ? 's' : '')
                : chkDates + ' of ' + allDates + ' payments selected';
        }
        var selEl = document.getElementById('zsSelCount');
        if (selEl) selEl.textContent = chkDates + ' selected';
        var btnEl = document.getElementById('zsSubmitBtn');
        if (btnEl) btnEl.disabled = (chkDates === 0);
    }

    render();
}());
</script>
{/if}
