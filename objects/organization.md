---
title: Organization 
permalink: objects/organization/
---

# Organization

While templating, you may access the following public attributes and methods on a [Organization Element].

## Properties
All of the standard [Element](https://docs.craftcms.com/api/v3/craft-base-element.html) public properties are available.  In addition, the following properties are also available:

| Property              | Type                  | Description                       |
| :-----                | :-----                | :-----                            |
| `$state`              | [string], [null]      | The organization's state (custom defined) |
| `$dateJoined`         | [DateTime], [null]    | The date the organization joined  |

[integer]: http://www.php.net/language.types.integer
[string]: http://www.php.net/language.types.string
[null]: http://www.php.net/language.types.null
[DateTime]: http://php.net/manual/en/class.datetime.php

## Methods
The following methods are available:

### `element.getUsers( $criteria = [] )`

Returns an [User Query].

{% raw %}
```twig
{% set element = craft.organizations.elements.find('flipbox') %} // Get an Organization Element
{% set users = element.getUsers({status: null}).all() %}
<ul>
{% for user in users %}
    <li>{{ user.id }} - {{ user.getFullName() }}</li>
{% endfor %}
</ul>
```
{% endraw %}


### `element.getTypes( $criteria = [] )`

Returns an [Organization Type Query].

{% raw %}
```twig
{% set element = craft.organizations.elements.find('flipbox') %} // Get an Organization Element
{% set types = element.getTypes({status: null}).all() %}
<ul>
{% for type in types %}
    <li>{{ type.id }} - {{ type.name }}</li>
{% endfor %}
</ul>
```
{% endraw %}


### `element.getType( $identifier )`

Returns an [Organization Type].

{% raw %}
```twig
{% set element = craft.organizations.elements.find('flipbox') %} // Get an Organization Element
{% set type = element.getType('technology') %}
<p>{{ type.id }} - <strong>{{ type.name }}</strong></p>
```
{% endraw %}


### `element.getPrimaryType()`

Returns an [Organization Type].

{% raw %}
```twig
{% set element = craft.organizations.elements.find('flipbox') %} // Get an Organization Element
{% set type = element.getPrimaryType() %}
<p>{{ type.id }} - <strong>{{ type.name }}</strong></p>
```
{% endraw %}

[User Query]: https://docs.craftcms.com/api/v3/craft-elements-db-userquery.html "User Query"
[Organization Type Query]: /query/organization-type-query/ "Organization Type Query"
[Organization Type]: /objects/organization-type/ "Organization Type"
[Organization Element]: /objects/organization/ "Organization"


