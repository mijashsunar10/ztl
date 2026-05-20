<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'ZtlSettle.class.php';

if (!class_exists('ZtlSettleAgreementExport', false) && class_exists('ZtlSettle', false)) {
    class ZtlSettleAgreementExport extends ZtlSettle
    {
    }
}
