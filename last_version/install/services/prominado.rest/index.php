<?php

define('NOT_CHECK_PERMISSIONS', true);
define('STOP_STATISTICS', true);
define('BX_SENDPULL_COUNTER_QUEUE_DISABLE', true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->IncludeComponent('prominado:rest.router', '', [
        'SEF_MODE'          => 'Y',
        'SEF_FOLDER'        => '/rest/',
        'SEF_URL_TEMPLATES' => [
            'path' => '#method#',
        ]
    ],
    false,
    ['HIDE_ICONS' => 'Y']
);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';