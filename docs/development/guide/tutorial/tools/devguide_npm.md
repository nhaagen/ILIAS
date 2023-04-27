[< devguide](../devguide.md#tools)

# npm

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
