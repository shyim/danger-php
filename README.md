
# Danger PHP

Danger runs during your CI process, and gives teams the chance to automate common code review chores.
This project ports [Danger](https://danger.systems/ruby/) to PHP.
This project is still in the early phase. Feel free to try it out and contribute!




## Badges

[![MIT License](https://img.shields.io/apm/l/atomic-design-ui.svg?)](https://github.com/shyim/danger-php/blob/main/LICENSE)



## Installation

Install danger-php using Composer

```bash 
  composer global require shyim/danger-php
```

Install danger-php using phar attached on Github Releases
## Usage/Examples

### Disallow multiple commits with same message

```php
<?php declare(strict_types=1);

use Danger\Config;
use Danger\Rule\DisallowRepeatedCommitsRule;

return (new Config())
    ->useRule(new DisallowRepeatedCommitsRule) // Disallows multiple commits with the same message
;
```

### Only allow one commit in Pull Request

```php
<?php declare(strict_types=1);

use Danger\Config;
use Danger\Rule\MaxCommitRule;

return (new Config())
    ->useRule(new MaxCommitRule(1))
;


```

### Check for modification on CHANGELOG.md

```php
<?php declare(strict_types=1);

use Danger\Config;
use Danger\Context;

return (new Config())
    ->useRule(function (Context $context): void {
        if (!$context->platform->pullRequest->getFiles()->hasModifiedFile('CHANGELOG.md')) {
            $context->failure('Please edit also the CHANGELOG.md');
        }
    })
;

```

### Check for Assignee in PR

```php
<?php declare(strict_types=1);

use Danger\Config;
use Danger\Context;

return (new Config())
    ->useRule(function (Context $context): void {
        if (count($context->platform->pullRequest->assignees) === 0) {
            $context->warning('This PR currently doesn\'t have an assignee');
        }
    })
;

```

## CI Integration

### Github Actions

```yaml
name: Danger
on:
  pull_request:

jobs:
  pr:
    runs-on: ubuntu-latest
    steps:
      - name: Clone
        uses: actions/checkout@v1

      - name: Download latest Danger
        run: wget https://github.com/shyim/danger-php/releases/download/0.0.1/danger.phar

      - name: Danger
        run: php danger.phar ci
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
          GITHUB_PULL_REQUEST_ID: ${{ github.event.pull_request.number }}
```

## Screenshots

![Example Comment](https://i.imgur.com/e2OEChE.png)

## Roadmap

- Add GitLab Support


## License

[MIT](https://choosealicense.com/licenses/mit/)

  