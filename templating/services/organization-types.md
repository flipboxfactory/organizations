---
title: Templating 
permalink: templating/services/organization-types/
---

# Organization Types

The `craft.organizations.organizationTypes` tag enables interaction with [Organization Types].  Commonly, these tags are used to retrieve one or many [Organization Types].

### `find( $identifier )`

Returns an [Organization Element] by its Id or handle.

{% raw %}
```twig
{% set element = craft.organizations.organizationTypes.find(1) %}
{% set element = craft.organizations.organizationTypes.find('technology') %}
```
{% endraw %}


### `findByCondition( $condition )`
Returns a [Organization Type] or `null` if not found.

{% raw %}
```twig
{% set element = craft.organizations.organizationTypes.findByCondition({
    id: 1
}) %}
```
{% endraw %}


### `findByCriteria( $criteria )`
Returns a [Organization Type] or `null` if not found.

{% raw %}
```twig
{% set element = craft.organizations.organizationTypes.findByCriteria({
    id: 1,
    indexBy: 'id'
}) %}
```
{% endraw %}


### `findAllByCondition( $condition )`
Returns an array of [Organization Types].

{% raw %}
```twig
{% set element = craft.organizations.organizationTypes.findAllByCondition({
    id: 1
}) %}
```
{% endraw %}


### `findAllByCriteria( $criteria )`
Returns an array of [Organization Types].

{% raw %}
```twig
{% set element = craft.organizations.organizationTypes.findAllByCriteria({
    id: 1,
    indexBy: 'id'
}) %}
```
{% endraw %}


### `getQuery( $criteria )`

Returns a [Organization Type Query].

{% raw %}
```twig
{% set element = craft.organizations.organizationTypes.getQuery({
    id: 1
}) %}
```
{% endraw %}

[Organization Type Query]: /query/Organization-type-query/ "Organization Type Query"
[Organization Type]: /objects/organization-type/ "Organization Type"
[Organization Types]: /objects/organization-type/ "Organization Type"