---
title: Organization Type 
permalink: objects/organization-type/
---

# Organization Type

While templating, you may access the following public attributes and methods on a [Organization Type].

## Properties
The following properties are available:


| Property              | Type                  | Description                   |
| :-----                | :-----                | :-----                        |
| `$id`                 | [integer]             | The organization type's Id    |
| `$name`               | [string]              | The organization type's title |

[integer]: http://www.php.net/language.types.integer
[string]: http://www.php.net/language.types.string


## Methods
The following methods are available:

### `object.getFieldLayout()`

Returns an [Field Layout].

{% raw %}
```twig
{% set object = craft.organizations.organizationTypes.find('technology') %} // Get an Organization Type
{% set fieldLayout = object.getFieldLayout() %}
<p>{{ fieldLayout.id }}</p>
```
{% endraw %}


### `object.getSiteSettings()`

Returns an array of [Organization Type Site Settings].

{% raw %}
```twig
{% set object = craft.organizations.organizationTypes.find('technology') %} // Get an Organization Type
{% set siteSettings = object.getSiteSettings() %}
<ul>
{% for site in siteSettings %} 
    <li>{{ site.hasUrls }} - {{ site.getUriFormat() }} - {{ site.getTemplate() }}</li>
{% endfor %}
</ul>
```
{% endraw %}


[Organization Type]: /objects/organization-type/ "Organization Type"
[Field Layout]: https://docs.craftcms.com/api/v3/craft-models-fieldlayout.html "Field Layout"
[Organization Type Site Settings]: /object/organization-type-site-settings/ "Organization Type Site Settings"
