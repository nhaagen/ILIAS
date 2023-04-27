[< devguide](../devguide.md#tools)

# git (ILIAS Development)

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


