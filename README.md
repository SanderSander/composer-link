# composer-link
![phpunit](https://github.com/SanderSander/composer-link/actions/workflows/unit-tests.yml/badge.svg?branch=master)
![Packagist Downloads](https://img.shields.io/packagist/dt/SanderSander/composer-link)

Adds ability to link local packages in composer for development. 

This plugin won't alter your `composer.json` or `composer.lock` file, 
while maintaining the composer abilities to manage/upgrade your packages.

## Requirements

- PHP >= 8.1
- Composer >= 2.6

## Installation

This plugin can be installed globally or per project

Globally 
```
composer global require sandersander/composer-link
```

Per project: 
```
composer require --dev sandersander/composer-link
```

## Usage

The following three commands are made available by this plugin `link`, `unlink` and `linked`.
When the plugin is installed globally you can prefix the commands with `global` as example `composer global linked` 
and install global packages.

To link a package you can use the `link` commands, you can also link a global package.
When linked to a global package absolute paths are used, when using a relative path composer-link resolves
it to the absolute path.

```
composer link ../path/to/package
composer global link ../path/to/package
```

It's also possible to use a wildcard in your path, note that this will install all packages found in the directory `../packages`
If you don't want to link all the packages but only the ones originally installed you can pass the `--only-installed` flag.

```
composer link ../packages/*
composer link ../packages/* --only-installed
```

Composer link will automatically install/update the required packages from the linked package, 
you can prevent this behavior by adding the `--without-dependencies` flag.

When the `composer link` or `composer unlink` are used all packages defined in `require-dev` of the root package are 
installed by default, this can be prevented by using the `--no-dev` flag

To unlink the package you can use the `unlink` command
```
composer unlink ../path/to/package
composer unlink ../packages/*
composer global unlink ../path/to/package
```

You can also unlink all package with the following command

``` 
composer unlink-all
```

To see all linked packages in your project you can use the `linked` command
```
composer linked
composer global linked
```

## Development

The following tools are available for development.
It's also possible to link this package to your global for testing changes.

```
composer run lint               # Lints all files 
composer run test               # Runs unit tests
composer run phpmd              # Runs phpmd
composer run phpstan            # Runs phpstan
composer run test-integration   # Runs integration tests for linux, this requires docker
```
