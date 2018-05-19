---
title: Templating 
permalink: templating/services/elements/
---

# Elements

The `craft.organizations.elements` tag enables interaction with [Organization Elements].  Commonly, these tags are used to retrieve one or many [Organization Elements].

### `find( $identifier, int $siteId = null )`

Returns an [Organization Element] by its Id or slug.

{% raw %}
```twig
{% set element = craft.organizations.elements.find(1) %}
{% set element = craft.organizations.elements.find('flipbox') %}
```
{% endraw %}


### `getQuery( $criteria )`

Returns a [Organization Query].

{% raw %}
```twig
{% set element = craft.organizations.elements.getQuery({
    id: 1
}) %}
```
{% endraw %}

### `create( $config = [] )`

Creates (but does not save) a new [Organization Element].

{% raw %}
```twig
{% set element = craft.organizations.elements.create({
    title: 'Flipbox Digital'
}) %}
```
{% endraw %}

[Organization Query]: /query/OrganizationQuery/ "Organization Query"
[Organization Element]: /objects/organization/ "Organization Element"
[Organization Elements]: /objects/organization/ "Organization Element"