---
title: Templating 
permalink: templating/
---

{% assign variableUrl = page.url|append:'tags/' %}
### Tags

[Twig][] [Tags][] are the interface when interacting with Rating data.  


---

{% assign filterUrl = page.url|append:'filters/' %}
### Filters

[Twig][] [Filters][] allow for extra manipulation of the data before it is rendered.



[Twig]: http://twig.sensiolabs.org/ "Twig is a modern template engine for PHP"
[Tags]: http://twig.sensiolabs.org/doc/tags/index.html "Twig Tags"
[Filters]: http://twig.sensiolabs.org/doc/filters/index.html "Twig Filters"