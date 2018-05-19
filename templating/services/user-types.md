---
title: Templating 
permalink: templating/services/user-types/
---

# User Types

The `craft.organizations.userTypes` tag enables interaction with [User Types].  Commonly, these tags are used to retrieve one or many [User Types].

### `find( $identifier )`

Returns an [User Type] by its Id or handle.

{% raw %}
```twig
{% set element = craft.organizations.userTypes.find(1) %}
{% set element = craft.organizations.userTypes.find('president') %}
```
{% endraw %}


### `findByCondition( $condition )`
Returns a [User Type] or `null` if not found.

{% raw %}
```twig
{% set element = craft.organizations.userTypes.findByCondition({
    id: 1
}) %}
```
{% endraw %}


### `findByCriteria( $criteria )`
Returns a [User Type] or `null` if not found.

{% raw %}
```twig
{% set element = craft.organizations.userTypes.findByCriteria({
    id: 1,
    indexBy: 'id'
}) %}
```
{% endraw %}


### `findAllByCondition( $condition )`
Returns an array of [User Types].

{% raw %}
```twig
{% set element = craft.organizations.userTypes.findAllByCondition({
    id: 1
}) %}
```
{% endraw %}


### `findAllByCriteria( $criteria )`
Returns an array of [User Types].

{% raw %}
```twig
{% set element = craft.organizations.userTypes.findAllByCriteria({
    id: 1,
    indexBy: 'id'
}) %}
```
{% endraw %}

### `getQuery( $criteria )`

Returns a [User Type Query].

{% raw %}
```twig
{% set element = craft.organizations.userTypes.getQuery({
    id: 1
}) %}
```
{% endraw %}

[User Type Query]: /query/user-type-query/ "User Type Query"
[User Type]: /objects/user-type/ "User Type"
[User Types]: /objects/user-type/ "User Type"