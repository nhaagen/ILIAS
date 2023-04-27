# ILIAS Development, a guide to get you started

It is great you that you want ot contribute to ILIAS. 
You will want your local codebase and most certainly a locally installed instance, too.
In order to get it up and running, you will need a webserver environment along 
with some additional tools.

<a name="environment"></a>
## Environment
As a WebApp, ILIAS will need a webserver along with a database. There are 
different ways to set up a suitable environment; of course, you can set up 
a (local) server and install ILIAS. However, when actively developing on 
several branches (e.g. with different PHP versions), a [dockerized solution](https://docs.docker.com/engine/install/) 
might be preferred.


### Manually Setup the Server
There is a very good guide on [how to set up ILIAS](../../../configuration/install.md).
This is definitely the way to go for productive systems.

### Docker Images 
SR Solutions provides prebuilt docker images; you will have to checkout the ILIAS code seperately and mount 
the local directory to the container.

[docker images](tools/devguide_dockerimages.md)

### doil
doil will build and manage multiple docker-instances according to your specs given 
in a wizard.

[introduction to doil](tools/devguide_doil.md)

<a name="tools"></a>
## Tools
There are some tools that matter during setup and development:

### git
No matter which way you chose to install ILIAS - in order to interact with the
codebase, you need to be familiar with git.

[introduction to git](tools/devguide_git.md)

### composer
Some PHP-dependencies are not part of the ILIAS-repo, so they have to be retrieved and installed via composer.
```
composer install --no-dev
```
```
composer du
```
[introduction to composer](tools/devguide_composer.md)

### npm
ILIAS uses some JavaScript-libraries; they need to be downloaded via npm.
```
npm install --omit-dev --ignore-scripts
```

[introduction to npm](tools/devguide_npm.md)

### setup
When installing ILIAS or upgrading from an existing version, you will come along
the [setup cli](../../../../setup/README.md).
```
php setup/setup.php update -y
```
[introduction to setup](tools/devguide_setup.md)



<a name="testandlinters"></a>
## Tests and Linters

ILIAS sticks to PSR-12 coding standard (plus minor additions).
Also, when typehinting is not all sufficient, proper docstrings should be used.

### PHP Coding Standards Fixer
A helpful tool that applies the coding standard to your files;

[introduction to PHP CS Fixer](../../coding-style.md)

### PHPStan
Further code analysis, quite valuable introspection; we do not enforce a stan-level yet, 
but we highly encourage you to use this tool.

[introduction to PHPStan](../../static_code_analysis.md)

### PHPUnit

There are several unit tests on ILIAS; you should at least write tests for the 
public interface methods. Failing tests will prevent merging your code.
```
./CI/PHPUnit/run_tests.sh
```
or, to run specific tests only
```
./CI/PHPUnit/run_tests.sh --filter [part of your test function's name]
```

### Mocha
Also, there are some tests for the JS-parts:

[introduction to Mocha](../../js/js-unit-test.md)

### eslint
And, of course, there is a preferred style for JS as well that you can check with eslint.

[introduction to eslint](../../js/js-coding-style.md)

<a name="tweaks"></a>
## Tweaks

### Errors / log
Whenever ILIAS runs into an (uncought) error, it will give a short warning with
the name of the log file.
To change this behavior in favor of a full stacktrace, you can edit the ini-file
in *./data/[client]/client.ini.php*

In the section 'system', you can switch to DEVMODE by adding the line
```
[system]
...
DEVMODE = "1"

```

<a name="process"></a>
## Contributing
See this [contributor guide](../../contributing.md).

### How to find relevant Codeparts
[API and Services Overview](../../api-overview.md)

### A Word on UI
Whenever possible, try to use the [UI-Framework](../../../../src/UI/README.md).

<a name="help"></a>
## Find Help
There is a great discord server over here: https://discord.gg/JSpPdcZb