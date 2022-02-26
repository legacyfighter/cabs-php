[1mdiff --git a/composer.json b/composer.json[m
[1mindex 3ef83b8..779b5fb 100644[m
[1m--- a/composer.json[m
[1m+++ b/composer.json[m
[36m@@ -18,6 +18,8 @@[m
         "doctrine/doctrine-bundle": "^2.5",[m
         "doctrine/doctrine-migrations-bundle": "^3.2",[m
         "doctrine/orm": "^2.10",[m
[32m+[m[32m        "pheature/inmemory-toggle": "^0.3.0",[m
[32m+[m[32m        "pheature/symfony-toggle": "^0.3.2",[m
         "symfony/console": "5.3.*",[m
         "symfony/dotenv": "5.3.*",[m
         "symfony/flex": "^1.3.1",[m
[1mdiff --git a/composer.lock b/composer.lock[m
[1mindex 1581168..179fa49 100644[m
[1m--- a/composer.lock[m
[1m+++ b/composer.lock[m
[36m@@ -4,7 +4,7 @@[m
         "Read more about it at https://getcomposer.org/doc/01-basic-usage.md#installing-dependencies",[m
         "This file is @generated automatically"[m
     ],[m
[31m-    "content-hash": "2db799592ac048eeb0280a7f9a8de843",[m
[32m+[m[32m    "content-hash": "d19b4c84a274cb0db467efec117b6f4e",[m
     "packages": [[m
         {[m
             "name": "composer/package-versions-deprecated",[m
[36m@@ -1587,6 +1587,397 @@[m
             ],[m
             "time": "2021-12-19T18:06:55+00:00"[m
         },[m
[32m+[m[32m        {[m
[32m+[m[32m            "name": "pheature/inmemory-toggle",[m
[32m+[m[32m            "version": "0.3.0",[m
[32m+[m[32m            "source": {[m
[32m+[m[32m                "type": "git",[m
[32m+[m[32m                "url": "https://github.com/pheature-flags/inmemory-toggle.git",[m
[32m+[m[32m                "reference": "89d1eff418bb150a23c4050902a6304f949ff245"[m
[32m+[m[32m            },[m
[32m+[m[32m            "dist": {[m
[32m+[m[32m                "type": "zip",[m
[32m+[m[32m                "url": "https://api.github.com/repos/pheature-flags/inmemory-toggle/zipball/89d1eff418bb150a23c4050902a6304f949ff245",[m
[32m+[m[32m                "reference": "89d1eff418bb150a23c4050902a6304f949ff245",[m
[32m+[m[32m                "shasum": ""[m
[32m+[m[32m            },[m
[32m+[m[32m            "require": {[m
[32m+[m[32m                "pheature/toggle-core": "^0.3",[m
[32m+[m[32m                "pheature/toggle-model": "^0.3",[m
[32m+[m[32m                "php": "^7.4|>=8.0",[m
[32m+[m[32m                "webmozart/assert": "^1.10"[m
[32m+[m[32m            },[m
[32m+[m[32m            "require-dev": {[m
[32m+[m[32m                "icanhazstring/composer-unused": "^0.7.5",[m
[32m+[m[32m                "infection/infection": "^0.25",[m
[32m+[m[32m                "phpcompatibility/php-compatibility": "^9.3",[m
[32m+[m[32m                "phpro/grumphp": "^1.0",[m
[32m+[m[32m                "phpstan/extension-installer": "^1.1",[m
[32m+[m[32m                "phpstan/phpstan": "^1.0",[m
[32m+[m[32m                "phpstan/phpstan-webmozart-assert": "^1.0",[m
[32m+[m[32m                "phpunit/phpunit": "^8.0 || ^9.0",[m
[32m+[m[32m                "roave/infection-static-analysis-plugin": "^1.8",[m
[32m+[m[32m                "squizlabs/php_codesniffer": "^3.4",[m
[32m+[m[32m                "symfony/var-dumper": "^4.2 || ^5.0",[m
[32m+[m[32m                "vimeo/psalm": "^4.4"[m
[32m+[m[32m            },[m
[32m+[m[32m            "type": "library",[m
[32m+[m[32m            "autoload": {[m
[32m+[m[32m                "psr-4": {[m
[32m+[m[32m                    "Pheature\\InMemory\\Toggle\\": "src"[m
[32m+[m[32m                }[m
[32m+[m[32m            },[m
[32m+[m[32m            "notification-url": "https://packagist.org/downloads/",[m
[32m+[m[32m            "license": [[m
[32m+[m[32m                "BSD-3-Clause"[m
[32m+[m[32m            ],[m
[32m+[m[32m            "authors": [[m
[32m+[m[32m                {[m
[32m+[m[32m                    "name": "kpicaza"[m
[32m+[m[32m                },[m
[32m+[m[32m                {[m
[32m+[m[32m                    "name": "pcs289"[m
[32m+[m[32m                },[m
[32m+[m[32m                {[m
[32m+[m[32m                    "name": "xserrat"[m
[32m+[m[32m                }[m
[32m+[m[32m            ],[m
[32m+[m[32m            "description": "Pheature flags In Memory toggle implementation library.",[m
[32m+[m[32m            "keywords": [[m
[32m+[m[32m                "feature-flags",[m
[32m+[m[32m                "feature-toggle"[m
[32m+[m[32m            ],[m
[32m+[m[32m            "support": {[m
[32m+[m[32m                "issues": "https://github.com/pheature-flags/inmemory-toggle/issues",[m
[32m+[m[32m                "source": "https://github.com/pheature-flags/inmemory-toggle/tree/0.3.0"[m
[32m+[m[32m            },[m
[32m+[m[32m            "funding": [[m
[32m+[m[32m                {[m
[32m+[m[32m                    "url": "https://github.com/pheature-flags",[m
[32m+[m[32m                    "type": "github"[m
[32m+[m[32m                }[m
[32m+[m[32m            ],[m
[32m+[m[32m            "time": "2021-11-20T17:51:56+00:00"[m
[32m+[m[32m        },[m
[32m+[m[32m        {[m
[32m+[m[32m            "name": "pheature/symfony-toggle",[m
[32m+[m[32m            "version": "0.3.2",[m
[32m+[m[32m            "source": {[m
[32m+[m[32m                "type": "git",[m
[32m+[m[32m                "url": "https://github.com/pheature-flags/symfony-toggle.git",[m
[32m+[m[32m                "reference": "3a4686727e682392386dc02de27efab072c9b130"[m
[32m+[m[32m            },[m
[32m+[m[32m            "dist": {[m
[32m+[m[32m                "type": "zip",[m
[32m+[m[32m                "url": "https://api.github.com/repos/pheature-flags/symfony-toggle/zipball/3a4686727e682392386dc02de27efab072c9b130",[m
[32m+[m[32m                "reference": "3a4686727e682392386dc02de27efab072c9b130",[m
[32m+[m[32m                "shasum": ""[m
[32m+[m[32m            },[m
[32m+[m[32m            "require": {[m
[32m+[m[32m                "pheature/toggle-core": "^0.3",[m
[32m+[m[32m                "pheature/toggle-crud-psr11-factories": "^0.3",[m
[32m+[m[32m                "pheature/toggle-model": "^0.3",[m
[32m+[m[32m                "php": "^7.4|>=8.0",[m
[32m+[m[32m                "symfony/framework-bundle": "~5.0"[m
[32m+[m[32m            },[m
[32m+[m[32m            "require-dev": {[m
[32m+[m[32m                "doctrine/dbal": ">=2.6 || ^3.0.0",[m
[32m+[m[32m                "icanhazstring/composer-unused": "^0.7.5",[m
[32m+[m[32m                "infection/infection": "^0.25",[m
[32m+[m[32m                "pheature/dbal-toggle": "^0.3",[m
[32m+[m[32m                "pheature/inmemory-toggle": "^0.3",[m
[32m+[m[32m                "pheature/toggle-crud": "^0.3",[m
[32m+[m[32m                "pheature/toggle-crud-psr7-api": "^0.3",[m
[32m+[m[32m                "phpcompatibility/php-compatibility": "^9.3",[m
[32m+[m[32m                "phpro/grumphp": "^1.0",[m
[32m+[m[32m                "phpstan/extension-installer": "^1.1",[m
[32m+[m[32m                "phpstan/phpstan": "^1.0",[m
[32m+[m[32m                "phpstan/phpstan-webmozart-assert": "^1.0",[m
[32m+[m[32m                "phpunit/phpunit": "^8.0 || ^9.0",[m
[32m+[m[32m                "roave/infection-static-analysis-plugin": "^1.8",[m
[32m+[m[32m                "squizlabs/php_codesniffer": "^3.4",[m
[32m+[m[32m                "symfony/var-dumper": "^4.2 || ^5.0",[m
[32m+[m[32m                "vimeo/psalm": "^4.4"[m
[32m+[m[32m            },[m
[32m+[m[32m            "suggest": {[m
[32m+[m[32m                "pheature/dbal-toggle": "Allows using Dbal toggle management implementation.",[m
[32m+[m[32m                "pheature/inmemory-toggle": "Allows using Inmemory toggle management implementation.",[m
[32m+[m[32m                "pheature/toggle-crud": "Allows using toggle management CRUD implementation.",[m
[32m+[m[32m                "pheature/toggle-crud-psr7-api": "Allows using toggle management CRUD HTTP API."[m
[32m+[m[32m            },[m
[32m+[m[32m            "type": "library",[m
[32m+[m[32m            "extra": {[m
[32m+[m[32m                "laravel": {[m
[32m+[m[32m                    "providers": [[m
[32m+[m[32m                        "Pheature\\Community\\Symfony\\ToggleProvider"[m
[32m+[m[32m                    ],[m
[32m+[m[32m                    "aliases": {[m
[32m+[m[32m                        "Toggle": "Pheature\\Community\\Symfony\\Toggle"[m
[32m+[m[32m                    }[m
[32m+[m[32m                }[m
[32m+[m[32m            },[m
[32m+[m[32m            "autoload": {[m
[32m+[m[32m                "psr-4": {[m
[32m+[m[32m                    "Pheature\\Community\\Symfony\\": "src"[m
[32m+[m[32m                }[m
[32m+[m[32m            },[m
[32m+[m[32m            "notification-url": "https://packagist.org/downloads/",[m
[32m+[m[32m            "license": [[m
[32m+[m[32m                "BSD-3-Clause"[m
[32m+[m[32m            ],[m
[32m+[m[32m            "authors": [[m
[32m+[m[32m                {[m
[32m+[m[32m                    "name": "kpicaza"[m
[32m+[m[32m                },[m
[32m+[m[32m                {[m
[32m+[m[32m                    "name": "pcs289"[m
[32m+[m[32m                },[m
[32m+[m[32m                {[m
[32m+[m[32m                    "name": "xserrat"[m
[32m+[m[32m                }[m
[32m+[m[32m            ],[m
[32m+[m[32m            "description": "Pheature flags Symfony toggle management bundle.",[m
[32m+[m[32m            "keywords": [[m
[32m+[m[32m                "feature-flags",[m
[32m+[m[32m                "feature-toggle",[m
[32m+[m[32m                "symfony"[m
[32m+[m[32m            ],[m
[32m+[m[32m            "support": {[m
[32m+[m[32m                "issues": "https://github.com/pheature-flags/symfony-toggle/issues",[m
[32m+[m[32m                "source": "https://github.com/pheature-flags/symfony-toggle/tree/0.3.2"[m
[32m+[m[32m            },[m
[32m+[m[32m            "funding": [[m
[32m+[m[32m                {[m
[32m+[m[32m                    "url": "https://github.com/pheature-flags",[m
[32m+[m[32m                    "type": "github"[m
[32m+[m[32m                }[m
[32m+[m[32m            ],[m
[32m+[m[32m            "time": "2022-02-10T12:31:55+00:00"[m
[32m+[m[32m        },[m
[32m+[m[32m        {[m
[32m+[m[32m            "name": "pheature/toggle-core",[m
[32m+[m[32m            "version": "0.3.0",[m
[32m+[m[32m            "source": {[m
[32m+[m[32m                "type": "git",[m
[32m+[m[32m                "url": "https://github.com/pheature-flags/toggle-core.git",[m
[32m+[m[32m                "reference": "8089a80579b17e8b24038b5e577dfd6174cf1599"[m
[32m+[m[32m            },[m
[32m+[m[32m            "dist": {[m
[32m+[m[32m                "type": "zip",[m
[32m+[m[32m                "url": "https://api.github.com/repos/pheature-flags/toggle-core/zipball/8089a80579b17e8b24038b5e577dfd6174cf1599",[m
[32m+[m[32m                "reference": "8089a80579b17e8b24038b5e577dfd6174cf1599",[m
[32m+[m[32m                "shasum": ""[m
[32m+[m[32m            },[m
[32m+[m[32m            "require": {[m
[32m+[m[32m                "php": "^7.4|>=8.0"[m
[32m+[m[32m            },[m
[32m+[m[32m            "require-dev": {[m
[32m+[m[32m                "icanhazstring/composer-unused": "^0.7.5",[m
[32m+[m[32m                "infection/infection": "^0.25",[m
[32m+[m[32m                "phpcompatibility/php-compatibility": "^9.3",[m
[32m+[m[32m                "phpro/grumphp": "^1.0",[m
[32m+[m[32m                "phpstan/phpstan": "^1.0",[m
[32m+[m[32m                "phpunit/phpunit": "^8.0 || ^9.0",[m
[32m+[m[32m                "roave/infection-static-analysis-plugin": "^1.8",[m
[32m+[m[32m                "squizlabs/php_codesniffer": "^3.4",[m
[32m+[m[32m                "symfony/var-dumper": "^4.2 || ^5.0",[m
[32m+[m[32m                "vimeo/psalm": "^4.4"[m
[32m+[m[32m            },[m
[32m+[m[32m            "type": "library",[m
[32m+[m[32m            "autoload": {[m
[32m+[m[32m                "psr-4": {[m
[32m+[m[32m                    "Pheature\\Core\\Toggle\\": "src"[m
[32m+[m[32m                }[m
[32m+[m[32m            },[m
[32m+[m[32m            "notification-url": "https://packagist.org/downloads/",[m
[32m+[m[32m            "license": [[m
[32m+[m[32m                "BSD-3-Clause"[m
[32m+[m[32m            ],[m
[32m+[m[32m            "authors": [[m
[32m+[m[32m                {[m
[32m+[m[32m                    "name": "kpicaza"[m
[32m+[m[32m                },[m
[32m+[m[32m                {[m
[32m+[m[32m                    "name": "pcs289"[m
[32m+[m[32m                },[m
[32m+[m[32m                {[m
[32m+[m[32m                    "name": "xserrat"[m
[32m+[m[32m                }[m
[32m+[m[32m            ],[m
[32m+[m[32m            "description": "Pheature flags toggle core library.",[m
[32m+[m[32m            "keywords": [[m
[32m+[m[32m                "feature-flags",[m
[32m+[m[32m                "feature-toggle"[m
[32m+[m[32m            ],[m
[32m+[m[32m            "support": {[m
[32m+[m[32m                "issues": "https://github.com/pheature-flags/toggle-core/issues",[m
[32m+[m[32m                "source": "https://github.com/pheature-flags/toggle-core/tree/0.3.0"[m
[32m+[m[32m            },[m
[32m+[m[32m            "funding": [[m
[32m+[m[32m                {[m
[32m+[m[32m                    "url": "https://github.com/pheature-flags",[m
[32m+[m[32m                    "type": "github"[m
[32m+[m[32m                }[m
[32m+[m[32m            ],[m
[32m+[m[32m            "time": "2021-11-20T17:33:42+00:00"[m
[32m+[m[32m        },[m
[32m+[m[32m        {[m
[32m+[m[32m            "name": "pheature/toggle-crud-psr11-factories",[m
[32m+[m[32m            "version": "0.3.0",[m
[32m+[m[32m            "source": {[m
[32m+[m[32m                "type": "git",[m
[32m+[m[32m                "url": "https://github.com/pheature-flags/toggle-crud-psr11-factories.git",[m
[32m+[m[32m                "reference": "0c44428b6de585a15f432675e9aaf65704dae06d"[m
[32m+[m[32m            },[m
[32m+[m[32m            "dist": {[m
[32m+[m[32m                "type": "zip",[m
[32m+[m[32m                "url": "https://api.github.com/repos/pheature-flags/toggle-crud-psr11-factories/zipball/0c44428b6de585a15f432675e9aaf65704dae06d",[m
[32m+[m[32m                "reference": "0c44428b6de585a15f432675e9aaf65704dae06d",[m
[32m+[m[32m                "shasum": ""[m
[32m+[m[32m            },[m
[32m+[m[32m            "require": {[m
[32m+[m[32m                "pheature/toggle-core": "^0.3",[m
[32m+[m[32m                "pheature/toggle-model": "^0.3",[m
[32m+[m[32m                "php": "^7.4|>=8.0"[m
[32m+[m[32m            },[m
[32m+[m[32m            "require-dev": {[m
[32m+[m[32m                "icanhazstring/composer-unused": "^0.7.5",[m
[32m+[m[32m                "infection/infection": "^0.25",[m
[32m+[m[32m                "pheature/dbal-toggle": "^0.3",[m
[32m+[m[32m                "pheature/inmemory-toggle": "^0.3",[m
[32m+[m[32m                "pheature/php-sdk": "^0.3",[m
[32m+[m[32m                "pheature/toggle-crud": "^0.3",[m
[32m+[m[32m                "phpcompatibility/php-compatibility": "^9.3",[m
[32m+[m[32m                "phpro/grumphp": "^1.0",[m
[32m+[m[32m                "phpstan/extension-installer": "^1.1",[m
[32m+[m[32m                "phpstan/phpstan": "^1.0",[m
[32m+[m[32m                "phpstan/phpstan-webmozart-assert": "^1.0",[m
[32m+[m[32m                "phpunit/phpunit": "^8.0 || ^9.0",[m
[32m+[m[32m                "roave/infection-static-analysis-plugin": "^1.8",[m
[32m+[m[32m                "squizlabs/php_codesniffer": "^3.4",[m
[32m+[m[32m                "symfony/var-dumper": "^4.2 || ^5.0",[m
[32m+[m[32m                "vimeo/psalm": "^4.4"[m
[32m+[m[32m            },[m
[32m+[m[32m            "suggest": {[m
[32m+[m[32m                "pheature/dbal-toggle": "Dbal toggle implementation",[m
[32m+[m[32m                "pheature/inmemory-toggle": "In memory toggle implementation"[m
[32m+[m[32m            },[m
[32m+[m[32m            "type": "library",[m
[32m+[m[32m            "extra": {[m
[32m+[m[32m                "laminas": {[m
[32m+[m[32m                    "config-provider": "Pheature\\Crud\\Psr11\\Toggle\\Container\\ConfigProvider"[m
[32m+[m[32m                }[m
[32m+[m[32m            },[m
[32m+[m[32m            "autoload": {[m
[32m+[m[32m                "psr-4": {[m
[32m+[m[32m                    "Pheature\\Crud\\Psr11\\Toggle\\": "src"[m
[32m+[m[32m                }[m
[32m+[m[32m            },[m
[32m+[m[32m            "notification-url": "https://packagist.org/downloads/",[m
[32m+[m[32m            "license": [[m
[32m+[m[32m                "BSD-3-Clause"[m
[32m+[m[32m            ],[m
[32m+[m[32m            "authors": [[m
[32m+[m[32m                {[m
[32m+[m[32m                    "name": "kpicaza"[m
[32m+[m[32m                },[m
[32m+[m[32m                {[m
[32m+[m[32m                    "name": "pcs289"[m
[32m+[m[32m                },[m
[32m+[m[32m                {[m
[32m+[m[32m                    "name": "xserrat"[m
[32m+[m[32m                }[m
[32m+[m[32m            ],[m
[32m+[m[32m            "description": "Pheature flags toggle CRUD PSR-11 Factories.",[m
[32m+[m[32m            "keywords": [[m
[32m+[m[32m                "feature-flags",[m
[32m+[m[32m                "feature-toggle"[m
[32m+[m[32m            ],[m
[32m+[m[32m            "support": {[m
[32m+[m[32m                "issues": "https://github.com/pheature-flags/toggle-crud-psr11-factories/issues",[m
[32m+[m[32m                "source": "https://github.com/pheature-flags/toggle-crud-psr11-factories/tree/0.3.0"[m
[32m+[m[32m            },[m
[32m+[m[32m            "funding": [[m
[32m+[m[32m                {[m
[32m+[m[32m                    "url": "https://github.com/pheature-flags",[m
[32m+[m[32m                    "type": "github"[m
[32m+[m[32m                }[m
[32m+[m[32m            ],[m
[32m+[m[32m            "time": "2021-11-21T18:51:42+00:00"[m
[32m+[m[32m        },[m
[32m+[m[32m        {[m
[32m+[m[32m            "name": "pheature/toggle-model",[m
[32m+[m[32m            "version": "0.3.0",[m
[32m+[m[32m            "source": {[m
[32m+[m[32m                "type": "git",[m
[32m+[m[32m                "url": "https://github.com/pheature-flags/toggle-model.git",[m
[32m+[m[32m                "reference": "3370f585894284e894eed6a00fc2b8b35e2f364e"[m
[32m+[m[32m            },[m
[32m+[m[32m            "dist": {[m
[32m+[m[32m                "type": "zip",[m
[32m+[m[32m                "url": "https://api.github.com/repos/pheature-flags/toggle-model/zipball/3370f585894284e894eed6a00fc2b8b35e2f364e",[m
[32m+[m[32m                "reference": "3370f585894284e894eed6a00fc2b8b35e2f364e",[m
[32m+[m[32m                "shasum": ""[m
[32m+[m[32m            },[m
[32m+[m[32m            "require": {[m
[32m+[m[32m                "pheature/toggle-core": "^0.3",[m
[32m+[m[32m                "php": "^7.4|>=8.0"[m
[32m+[m[32m            },[m
[32m+[m[32m            "require-dev": {[m
[32m+[m[32m                "icanhazstring/composer-unused": "^0.7.5",[m
[32m+[m[32m                "infection/infection": "^0.25",[m
[32m+[m[32m                "phpcompatibility/php-compatibility": "^9.3",[m
[32m+[m[32m                "phpro/grumphp": "^1.0",[m
[32m+[m[32m                "phpstan/phpstan": "^1.0",[m
[32m+[m[32m                "phpunit/phpunit": "^8.0 || ^9.0",[m
[32m+[m[32m                "roave/infection-static-analysis-plugin": "^1.8",[m
[32m+[m[32m                "squizlabs/php_codesniffer": "^3.4",[m
[32m+[m[32m                "symfony/var-dumper": "^4.2 || ^5.0",[m
[32m+[m[32m                "vimeo/psalm": "^4.4"[m
[32m+[m[32m            },[m
[32m+[m[32m            "type": "library",[m
[32m+[m[32m            "extra": {[m
[32m+[m[32m                "laminas": {[m
[32m+[m[32m                    "config-provider": "Pheature\\Model\\Toggle\\Container\\ConfigProvider"[m
[32m+[m[32m                }[m
[32m+[m[32m            },[m
[32m+[m[32m            "autoload": {[m
[32m+[m[32m                "psr-4": {[m
[32m+[m[32m                    "Pheature\\Model\\Toggle\\": "src"[m
[32m+[m[32m                }[m
[32m+[m[32m            },[m
[32m+[m[32m            "notification-url": "https://packagist.org/downloads/",[m
[32m+[m[32m            "license": [[m
[32m+[m[32m                "BSD-3-Clause"[m
[32m+[m[32m            ],[m
[32m+[m[32m            "authors": [[m
[32m+[m[32m                {[m
[32m+[m[32m                    "name": "kpicaza"[m
[32m+[m[32m                },[m
[32m+[m[32m                {[m
[32m+[m[32m                    "name": "pcs289"[m
[32m+[m[32m                },[m
[32m+[m[32m                {[m
[32m+[m[32m                    "name": "xserrat"[m
[32m+[m[32m                }[m
[32m+[m[32m            ],[m
[32m+[m[32m            "description": "Pheature flags toggle model implementation library.",[m
[32m+[m[32m            "keywords": [[m
[32m+[m[32m                "feature-flags",[m
[32m+[m[32m                "feature-toggle"[m
[32m+[m[32m            ],[m
[32m+[m[32m            "support": {[m
[32m+[m[32m                "issues": "https://github.com/pheature-flags/toggle-model/issues",[m
[32m+[m[32m                "source": "https://github.com/pheature-flags/toggle-model/tree/0.3.0"[m
[32m+[m[32m            },[m
[32m+[m[32m            "funding": [[m
[32m+[m[32m                {[m
[32m+[m[32m                    "url": "https://github.com/pheature-flags",[m
[32m+[m[32m                    "type": "github"[m
[32m+[m[32m                }[m
[32m+[m[32m            ],[m
[32m+[m[32m            "time": "2021-11-20T17:43:14+00:00"[m
[32m+[m[32m        },[m
         {[m
             "name": "psr/cache",[m
             "version": "2.0.0",[m
[36m@@ -4845,6 +5236,64 @@[m
                 }[m
             ],[m
             "time": "2021-07-29T06:20:01+00:00"[m
[32m+[m[32m        },[m
[32m+[m[32m        {[m
[32m+[m[32m            "name": "webmozart/assert",[m
[32m+[m[32m            "version": "1.10.0",[m
[32m+[m[32m            "source": {[m
[32m+[m[32m                "type": "git",[m
[32m+[m[32m                "url": "https://github.com/webmozarts/assert.git",[m
[32m+[m[32m                "reference": "6964c76c7804814a842473e0c8fd15bab0f18e25"[m
[32m+[m[32m            },[m
[32m+[m[32m            "dist": {[m
[32m+[m[32m                "type": "zip",[m
[32m+[m[32m                "url": "https://api.github.com/repos/webmozarts/assert/zipball/6964c76c7804814a842473e0c8fd15bab0f18e25",[m
[32m+[m[32m                "reference": "6964c76c7804814a842473e0c8fd15bab0f18e25",[m
[32m+[m[32m                "shasum": ""[m
[32m+[m[32m            },[m
[32m+[m[32m            "require": {[m
[32m+[m[32m                "php": "^7.2 || ^8.0",[m
[32m+[m[32m                "symfony/polyfill-ctype": "^1.8"[m
[32m+[m[32m            },[m
[32m+[m[32m            "conflict": {[m
[32m+[m[32m                "phpstan/phpstan": "<0.12.20",[m
[32m+[m[32m                "vimeo/psalm": "<4.6.1 || 4.6.2"[m
[32m+[m[32m            },[m
[32m+[m[32m            "require-dev": {[m
[32m+[m[32m                "phpunit/phpunit": "^8.5.13"[m
[32m+[m[32m            },[m
[32m+[m[32m            "type": "library",[m
[32m+[m[32m            "extra": {[m
[32m+[m[32m                "branch-alias": {[m
[32m+[m[32m                    "dev-master": "1.10-dev"[m
[32m+[m[32m                }[m
[32m+[m[32m            },[m
[32m+[m[32m            "autoload": {[m
[32m+[m[32m                "psr-4": {[m
[32m+[m[32m                    "Webmozart\\Assert\\": "src/"[m
[32m+[m[32m                }[m
[32m+[m[32m            },[m
[32m+[m[32m            "notification-url": "https://packagist.org/downloads/",[m
[32m+[m[32m            "license": [[m
[32m+[m[32m                "MIT"[m
[32m+[m[32m            ],[m
[32m+[m[32m            "authors": [[m
[32m+[m[32m                {[m
[32m+[m[32m                    "name": "Bernhard Schussek",[m
[32m+[m[32m                    "email": "bschussek@gmail.com"[m
[32m+[m[32m                }[m
[32m+[m[32m            ],[m
[32m+[m[32m            "description": "Assertions to validate method input/output with nice error messages.",[m
[32m+[m[32m            "keywords": [[m
[32m+[m[32m                "assert",[m
[32m+[m[32m                "check",[m
[32m+[m[32m                "validate"[m
[32m+[m[32m            ],[m
[32m+[m[32m            "support": {[m
[32m+[m[32m                "issues": "https://github.com/webmozarts/assert/issues",[m
[32m+[m[32m                "source": "https://github.com/webmozarts/assert/tree/1.10.0"[m
[32m+[m[32m            },[m
[32m+[m[32m            "time": "2021-03-09T10:59:23+00:00"[m
         }[m
     ],[m
     "packages-dev": [[m
[36m@@ -7290,64 +7739,6 @@[m
                 }[m
             ],[m
             "time": "2021-07-28T10:34:58+00:00"[m
[31m-        },[m
[31m-        {[m
[31m-            "name": "webmozart/assert",[m
[31m-            "version": "1.10.0",[m
[31m-            "source": {[m
[31m-                "type": "git",[m
[31m-                "url": "https://github.com/webmozarts/assert.git",[m
[31m-                "reference": "6964c76c7804814a842473e0c8fd15bab0f18e25"[m
[31m-            },[m
[31m-            "dist": {[m
[31m-                "type": "zip",[m
[31m-                "url": "https://api.github.com/repos/webmozarts/assert/zipball/6964c76c7804814a842473e0c8fd15bab0f18e25",[m
[31m-                "reference": "6964c76c7804814a842473e0c8fd15bab0f18e25",[m
[31m-                "shasum": ""[m
[31m-            },[m
[31m-            "require": {[m
[31m-                "php": "^7.2 || ^8.0",[m
[31m-                "symfony/polyfill-ctype": "^1.8"[m
[31m-            },[m
[31m-            "conflict": {[m
[31m-                "phpstan/phpstan": "<0.12.20",[m
[31m-                "vimeo/psalm": "<4.6.1 || 4.6.2"[m
[31m-            },[m
[31m-            "require-dev": {[m
[31m-                "phpunit/phpunit": "^8.5.13"[m
[31m-            },[m
[31m-            "type": "library",[m
[31m-            "extra": {[m
[31m-                "branch-alias": {[m
[31m-                    "dev-master": "1.10-dev"[m
[31m-                }[m
[31m-            },[m
[31m-            "autoload": {[m
[31m-                "psr-4": {[m
[31m-                    "Webmozart\\Assert\\": "src/"[m
[31m-                }[m
[31m-            },[m
[31m-            "notification-url": "https://packagist.org/downloads/",[m
[31m-            "license": [[m
[31m-                "MIT"[m
[31m-            ],[m
[31m-            "authors": [[m
[31m-                {[m
[31m-                    "name": "Bernhard Schussek",[m
[31m-                    "email": "bschussek@gmail.com"[m
[31m-                }[m
[31m-            ],[m
[31m-            "description": "Assertions to validate method input/output with nice error messages.",[m
[31m-            "keywords": [[m
[31m-                "assert",[m
[31m-                "check",[m
[31m-                "validate"[m
[31m-            ],[m
[31m-            "support": {[m
[31m-                "issues": "https://github.com/webmozarts/assert/issues",[m
[31m-                "source": "https://github.com/webmozarts/assert/tree/1.10.0"[m
[31m-            },[m
[31m-            "time": "2021-03-09T10:59:23+00:00"[m
         }[m
     ],[m
     "aliases": [],[m
[1mdiff --git a/config/bundles.php b/config/bundles.php[m
[1mindex 0b0a44a..df7e0ff 100644[m
[1m--- a/config/bundles.php[m
[1m+++ b/config/bundles.php[m
[36m@@ -4,5 +4,6 @@[m [mreturn [[m
     Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],[m
     Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],[m
     Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],[m
[32m+[m[32m    Pheature\Community\Symfony\PheatureFlagsBundle::class => ['all' => true],[m
     DAMA\DoctrineTestBundle\DAMADoctrineTestBundle::class => ['test' => true],[m
 ];[m
[1mdiff --git a/src/DriverReport/SqlBasedDriverReportCreator.php b/src/DriverReport/SqlBasedDriverReportCreator.php[m
[1mindex 7d2fc71..33a6827 100644[m
[1m--- a/src/DriverReport/SqlBasedDriverReportCreator.php[m
[1m+++ b/src/DriverReport/SqlBasedDriverReportCreator.php[m
[36m@@ -1,12 +1,11 @@[m
 <?php[m
 [m
[31m-namespace LegacyFighter\Cabs\Ui;[m
[32m+[m[32mnamespace LegacyFighter\Cabs\DriverReport;[m
 [m
 use Doctrine\DBAL\Connection;[m
 use LegacyFighter\Cabs\Common\Clock;[m
 use LegacyFighter\Cabs\DTO\AddressDTO;[m
 use LegacyFighter\Cabs\DTO\ClaimDTO;[m
[31m-use LegacyFighter\Cabs\DTO\ClientDTO;[m
 use LegacyFighter\Cabs\DTO\DriverDTO;[m
 use LegacyFighter\Cabs\DTO\DriverReport;[m
 use LegacyFighter\Cabs\DTO\DriverSessionDTO;[m
[1mdiff --git a/src/Ui/DriverReportController.php b/src/Ui/DriverReportController.php[m
[1mindex 56dacef..76b8a03 100644[m
[1m--- a/src/Ui/DriverReportController.php[m
[1m+++ b/src/Ui/DriverReportController.php[m
[36m@@ -2,6 +2,7 @@[m
 [m
 namespace LegacyFighter\Cabs\Ui;[m
 [m
[32m+[m[32muse LegacyFighter\Cabs\DriverReport\DriverReportCreator;[m
 use Symfony\Component\HttpFoundation\JsonResponse;[m
 use Symfony\Component\HttpFoundation\Request;[m
 use Symfony\Component\HttpFoundation\Response;[m
[36m@@ -10,12 +11,12 @@[m [muse Symfony\Component\Routing\Annotation\Route;[m
 class DriverReportController[m
 {[m
     public function __construct([m
[31m-        private SqlBasedDriverReportCreator $sqlBasedDriverReportCreator[m
[32m+[m[32m        private DriverReportCreator $driverReportCreator[m
     ) {}[m
 [m
     #[Route('/driverreport/{driverId}', methods: ['GET'])][m
     public function loadReportForDriver(int $driverId, Request $request): Response[m
     {[m
[31m-        return new JsonResponse($this->sqlBasedDriverReportCreator->createReport($driverId, (int) $request->get('lastDays', 1)));[m
[32m+[m[32m        return new JsonResponse($this->driverReportCreator->create($driverId, (int) $request->get('lastDays', 1)));[m
     }[m
 }[m
[1mdiff --git a/symfony.lock b/symfony.lock[m
[1mindex c549073..d74b050 100644[m
[1m--- a/symfony.lock[m
[1m+++ b/symfony.lock[m
[36m@@ -118,6 +118,21 @@[m
     "laminas/laminas-code": {[m
         "version": "4.5.1"[m
     },[m
[32m+[m[32m    "pheature/inmemory-toggle": {[m
[32m+[m[32m        "version": "0.3.0"[m
[32m+[m[32m    },[m
[32m+[m[32m    "pheature/symfony-toggle": {[m
[32m+[m[32m        "version": "0.3.2"[m
[32m+[m[32m    },[m
[32m+[m[32m    "pheature/toggle-core": {[m
[32m+[m[32m        "version": "0.3.0"[m
[32m+[m[32m    },[m
[32m+[m[32m    "pheature/toggle-crud-psr11-factories": {[m
[32m+[m[32m        "version": "0.3.0"[m
[32m+[m[32m    },[m
[32m+[m[32m    "pheature/toggle-model": {[m
[32m+[m[32m        "version": "0.3.0"[m
[32m+[m[32m    },[m
     "php-cs-fixer/diff": {[m
         "version": "v2.0.2"[m
     },[m
