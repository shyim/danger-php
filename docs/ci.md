# CI Integration

## Github Actions

### pull_request_target

This uses the target branch instead the pull request branch. All changes will be not locally available.
With this method all operations like commenting and labeling will work as github-actions user

```yaml
name: Danger
on:
  pull_request_target:

jobs:
  pr:
    runs-on: ubuntu-latest
    steps:
      - name: Clone
        uses: actions/checkout@v2.4.0

      - name: Danger
        uses: shyim/danger-php@0.2.8
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
          GITHUB_PULL_REQUEST_ID: ${{ github.event.pull_request.number }}
```

### pull_request

**Warning**: This will only function on pull requests in the same repository. Checkout [this](./getting_started.md) for an entire setup.
Label functions will not work on pull requests coming from forks.

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

      - name: Danger
        uses: shyim/danger-php@0.2.8
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
          GITHUB_PULL_REQUEST_ID: ${{ github.event.pull_request.number }}
```

## GitLab CI

```yaml
Danger:
    image:
        name: ghcr.io/shyim/danger-php:latest
        entrypoint: ["/bin/sh", "-c"]
    rules:
      - if: '$CI_PIPELINE_SOURCE == "merge_request_event"'
    script:
        - danger ci
```

You will need also a new environment variable `DANGER_GITLAB_TOKEN` with  a Gitlab Token to be able to post the message.
For this purpose you should use a Bot account
