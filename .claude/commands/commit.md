# Commit Changes

Analyze recent changes and create a well-formatted git commit.

## Instructions

Execute the following steps:

1. **Check git status**: Run `git status` to see all untracked and modified files. Never use the `-uall` flag.

2. **Check for changes**: If there are no changes to commit (no untracked files and no modifications), inform the user and stop.

3. **View the diff**: Run `git diff` to see unstaged changes and `git diff --cached` to see staged changes.

4. **Analyze changes**: Based on the diff output, determine:
   - The type of change (feature, fix, refactor, docs, style, test, chore)
   - Which files were affected
   - What the changes accomplish

5. **Stage all changes**: Run `git add -A` to stage all changes.

6. **Create commit message**: Generate a concise, descriptive commit message following these conventions:
   - Use imperative mood ("Add feature" not "Added feature")
   - First line: Brief summary (50 chars or less if possible)
   - If needed, add a blank line followed by more detailed explanation
   - Focus on "why" rather than "what"

7. **Create the commit**: Create the commit with the generated message using a HEREDOC format:
   ```bash
   git commit -m "$(cat <<'EOF'
   Commit message here

   Co-Authored-By: Claude Opus 4.5 <noreply@anthropic.com>
   EOF
   )"
   ```

8. **Verify success**: Run `git status` to confirm the commit was created successfully.

9. **Report result**: Show the user what was committed and the commit message used.

## Notes

- Do not push to remote - only create the local commit
- Do not commit files that appear to contain secrets (.env, credentials, API keys)
- If changes span multiple unrelated areas, suggest splitting into multiple commits
