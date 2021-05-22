# Commands

## Init

This commands generates a default `.danger.php` file

## github-local

This command runs the local Danger configuration against a Github PR without modifying anything.

### Parameters

- Github Pull Request URL

## gitlab-local

This command runs the local Danger configuration against a Gitlab PR without modifying anything;

### Parameters

- Gitlab Project ID
- Pull Request Number

### Environment variables

- DANGER_GITLAB_TOKEN with a Gitlab Token with api scope

## ci

This command runs Danger in CI mode and tries to detect the platform by environment variables