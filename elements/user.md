---
title: User Element 
permalink: elements/user/
---

### `getOrganizations( $criteria = [] )`

{% raw %}
```twig
{% set query = currentUser.getOrganizations({
    organizationType: [1,2]
}) %}
```

```php
$query = \Craft::$app->getUser()->getIdentity()->getOrganizations([
    organizationType => [1,2]
]);
```
{% endraw %}

### `getUserTypes( $criteria = [] )`

{% raw %}
```twig
{% set query = currentUser.getUserTypes({
    id: [1,2]
}) %}
```

```php
$query = \Craft::$app->getUser()->getIdentity()->getUserTypes([
    id => [1,2]
]);
```
{% endraw %}