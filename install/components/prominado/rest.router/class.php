<?php

namespace Prominado\Components;

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Prominado\Rest\RestException;
use Prominado\Rest\Route;

class RestRouter extends \CBitrixComponent
{
    private function prepareComponent()
    {
        Loader::includeSharewareModule('prominado.rest');
    }

    private function getQuery()
    {
        $request = Context::getCurrent()->getRequest();

        return $request->toArray();
    }

    private function getHttpMethod()
    {
        return Context::getCurrent()->getRequest()->getRequestMethod();
    }

    private function route()
    {
        global $APPLICATION;

        $arDefaultUrlTemplates404 = [
            'method'  => '#method#',
            'method1' => '#method#/',
        ];

        $arDefaultVariableAliases404 = [];
        $arComponentVariables = ['method'];

        $arVariables = [];

        $query = $this->getQuery();

        if ($this->arParams['SEF_MODE'] === 'Y') {
            $arUrlTemplates = \CComponentEngine::makeComponentUrlTemplates($arDefaultUrlTemplates404,
                $this->arParams['SEF_URL_TEMPLATES']);
            $arVariableAliases = \CComponentEngine::makeComponentVariableAliases($arDefaultVariableAliases404,
                $this->arParams['VARIABLE_ALIASES']);

            $componentPage = \CComponentEngine::parseComponentPath(
                $this->arParams['SEF_FOLDER'],
                $arUrlTemplates,
                $arVariables
            );

            \CComponentEngine::initComponentVariables($componentPage, $arComponentVariables, $arVariableAliases,
                $arVariables);
        } else {
            ShowError('Non-SEF mode is not supported by bitrix:rest.server component');
        }

        $transport = 'json';
        $method = mb_strtolower($arVariables['method']);
        $point = strrpos($method, '.');

        if ($point > 0) {
            $check = substr($method, $point + 1);
            if (Route::isTransportAvailable($check)) {
                $transport = $check;
                $method = substr($method, 0, $point);
            }
        }

        $http_method = $this->getHttpMethod();

        $api = new Route();
        $api->setMethod($method);
        $api->setQuery($query);
        $api->setHttpMethod($http_method);
        $api->setTransport($transport);

        $APPLICATION->RestartBuffer();

        try {
            echo $api->process();
        } catch (RestException $exception) {
            echo $exception->getMessage();
        }

        \CMain::FinalActions();
        die();
    }

    public function executeComponent()
    {
        $this->prepareComponent();

        $this->route();
    }
}