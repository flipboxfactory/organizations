---
title: User Query 
permalink: queries/user/
---

### `query.setOrganization( $value )`

{% raw %}
```twig
{% set query = craft.users.find({
    organization: {
        id: 1,
        organizationType: [1,2],
        userType: [1,2]
    }
}) %}
```

```php
$query = User::find()->setOrganization([
    id => 1,
    organizationType => [1,2],
    userType => [1,2]
]);
```
{% endraw %}