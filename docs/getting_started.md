# Getting started

Before you can use Danger-PHP, you need to provide a ruleset.
The default configuration can be generated using:

```shell
# Composer global installed or Docker
$ danger init
# Phar
$ php danger.phar init
```

This command generates the default `.danger.php` in the current directory.


# Danger config

The `.danger.php` file returns a `Danger\Config` object. This object accepts lot of configuration.

## useRule

The `useRule` method can be used to add a Rule which should be executed in this Danger. 
See [builtin rules](./builtin-rules.md) for all included rules. 
This method also accepts a function consider you own changes. You will get the current Danger context as first parameter.
Here is an example:

```php
<?php declare(strict_types=1);

use Danger\Config;
use Danger\Context;

return (new Config())
    ->useRule(function (Context $context) {
        // if !$context->xxxx
            $context->failure('Conditions not matched');
    })
;
```

To see what the `Danger\Context` see [here](./context.md)

## after

The `after` is similar to `useRule` but are intended to be executed as last steps.
One example usage case could be to add labels when Danger is failed.

```php
<?php declare(strict_types=1);

use Danger\Config;
use Danger\Context;

return (new Config())
    ->after(function (Context $context) {
        if ($context->hasFailures()) {
            $context->platform->addLabels('Incomplete');
        }
    })
;
```

## useCommentMode

With this option you can control that the message should be updated or replaced

```php
<?php declare(strict_types=1);

use Danger\Config;

return (new Config())
    ->useCommentMode(Config::UPDATE_COMMENT_MODE_REPLACE) // Replace the old comment
    ->useCommentMode(Config::UPDATE_COMMENT_MODE_UPDATE) // Update the old comment
;
```

## useThreadOn

**Currently only supported on GitLab**

This option allows using a thread instead of a comment in the Pull Request.
You can declare for which level of report you want to use a thread.

Use thread if reports has at least one failure:
```php
<?php declare(strict_types=1);

use Danger\Config;

return (new Config())
    ->useThreadOn(Config::REPORT_LEVEL_FAILURE)
;
```

Use thread if reports has at least one warning:
```php
<?php declare(strict_types=1);

use Danger\Config;

return (new Config())
    ->useThreadOn(Config::REPORT_LEVEL_WARNING)
;
```

Use thread if reports has at least one notice:
```php
<?php declare(strict_types=1);

use Danger\Config;

return (new Config())
    ->useThreadOn(Config::REPORT_LEVEL_NOTICE)
;
```
