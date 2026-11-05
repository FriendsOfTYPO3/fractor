# AGENTS.md

Instructions for coding agents. The rules in
[CONTRIBUTING.md](CONTRIBUTING.md) apply to you as well; this file says
where each piece of text belongs and how long it may be.

## Where text belongs

| Text | Where | Length |
|---|---|---|
| What changed and why | Commit body | A few lines or bullets, wrapped at 72 |
| What changes for users | Pull request | TL;DR of 1-3 sentences and `Resolves #123` |
| What is wrong and how to reproduce it | Issue | Template fields, minimal reproduction |
| Logs, traces, analyses, alternatives | `<details>` below the TL;DR | Only if really needed |
| Why code that looks wrong is correct | Code comment | At most 2 lines |
| Behavior guarded by a test | Test method name | - |

Never repeat the same text in commit, pull request and issue. The pull
request links the issue; the commit explains the change.

## Rules

- Summary line: `[BUGFIX]`, `[FEATURE]`, `[TASK]` or `[DOCS]`, with
  `[!!!]` in front for breaking changes; imperative, at most 72
  characters.
- Footer: `Resolves: #123` or `Related: #123`, one issue per line.
- Do not add `Signed-off-by:`; only the human author does.
- No emojis in commits, pull requests or issues.
- Before you write a long text, write the TL;DR. If the TL;DR is enough,
  stop there.

## Before you commit

Run `composer run-script local:contribute`. If you added or changed a
rule, also run `composer run-script docs`. Rebase onto `main`; merge,
`fixup!` and `WIP` commits fail the checks.
