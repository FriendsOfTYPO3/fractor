# Contributing to Fractor

Thanks for helping! Reviews are done by hand, so keep everything you write
short enough to be read in full: long enough to know what changed, short
enough to review.

Commit messages, pull requests and issues follow the
[TYPO3 commit message rules][typo3-commit], adapted to GitHub below.

## Workflow

1. Fork the repository and create a branch from `main`.
2. Make your changes, one logical change per commit.
3. Run `composer run-script local:contribute` (Rector, ECS, PHPStan,
   tests).
4. Run `composer run-script docs` if you added or changed a rule.
5. Rebase onto the latest `main`; merge commits, `fixup!` and `WIP`
   commits fail the checks.
6. Open a pull request.

## Commit messages

```
[BUGFIX] Keep umlauts when printing XLIFF files

The printer encoded every non-ASCII character as an HTML entity.
It now writes the document in its declared encoding.

Resolves: #123
```

Summary line:

- Start with a prefix: `[BUGFIX]`, `[FEATURE]`, `[TASK]` or `[DOCS]`.
  Put `[!!!]` before it for a breaking change, e.g. `[!!!][TASK]`.
- Use the imperative ("Add", "Fix", "Remove"), describe the new
  behavior, and keep it under 52 characters if possible, 72 at most.

Body:

- Explain what changes and why, and what reviewers should look at.
- Wrap lines at 72 characters. Bullets start with `- `, continuation
  lines are indented.
- Leave out reproduction steps, the content of the issue, a restatement
  of the diff and the history of what you tried.

Footer, one issue per line:

- `Resolves: #123` closes the issue on merge.
- `Related: #123` links an issue that stays open.

Only the human author adds `Signed-off-by:`, never an AI tool.
Attribution lines such as `Co-authored-by:` for tools are not needed.

## Pull requests

The commit message is the full description, so the pull request does not
repeat it. Start with a TL;DR of one to three sentences: what changes for
users. Then link the issue.

```markdown
`process` now fails with exit code 1 when none of the given paths
matches a file or directory, as Rector does.

Resolves #123
```

Screenshots of console output are welcome. If more is really needed, put
it below the TL;DR in a collapsible section (see below).

## Issues

- Use the templates. Bug titles start with `[BUG]` and name the wrong
  behavior; rule requests use the title of the TYPO3 changelog entry,
  e.g. `Deprecation: #109196 - Deprecate doktypesToShowInNewPageDragArea`.
- Give a minimal reproduction and the versions; leave out everything a
  maintainer does not need to reproduce it.
- Put long logs, stack traces and analyses in a collapsible section.

## Collapsible sections

Use them only when the extra detail is really needed. The TL;DR stays on
top and must make sense without opening them.

```markdown
<details>
<summary>Analysis</summary>

Long content in Markdown. The blank line after `<summary>` is required.

</details>
```

[typo3-commit]: https://docs.typo3.org/m/typo3/guide-contributionworkflow/main/en-us/Appendix/CommitMessage.html
