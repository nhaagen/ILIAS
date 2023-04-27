[< devguide](../devguide.md#tools)

# DOIL (DOcker and ILias)

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

