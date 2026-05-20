<?php

// LOCAL CONFIG BELOW
// ---------------------------

define("PRODUCTION", false);

define('ADMIN_ROOT_DIR', '/admin/');
if (stristr($_SERVER["DOCUMENT_ROOT"], ADMIN_ROOT_DIR) > '') {
    $prefix = $_SERVER['DOCUMENT_ROOT'];
} else {
    $prefix = $_SERVER['DOCUMENT_ROOT'] . ADMIN_ROOT_DIR;
}
define('SITE_ROOT_DIR', __DIR__ . '/../');

$protocol = $_SERVER['PROTOCOL'] = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http';
define('SERVER_PROTOCOL', $protocol);
define('SPG_DOMAIN', SERVER_PROTOCOL . '://beta.splitgr.id');
define('SPG_RETAIL_DOMAIN', SERVER_PROTOCOL . '://webdev.splitgr.id');
define('CRUNCH_DOMAIN', SERVER_PROTOCOL . '://crunch.splitgrid.com');

define('CERTIFICATE_DIR', SITE_ROOT_DIR . 'certificate/');
define("LOG_DIR", SITE_ROOT_DIR . "logs/");

if (isset($_SESSION["layout"]) && $_SESSION["layout"] == "3") {
    define('JS_DIR', 'js.v3/');
    define('CSS_DIR', 'css.v3/');
    define('TEMPLATE_DIR', $prefix . '/smarty/template.v3/');
} else {
    define('JS_DIR', 'js.v2/');
    define('CSS_DIR', 'css.v2/');
    define('TEMPLATE_DIR', $prefix . '/smarty/template.v2/');
}
// print "LY:".$_SESSION["layout"];
define('IMG_DIR', 'i/');
define('ICONS_DIR', './images/icons/');

define('CONFIG_DIR', __DIR__ . '/');
define('MENU_DIR', __DIR__ . '/');
define('ADMIN_DIR', $prefix);
define('FORMS_DIR', $prefix . 'forms/');
define('DO_ACTION_DIR', $prefix . 'doaction/');

define('PLUGIN_DIR', $prefix . 'plugin/');
define('MODULES_DIR', $prefix . 'modules/');
define('DEFAULT_LANGUAGE', 'sv');
define('LANGUAGE_DIR', $prefix . 'lang/');

define('API_URL', 'dev.splitgr.id/api/v1/');
define("API_URL_EXTERNAL", SERVER_PROTOCOL . "://dev.splitgr.id/api/v1/");
define('API_ADMIN_URL', SERVER_PROTOCOL . '://webdev.splitgr.id/api/adminv2/');
define('BC_API_URL', '45.138.141.21');

define('TPL_MAIL_DIR', 'mail/');
define('TPL_SUPPLIER_DIR', 'supplier/');
define('TPL_SYS_DIR', 'sys/');
define('TPL_REPORTS_DIR', 'reports/');
define('TPL_RETAILER_DIR', 'retailer/');
define('TPL_PARTIAL_DIR', 'partial/');
define('TPL_AGREEMENTS_DIR', 'agreements/');
define('TPL_KYC_DIR', 'compliance/');
$template_dir_r = array(
    TEMPLATE_DIR,
    TEMPLATE_DIR . TPL_MAIL_DIR,
    TEMPLATE_DIR . TPL_SUPPLIER_DIR,
    TEMPLATE_DIR . TPL_SYS_DIR,
    TEMPLATE_DIR . TPL_REPORTS_DIR,
    TEMPLATE_DIR . TPL_RETAILER_DIR,
    TEMPLATE_DIR . TPL_PARTIAL_DIR,
    TEMPLATE_DIR . TPL_AGREEMENTS_DIR,
    TEMPLATE_DIR . TPL_KYC_DIR,
);
define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/uploads/');
define('UPLOAD_IMG_DIR', $_SERVER['DOCUMENT_ROOT'] . '/uploads/');
define('UPLOAD_FILE_DIR', $_SERVER['DOCUMENT_ROOT'] . '/uploads/');
define('GALLERY_IMG_DIR', "/uploads/");
define('DOCUMENT_DIR', $prefix . "../../documents/");
define('MAX_ROWS_PER_PAGE', 25);

