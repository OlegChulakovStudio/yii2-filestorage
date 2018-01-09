#Слушатели

##Создание слушателя

Система компонента сохранения работает на основе шаблона `Observer`.

Все слушатели должны реализовывать ListenerInterface, только так они могут подписаться на наблюдателя.
####Шаблон `Observer` имеет две сущности: 
 - слушатель - реализует `ListenerInterface`;
 - наблюдатель - реализует `ObserverInterface`.

Слушатели подписываются на события наблюдателя, после чего каждый слушатель получает информацию о файле в момент сохранения файла. Каждый слушатель может производить над файлом свои действия, в результате чего производится видоизменение файла или различные побочные дейтсвия.

Интерфейс слушателя реализует такой функционал:
```php
interface ListenerInterface
{
    /**
     * Присоединение к Observer
     *
     * @param ObserverInterface $observer
     * @return mixed
     */
    public function attach(ObserverInterface $observer);
}
```

Функция `attach` позволяет навеситься на наблюдателя и получать от него нужные события с нужной информацией.

Базовая реализация слушателя:
```php
class Manager implements ListenerInterface {
    /**
     * Событие на сохранение
     *
     * @param Event $event
     * @throws \Exception
     */
    public function handle(Event $event)
    {
       // работа с данными из слушателя
    }

    /**
     * Присоединение к Observer
     *
     * @param ObserverInterface $observer
     */
    public function attach(ObserverInterface $observer)
    {
        $observer->on(Event::SAVE_EVENT, [$this, 'handle']);
    }
}
```
####, где: 
- `Event::SAVE_EVENT` - тип события, которого хотим получать и обрабатывать события;
- `handle` - срабатываемый `callback` (типа `callable`), вызываемый при срабатывании установленного события;
- `attach` - реализация интерфейса `ListenerInterface`;
- `$observer->on()` - функция прикрепления слушателя к наблюдателю. Аргументы: тип события, объект типа `callable`.

##Конфигурирование слушателя.

Каждый слушатель может конфигурироваться. 

####Конфигурирование обеспечивает репозитории: 
- `UploadedFile`;
- `RemoteUploadedFile`.

Каждый из репозиториев реализует интерфейс `UploadInterface`, где есть функция: 

```php
    /**
     * Конфигурирование загрузчика
     *
     * @param array $config
     * @return mixed
     */
    public function configure($config);
```
####, где:
- `$config` - конфигурация, коей будут конфигурироваться слушатели;
- `configure` - функция, которая конфигурирует слушателей.

## Конфигурирование  репозиториев

При конфигурировании репозитория можно задать нужные параметры как слушателям, так и событиям. 
Поле для настроек репозиториев - `repositoryOptions`. Данное поле имеет два внутренних поля: 
- `listeners`;
- `events`.

Поле `listeners` содержит в себе массив конфигураций слушателей, в каждой ячейке массива описывается подключение слушателя и его конфигурации.

Аналогично слушателям, события можно также подключать заранее. 
```php
 'events' => [
        Event::SAVE_EVENT => [
             function (Event $event) {
                   // реализация срабатываемого события
             }
        ]
  ]
```

Примерная реализация подключения отношения:
```php
function behaviors()
{
    return [
        [
            'class' => FileUploadBehavior::className(),
            'attribute' => 'image',
            'repository' => UploadedFile::class,
            'repositoryOptions' => [
                'listeners' =>
                    [
                        [
                            'class' => ListenerExample::class,
                            // конфигурация слушателя
                        ],
                        [
                            'class' => ListenerExample::class,
                            // конфигурация слушателя
                        ]
                    ],
                'events' => [
                    Event::SAVE_EVENT => [
                        function (Event $event) {
                            // реализация срабатываемого события
                        },
                        function (Event $event) {
                            // реализация срабатываемого события
                        }
                    ]
                ]
            ]
        ],
    ];
}
```