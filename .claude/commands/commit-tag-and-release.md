# Commit, Tag, and Release

Bump the plugin version, commit changes, create a git tag, and push to trigger a release.

## Arguments

- `$ARGUMENTS` - Required: Version bump type (patch, minor, or major)

## Instructions

Execute the following steps:

1. **Validate the argument**: Ensure `$ARGUMENTS` is one of: `patch`, `minor`, or `major`. If not provided or invalid, ask the user to specify the bump type.

2. **Read current version**: Read the file `carramba-woo-order-tracking.php` and extract the current version from:
   - The plugin header: `Version: X.Y.Z`
   - The constant: `define('CWOT_VERSION', 'X.Y.Z');`

3. **Calculate new version**: Based on the bump type:
   - `patch`: X.Y.Z → X.Y.(Z+1)
   - `minor`: X.Y.Z → X.(Y+1).0
   - `major`: X.Y.Z → (X+1).0.0

4. **Update version in plugin file**: Update both locations in `carramba-woo-order-tracking.php`:
   - Line with `Version:` in the plugin header
   - Line with `define('CWOT_VERSION',`

5. **Ask for release message**: Use AskUserQuestion to ask the user for a release message/changelog for this version. Provide a default option like "Release version X.Y.Z" and allow custom input.

6. **Create git commit**: Stage and commit the changes with message: `Bump version to X.Y.Z`

7. **Create git tag**: Create an annotated tag with the release message:
   ```bash
   git tag -a vX.Y.Z -m "Release message here"
   ```

8. **Push to repository**: Push both the commit and the tag:
   ```bash
   git push && git push --tags
   ```

9. **Confirm success**: Report the new version and that the release workflow will be triggered.

## Example Usage

```
/commit-tag-and-release patch
/commit-tag-and-release minor
/commit-tag-and-release major
```
