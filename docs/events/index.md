# Events
The majority of events are triggered at the object level through first-party record or element events.

## Objects 
* [Organization Element](https://docs.craftcms.com/api/v3/craft-base-element.html#events)
* [Organization Type](https://www.yiiframework.com/doc/api/2.0/yii-db-activerecord#events)
* [Organization Type Site Settings](https://www.yiiframework.com/doc/api/2.0/yii-db-activerecord#events)
* [Settings](https://www.yiiframework.com/doc/api/2.0/yii-base-model#events)
* [User Type](https://www.yiiframework.com/doc/api/2.0/yii-db-activerecord#events)

## Views

### Organization Actions

#### `EVENT_REGISTER_ORGANIZATION_ACTIONS`
Triggered when available actions are being registered on the organization detail view

```php
    Event::on(
        \flipbox\organizations\cp\controllers\view\OrganizationsController::class,
        \flipbox\organizations\cp\controllers\view\OrganizationsController::EVENT_REGISTER_ORGANIZATION_ACTIONS,
        function (\flipbox\organizations\events\RegisterOrganizationActionsEvent $e) {
            // Manage `$e->destructiveActions` and `$e->miscActions`
    );
```
