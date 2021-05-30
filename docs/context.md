# Danger Context

There is an overview of all information inside the Context

```php
$context = new \Danger\Context();

$context->platform->addLabels('Label 1', 'Label 2'); // Allows adding label
$context->platform->removeLabels('Label 1', 'Label 2'); // Allows removing label
$context->platform->pullRequest->id; // Pull Request ID
$context->platform->pullRequest->projectIdentifier; // Github: Owner/Repository, Gitlab: Project-ID
$context->platform->pullRequest->title; // Pull Request Title
$context->platform->pullRequest->body; // Body
$context->platform->pullRequest->assignees; // Assignees
$context->platform->pullRequest->reviewers; // Reviewers
$context->platform->pullRequest->labels; // Labels
$context->platform->pullRequest->createdAt; // Created At as \DateTime
$context->platform->pullRequest->updatedAt; // Updated At as \DateTime
$context->platform->raw; // Raw API Response
$context->platform->pullRequest->rawCommits; // Raw API Commits (only available after getCommits() call)
$context->platform->pullRequest->rawFiles; // Raw API Files (only available after getFiles() call)
$context->platform->pullRequest->getCommits(); // Collection of commits

$commit; // Element of commit collection
$commit->author; // Commit author
$commit->authorEmail; // Commit author email
$commit->message; // Commit message
$commit->sha; // Commit sha
$commit->createdAt; // Created at as \DateTime
$commit->verified; // Commit verified (gpg)

$context->platform->pullRequest->getFiles(); // Collection of files

$file; // Element of files collection
$file->name; // File name
$file->status; // File status can be added, modified, removed
$file->additions; // Additions to the file as int
$file->changes; // Changes to the file as int
$file->deletions; // Deletions to the file as int
$file->patch; // Git patch of this file
$file->getContent(); // Retrieve the current content of the file 

$context->platform->pullRequest->getComments(); // Collection of comments

$comment; // Element of comments collection
$comment->author; // Comment author username
$comment->body; // Comment body
$comment->createdAt; // Comment createdAt
$comment->updatedAt; // Comment updatedAt
```

## Advanced

The information above are available in all platforms. The platforms has also a public property `client` with the corresponding platform client to do custom api calls