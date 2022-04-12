# composer-link
![phpunit](https://github.com/SanderSander/composer-link/actions/workflows/unit-tests.yml/badge.svg?branch=master)
[![Coverage Status](https://coveralls.io/repos/github/SanderSander/composer-link/badge.svg?branch=master)](https://coveralls.io/github/SanderSander/composer-link?branch=master)
[![Maintainability](https://api.codeclimate.com/v1/badges/3815e6abf2ec0e1d4ac8/maintainability)](https://codeclimate.com/github/SanderSander/composer-link/maintainability)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/SanderSander/composer-link/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/SanderSander/composer-link/?branch=master)

Adds ability to link local packages in composer for development. 

This plugin won't alter your `composer.json` or `composer.lock` file, 
while maintaining the composer abilities to manage/upgrade your packages.

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

To unlink the package you can use the `unlink` command
```
composer unlink ../path/to/package
composer global link ../path/to/package
```

To see all linked packages in your project you can use the `linked` command
```
composer linked
composer global linked
```

## Contributing

Feel free to contribute, all help is welcome!

1. Fork it!
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request :D
