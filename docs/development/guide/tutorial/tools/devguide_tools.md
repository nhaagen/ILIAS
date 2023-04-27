# Tools for ILIAS Development

<!-- MarkdownTOC depth=0 autolink="true" bracket="round" autoanchor="true" style="ordered" indent="   " -->


1. [Environment](#environment)
   1. [Manually Setup a LAMP](#manually)
   1. [Use Docker](#docker)
      1. [Docker Images](#sr-images)
      1. [doil](#doil)
1. [Tools](#tools)
   1. [git](#git)
   1. [composer](#composer)
   1. [npm](#npm)
   1. [setup](#setup)
1. [Tweaks](#tweaks)
   1. [Errors](#errors)

<!-- /MarkdownTOC -->

<a name="tools"></a>
You will want your local codebase and most certainly a locally installed ILIAS, too.
In order to get it up and running, you will need a webserver environment along 
with some additional tools.

<a name="environment"></a>
## Environment
As a WebApp, ILIAS will need a webserver along with a database. There are 
different ways to set up a suitable environment; of course, you can set up 
a (local) server and install ILIAS. However, when actively developing on 
several branches (e.g. with different PHP versions), a [dockerized solution](#docker) 
might be preferred.

<a name="manually"></a>
## Manually Setup the Server
There is a very good guide on [how to set up ILIAS](../../../../configuration/install.md).
This is definitely the way to go for productive systems.

<a name="docker"></a>
## Use Docker
You should be (slightly) familiar with [docker](https://docs.docker.com/engine/install/) 
to go this way.
Especially during development, running several instances in parallel or switching 
between dependencies is very handy.
There are some alternatives that will make it easy to set up your docker environment:

<a name="sr-images"></a>
### Docker Images 
There are some dockerfiles provided by SR Solutions:
https://hub.docker.com/r/srsolutions/ilias-dev/

You can also use 
```
docker pull srsolutions/ilias-dev
```
Please note that you will have to checkout the ILIAS code seperately and mount 
the local directory to the container.
There is a [video](https://www.youtube.com/watch?v=ZXiM9dqcOHI) explaining the process.

<a name="doil"></a>
### DOIL (DOcker and ILias)
Again, you will need [docker](https://docs.docker.com/engine/install/) installed.
Then, download and install [doil](https://github.com/conceptsandtraining/doil). 
If you already have a webserver running on port 80, shut it down.
```
git clone git@github.com:conceptsandtraining/doil.git doil_install
doil_install/setup/install.sh
```
Once installed, you can create new ILIAS instances. A guided setup will prompt you for desired branches and the like.
Please be patient - the repository will be downloaded and images will be created.
This takes a while when doing it the first time.
```
doil create
   Please enter a name for the instance to create: dev_r8
   Please select a repository to create from:
     [0] Global - ilias - https://github.com/ILIAS-eLearning/ILIAS.git
 > 0
   Update repo https://github.com/ILIAS-eLearning/ILIAS.git ... done
   Please select a branch to create from:
     [0] dependabot/composer/symfony/http-kernel-4.4.50
     [1] dependabot/npm_and_yarn/node-fetch-and-pouchdb-2.6.7
     [2] release_5-4
     [3] release_6
     [4] release_7
     [5] release_8
     [6] trunk
 > 5
Please enter the php version you want (format=*.*) : 7.4
Please enter a target where doil should install dev_r8. Leave blank for current directory. :   
Install xdebug? [yN]: y
Create a global instance? [yN]: N
Skip creating readme file? [yN]: 
   Creating instance dev_trunk ...
   Updating debian image ... done
   Create basic folders ... done
   Link instance ... done
   Set folder permissions ... done
   Copy necessary files ... done
   Setting up configuration ... done
   Copying ilias to target ... done
   Set up docker files ... done
   Building minion image. This will take a while. Please be patient ... done
   Checking salt key ...
   Checking salt key ... done
   Setting up instance configuration ... done
   Apply base state ... done
   Apply dev state ... done
   Apply php state ... done
   Apply ilias state ... ... done
   Apply composer state ... done
   Trying auto installer ... done
   Apply enable-xdebug state ... done
   Apply access state ... done
   Apply ilias-postinstall state ... done
   Finalizing docker image ... done
   Copy README to project ... done
   Please start the created instance by doil up dev_r8.
```
Now power up your instance with 
```
doil up dev_r8
   Start instance dev_r8 ... done
```
The ilias site should be available under http://doil/dev_r8 in your browser.

You can also log into the container with
```
doil login dev_r8
```
or directly run a command in the container, e.g. access the DB:
```
doil exec dev_r8 "mysql -A ilias"
```
or, with a specified path:
```
doil exec dev_r8 -w /var/www/html 'php setup/setup.php status'
doil exec dev_r8 -w /var/ilias/data 'cat ilias-config.json'
```

The source-code is in **dev_r8/volumes/ilias**.
Add your git-remote and create a new branch - happy coding. 


<a name="tools"></a>
# Tools
Next to the ILIAS code itself, which can be retrieved via git, you will need 
composer and npm to install further dependencies.

<a name="git"></a>
## git
ILIAS code is managed with git - you will need it.
https://git-scm.com/book/en/v2/Getting-Started-Installing-Git

Clone the code to the web servers docroot (e.g. /var/www/html); this example uses release_8.
```
cd /var/www/html/
git clone https://github.com/ILIAS-eLearning/ILIAS.git
git checkout release_8
```

You should now have a local copy of ILIAS.
There are literally books about git, so we will not go into too many details here;
however, you will like to add your own repository and create a branch to work on
(I assume, you have forked ILIAS on github?):
```
git remote -v
   origin   https://github.com/ILIAS-eLearning/ILIAS.git (fetch)
   origin   https://github.com/ILIAS-eLearning/ILIAS.git (push)

git remote add myrepo git@github.com:myrepo/ILIAS.git
git remote -v
   myrepo   git@github.com:myrepo/ILIAS.git (fetch)
   myrepo   git@github.com:myrepo/ILIAS.git (push)
   origin   https://github.com/ILIAS-eLearning/ILIAS.git (fetch)
   origin   https://github.com/ILIAS-eLearning/ILIAS.git (push)

git branch
     dependabot/composer/symfony/http-kernel-4.4.50
     dependabot/npm_and_yarn/node-fetch-and-pouchdb-2.6.7
     release_5-4
     release_6
     release_7
   * release_8
     trunk

git checkout -b r8/fix/mantis-issue-01234
   Switched to a new branch 'r8/fix/mantis-01234'
git branch
     dependabot/composer/symfony/http-kernel-4.4.50
     dependabot/npm_and_yarn/node-fetch-and-pouchdb-2.6.7
   * r8/fix/mantis-01234
     release_5-4
     release_6
     release_7
     release_8
     trunk
```

Please provide git with your name - it will show with your commits
```
git config --global user.name "Your Name"
git config --global user.email you@example.com
```

Now, that you fixed the issue or added to your feature, commit to your repo and
start a pull request.
```
git status
Changes not staged for commit:
     modified:   Modules/LearningSequence/classes/class.ilLearningSequenceAppEventListener.php

git add Modules/LearningSequence/classes/class.ilLearningSequenceAppEventListener.php
git commit -m"LearningSequence: fix isssue 01234"
   [r8/fix/mantis-01234 397179ac5a] LearningSequence: fix isssue 01234
    1 file changed, 1 insertion(+)

git push myrepo r8/fix/mantis-01234
   Enumerating objects: 4897, done.
   Counting objects: 100% (3521/3521), done.
   Delta compression using up to 8 threads
   Compressing objects: 100% (1128/1128), done.
   Writing objects: 100% (2529/2529), 5.32 MiB | 236.00 KiB/s, done.
   Total 2529 (delta 2030), reused 1833 (delta 1385), pack-reused 0
   remote: Resolving deltas: 100% (2030/2030), completed with 559 local objects.
   remote: 
   remote: Create a pull request for 'r8/fix/mantis-01234' on GitHub by visiting:
   remote:      https://github.com/myrepo/ILIAS/pull/new/r8/fix/mantis-01234
   remote: 
   To github.com:myrepo/ILIAS.git
    * [new branch]            r8/fix/mantis-01234 -> r8/fix/mantis-01234

```
Goto github to actually make your pull request.






<a name="composer"></a>
## composer
Some PHP-dependencies are not part of the ILIAS-repo, so they have to be retrieved and installed via composer.
https://getcomposer.org/download/

Head to the root-directory of your ILIAS directory.
The desired packages are listed in composer.json.
You can install them by running
```
composer install --no-dev
```
Since Composer also provides the autoloading-capabilites for ILIAS,
you will come back to it from time to time; 
a very common command to rescan and build static artifacts is dump-autoload:
```
composer du
```

<a name="npm"></a>
## npm
ILIAS uses some JavaScript-libraries; they need to be downloaded via npm.
https://docs.npmjs.com/downloading-and-installing-node-js-and-npm
or get it from github:
https://github.com/nodesource/distributions

Head to the root-directory of your ILIAS directory.
The desired packages are listed in package.json.
You can install them by running
```
npm install --omit-dev --ignore-scripts
```
This will create a direcotry "node_modules" and the libraries within.

To install further dependencies, like, e.g., mocha for JS testing, use the install flag (-i).
This done, you can run the JS tests.
```
npm i --save-dev mocha chai esm jsdom
npm test
```

<a name="setup"></a>
## setup
When installing ILIAS or upgrading from an existing version, you will come along
the [setup cli](../../../../../../setup/README.md).
Next to installing and update, there are other helpfull features you should be aware of,
e.g. (re-)building artifacts such as the control structure or achieving single
objectives.

```
php setup/setup.php build-artifacts
```
```
php setup/setup.php achieve
php setup/setup.php achieve globalcache.flushAll
```
You can skip the dialouges by adding the -y flag.


<a name="setup"></a>
# Tweaks

## errors
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