define('DATABASE_TYPE', 'postgres');
define('DATABASE_HOST', 'localhost');
define('DATABASE_USER', 'splitgrid_dev');

if (DATABASE_TYPE == 'postgres') {
    $phrase['db_true'] = 't';
    $phrase['db_false'] = 'f';

} else {
    $phrase['db_true'] = '1';
    $phrase['db_false'] = '0';
}

define('DB_TRUE', $phrase['db_true']);
define('DB_FALSE', $phrase['db_false']);

define('ADMIN_EMAIL', 'jf@splitgrid.com');
define('MAIL_ACCOUNTANT', 'noreply@splitgrid.com');
define("ALERT_EMAIL", "alerts@splitgrid.com");
define('COLUMN_PREFIX', 'column_');
define('DEACTIVATION_DAYS', 15);
define('PLUSIUS_API_KEY', 'b60f3e50d8304bf0bc88e0a4b2ca7656');
define('PLUSIUS_API_URL', 'https://gateway.plusius.io/sandbox/');
define('ZIGNED_WEBHOOK_KEY', 'e52dda75ef6a431620beeccf90c731d3e0ae80722686198ec8557139d8e18a54');

define('HELP_REPORT_ARCHIVE_SV', 'https://help.splitgrid.com/rapportarkiv');
define('HELP_REPORT_ARCHIVE_EN', 'https://help.splitgrid.com/en/report_archive');

if (isset($_GET['db']))
    $_SESSION['db'] = $_GET['db'];

// If DATABASE_PASSWORD is defined here,
// it is automatically written to the db-file
define('DATABASE_PASSWORD', '!DTxRB5YrgA+EcFP');
define('DATABASE_NAME', 'splitgrid_admin_dev');
define('DB_HOST', 'BETA');
define('DB_TOGGLE', 1);


# ------------ S M A R T Y -------------------------->
require_once 'smarty/Smarty.class.php';
define('SMARTY_DIR', $prefix . '/smarty/');
// define('TEMPLATE_DIR',$prefix.'/smarty/template/'); 	Template dir is set above
define('COMPILE_DIR', __DIR__ . '/../smarty/template_c/');
define('CACHE_DIR', __DIR__ . '/../cache/');

use Smarty\Smarty;
$smarty = new Smarty;
$smarty->setTemplateDir($template_dir_r);
//Register some php functions as plugins to allow use as modifiers.
$smarty->registerPlugin('modifier', 'strtoupper', 'strtoupper');
$smarty->registerPlugin('modifier', 'ucfirst', 'ucfirst');
$smarty->registerPlugin('modifier', 'strtolower', 'strtolower');
$smarty->registerPlugin('modifier', 'str_replace', 'str_replace');
$smarty->registerPlugin('modifier', 'is_numeric', 'is_numeric');
$smarty->setCompileDir(COMPILE_DIR);
$smarty->setCacheDir(CACHE_DIR);
// $smarty->setConfigDir('/some/config/dir');
$smarty->debugging_ctrl = 'URL';

# <------------ S M A R T Y ------------------------------



// GLOBAL CONFIG BELOW
// ---------------------------

define('INCLUDE_CORE_DIR', '/home/cms_core_beta_v0.10/');
define('INCLUDE_DIR', INCLUDE_CORE_DIR . 'include/');
define('CLASS_CORE_LIB', INCLUDE_CORE_DIR . 'lib/');
define('STATIC_CONFIG_DIR', INCLUDE_CORE_DIR . 'config/');

require_once(STATIC_CONFIG_DIR . '/config.inc.php');

include_once(INCLUDE_DIR . 'security.inc.php');
include_once(INCLUDE_DIR . 'db_procedures.inc.php');
include_once(CONFIG_DIR . 'look_up_tables.inc.php');

