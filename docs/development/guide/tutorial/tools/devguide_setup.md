[< devguide](../devguide.md#tools)

# setup

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


