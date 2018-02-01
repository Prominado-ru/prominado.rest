<?php

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Application;

class prominado_rest extends CModule
{
    var $MODULE_ID = 'prominado.rest';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $PARTNER_NAME;
    var $PARTNER_URI;
    var $MODULE_CSS;

    public function prominado_rest()
    {
        $arModuleVersion = [];
        include __DIR__ . '/version.php';
        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }
        $this->MODULE_NAME = Loc::getMessage('PROMINADO_MODULE_REST_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('PROMINADO_MODULE_REST_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('PROMINADO_MODULE_REST_PARTNER');
        $this->PARTNER_URI = Loc::getMessage('PROMINADO_MODULE_REST_PARTNER_WEBSITE');
    }

    public function DoInstall()
    {
        $this->InstallFiles();
        RegisterModule($this->MODULE_ID);

        return true;
    }

    public function InstallFiles()
    {
        CUrlRewriter::Add([
            'CONDITION' => '#^/rest/#',
            'RULE'      => '',
            'PATH'      => '/bitrix/services/prominado.rest/index.php',
        ]);

        CopyDirFiles(Application::getDocumentRoot() . '/bitrix/modules/' . $this->MODULE_ID . '/install/components/prominado',
            Application::getDocumentRoot() . '/bitrix/components/prominado', true, true);
        CopyDirFiles(Application::getDocumentRoot() . '/bitrix/modules/' . $this->MODULE_ID . '/install/services/prominado.rest',
            Application::getDocumentRoot() . '/bitrix/services/prominado.rest', true, true);
    }

    public function DoUninstall()
    {
        UnRegisterModule($this->MODULE_ID);

        return true;
    }

    public function UnInstallFiles()
    {
        DeleteDirFiles(Application::getDocumentRoot() . '/bitrix/modules/' . $this->MODULE_ID . '/install/components/prominado',
            Application::getDocumentRoot() . '/bitrix/components/prominado');
        DeleteDirFiles(Application::getDocumentRoot() . '/bitrix/modules/' . $this->MODULE_ID . '/install/services/prominado.rest',
            Application::getDocumentRoot() . '/bitrix/services/prominado.rest');
    }
}