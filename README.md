[![Latest Stable Version](https://poser.pugx.org/captainhook/captainhook/v/stable.svg?v=1)](https://packagist.org/packages/captainhook/captainhook)
[![PHP Version](https://poser.pugx.org/captainhook/captainhook/require/php)](https://packagist.org/packages/captainhook/captainhook)
[![Downloads](https://poser.pugx.org/captainhook/captainhook/downloads)](https://packagist.org/packages/captainhook/captainhook/stats)
[![Downloads](https://poser.pugx.org/captainhook/captainhook/d/monthly)](https://packagist.org/packages/captainhook/captainhook/stats)
[![Downloads](https://poser.pugx.org/captainhook/captainhook/d/daily)](https://packagist.org/packages/captainhook/captainhook/stats)
[![License](https://poser.pugx.org/captainhook/captainhook/license.svg?v=1)](https://github.com/captainhook-git/captainhook/blob/main/LICENSE)
[![Build Status](https://github.com/captainhook-git/captainhook/workflows/Continuous%20Integration/badge.svg)](https://github.com/captainhook-git/captainhook/actions)
[![Code Quality](https://scrutinizer-ci.com/g/captainhook-git/captainhook/badges/quality-score.png?b=main&v=1)](https://scrutinizer-ci.com/g/captainhook-git/captainhook/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/captainhook-git/captainhook/badges/coverage.png?b=main&v=1)](https://scrutinizer-ci.com/g/captainhook-git/captainhook/?branch=master)
[![X](https://img.shields.io/badge/X-%40captainhookgit-black.svg)](https://twitter.com/intent/user?screen_name=captainhookgit)
[![Mastodon](https://img.shields.io/badge/Mastodon-%40captainhook-purple.svg)](https://phpc.social/@captainhook)

# CaptainHook

<img src="https://captainhook-git.github.io/captainhook/gfx/ch.png" alt="CaptainHook logo" align="right" width="200"/>

*CaptainHook* is an easy to use and very flexible git hook library for php developers.
It enables you to configure your git hook actions in a simple json file.

You can use *CaptainHook* to validate or prepare your commit messages, ensure code quality
or run unit tests before you commit or push changes to git. You can automatically clear
local caches or install the latest composer dependencies after pulling the latest changes.

*CaptainHook* makes it easy to share hooks within your team and even can make sure that
everybody in your team activates the hooks locally.

You can run cli commands, use some built in validators, or write
your own PHP classes that get executed by *CaptainHook*.
For more information have a look at the [documentation](https://php.captainhook.info/ "CaptainHook Documentation").

## Installation

The preferred method to install *CaptainHook* is to install the PHAR file.
You can do so by using [Phive](https://phar.io/), use the PHAR Composer package, or download the PHAR from the GitHub [release page](https://github.com/captainhook-git/captainhook/releases/latest).
```bash
phive install captainhook
```
Or use *Composer* to install it.
```bash
composer require --dev captainhook/captainhook-phar
```
If you want to get the source code with all its dependencies you can use:
```bash
composer require --dev captainhook/captainhook
```

## Setup
After installing CaptainHook you can use the *captainhook* executable to create a configuration.
```bash
vendor/bin/captainhook configure
```
After creating the *captainhook.json* configuration file you have to activate the hooks by installing them to
your local .git directory. To do so just run the following *CaptainHook* command.
```bash
vendor/bin/captainhook install
```

Have a look at this short installation video.

[![Install demo](http://img.youtube.com/vi/qQyDc-Wxk7Y/hq720.jpg)](http://www.youtube.com/watch?v=qQyDc-Wxk7Y)

One of the goals of *CaptainHook* is to make it easy for a team to use the same git hooks. If you want to make sure
everybody actually installs the hooks you can use the small *Composer* plugin `hook-installer`.
It runs the `captainhook install` command everytime you run a *Composer* command.

```bash
composer require --dev captainhook/hook-installer
```

Off course teammates can still commit without executing the hooks,
that's why you should run appropriate checks on the backend as well.
But at least this way nobody can forget to install them "by accident".

## Configuration

Here's an example *captainhook.json* configuration file.
```json
{
  "commit-msg": {
    "actions": [
      {
        "action": "\\CaptainHook\\App\\Hook\\Message\\Action\\Beams"
      }
    ]
  },
  "pre-commit": {
    "actions": [
      {
        "action": "phpunit"
      },
      {
        "action": "phpcs --standard=psr12 src"
      }
    ]
  }
}
```

## Documentation

The full documentation can be found at [php.captainhook.info](https://php.captainhook.info/ "CaptainHook dcoumentation").

## Contributing

So you'd like to contribute to the `CaptainHook` project? Excellent! Thank you very much.
I can absolutely use your help.

Have a look at the [contribution guidelines](CONTRIBUTING.md).
