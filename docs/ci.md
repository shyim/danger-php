# CI Integration

## Github Actions

**Warning**: This will only function on pull requests in the same repository. Checkout [this](./getting_started.md#useGithubCommentProxy) for an entire setup.
Label functions will not work on pull requests coming from forks.

```yaml
name: Danger
on:
  pull_request_target:

jobs:
  pr:
    runs-on: ubuntu-latest
    steps:
      - name: Clone
        uses: actions/checkout@v1

      - name: Download latest Danger
        run: wget https://github.com/shyim/danger-php/releases/latest/download/danger.phar

      - name: Danger
        run: php danger.phar ci
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
          GITHUB_PULL_REQUEST_ID: ${{ github.event.pull_request.number }}
```

## Gitlab CI

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
