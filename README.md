# prominado.rest

Модуль для организации REST-сервиса в проектах на 1С-Битрикс.

Модуль обрабатывает адреса вида ``http://prominado.ru/rest/method.name``

Чтобы зарегистрировать обработчик, необходимо зарегистрировать обработчик:

````php
<?php

$event = \Bitrix\Main\EventManager::getInstance();
$event->addEventHandler('prominado.rest', 'onRestMethodBuildDescription', 'restServiceDescription');

function restServiceDescription()
{
    return [
        'data.get' => [
            'allow_methods' => [],
            'callback'      => ['\\Prominado\\Events\\Rest', 'dataGet']
        ],
        'data.update' => [
            'allow_methods' => ['POST'],
            'authenticator' => ['\\Prominado\\Events\\Rest', 'isAuthorized'],
            'callback'      => ['\\Prominado\\Events\\Rest', 'dataUpdate']
        ],
    ];
}
````

Указанные выше методы будут доступны по адресам:
``http://prominado.ru/rest/data.get`` и ``http://prominado.ru/rest/data.update`` 

Код обработчика:

```php
<?php

namespace Prominado\Events;

use Prominado\Rest\Request;
use Prominado\Rest\RestException;

class Rest
{
    public function dataGet(Request $request)
    {
        $userId = $request->getQuery('id');
        
        if(!$userId) {
            throw new RestException('No user_id passed');    
        }
        
        $request->withStatus(200);
        $request->withHeader('X-Token', 'prominado-web-access');

        return ['user' => ['NAME' => 'Prominado']];
    }
    
    public function dataUpdate(Request $request)
    {
        $userId = $request->getQuery('id');
        $fields = $request->getQuery('fields');
        
        if(!$userId) {
            throw new RestException('No user_id passed');    
        }
        
        if(!$fields) {
            throw new RestException('No fields passed');    
        }
        
        $request->withStatus(200);
        $request->withHeader('X-Token', 'prominado-web-access');

        return ['user' => ['NAME' => 'Prominado']];
    }
    
    public function isAuthorized(Request $request) 
    {
        $server = $request->getServer();
        
        preg_match('/Bearer\s(.*)/', $server['REMOTE_USER'], $matches);
        if ($matches[1]) {
            return true;
        }
    
        return false;
    }
}

```

## Roadmap
- [x] Метод для проверки авторизации
- [ ] Права доступа (без ограничения по времени)
- [ ] Права доступа (oauth2)