# Upgrade

This document will tell you how to upgrade from one version to one other. 

# Upgrade from 0.2.x to 0.3.0

* You need to install a HTTP client that provides the virtual package 
[php-http/client-implementation](https://packagist.org/providers/php-http/client-implementation).

* (Optinal) Add the client's service name to `cspoo_swiftmailer_mailgun.http_client`. (Preferably with help from [HttplugBundle](https://github.com/php-http/HttplugBundle)) 

* You might have to remove your bootstrap.php.cache, clear your apcu cache and then re-run composer update. 

Read more about how to use HTTPlug here: http://php-http.readthedocs.io/en/latest/httplug/users.html
