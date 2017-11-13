Asset-Manager-Plugin for Composer
=================================

Asset-Manager helps you to manage Assets ("large" binary files) that belong
to different instances (e.g. "production", "staging", "development") of your project.  
It does this by tracking your asset's defined by glob-patterns inside it's configuration.

It's best to think of it as a management frontend for a git repository which contains
all your assets (e.g. user generated content, videos, images, ...) of your project.  
This additional separation allows you to deploy entire projects (incl. assets) with
composer.

It uses a project-wide configuration stored in `composer.json`
and an environment-specific configuration stored in `asset-manager.json`.
Also it clones the configured repository to `.asset-manager`.

Installation / Usage
--------------------

Just add this to your `composer.json` and run `$ composer.phar update`
```$php
"require": {
	"fsmoak/asset-manager-plugin": "*"
},
```

After installation run `$ composer.phar asset-manager-init` to
create a configuration and begin using Asset-Manager.

Now you can use the following commands:
* `$ composer.phar asset-manager-init` to change the configuration
* `$ composer.phar asset-manager-commit` to commit changes from your assets to the repository
* `$ composer.phar asset-manager-deploy` to deploy files from the repository to your assets
* `$ composer.phar asset-manager-symlink` to symlink all unchanged assets in the repository
* `$ composer.phar asset-manager-copy` to copy all unchanged assets in the repository

You can use those in the `"script"` part of your `composer.json` to run Asset-Manager
commands alongside composer commands.

Requirements
------------

* PHP 5.6 or above `(tested with 7.1.8-1ubuntu1)`
* Git `(tested with git version 2.14.1)`



Authors
-------

- FSmoak  | [GitHub](https://github.com/fsmoak)  | <marieschreiber84@gmail.com> | [www.fluidweb.de](https://www.fluidweb.de)
