# Compojoom Hotspots

See README.md for what this repo is and where the code came from. `site/` mirrors the Joomla root of the client site.

The refactor plan and progress live in `.claude/ROADMAP.md`. Tick items off there as they are done.

## Build and check

- Build the package: `php build/build.php` (output in `dist/`, git-ignored).
- Lint: `find site build -name '*.php' -exec php -d error_reporting=-1 -l {} \;` must print no Deprecated/Warning lines (CI runs this on PHP 8.1 and 8.4).
- New and changed files keep their line endings (many Compojoom files use CRLF).
- Mark changes to Compojoom code with `TLWebdesign patch <date>` and list them in README "Local changes".

## Target

Joomla 5 and Joomla 6 only. No Joomla 3/4 support needed. The client site runs Joomla 5.4.10 (2026-11-02), so test there first; Joomla 6 support is for the later upgrade.

- PHP 8.1 minimum, and must run on PHP 8.4 without deprecation notices. So no PHP 8.2+ only syntax (readonly classes, DNF types, typed class constants), but use explicit nullable types (`?Foo $x = null`), declare all properties (no dynamic properties), and no required parameters after optional ones.
- Lint with the local PHP 8.4: `php -d error_reporting=-1 -l <file>`. Lint only catches compile-time issues; runtime deprecations need testing.
- Use only APIs that exist in both 5.4 and 6.x. No `J`-prefixed legacy classes, no reliance on the Backward Compatibility plugin.
- Modern structure: namespaced `src/`, `services/provider.php`, MVCFactory, event classes for plugins.

## Joomla reference code (read-only, do not edit or switch branches there)

- `../joomla-cms` is a git checkout that is often on a PR branch. Read the target versions from tags instead of checking them out:
  - Joomla 5: tag `5.4.10`, Joomla 6: tag `6.1.4`
  - `git -C ../joomla-cms show 6.1.4:libraries/src/Factory.php`
  - `git -C ../joomla-cms grep -n 'function getIdentity' 5.4.10 -- libraries/src`
  - Compare an API across versions: `git -C ../joomla-cms diff 5.4.10 6.1.4 -- libraries/src/MVC`
- Core components to copy patterns from: `administrator/components/com_content`, `com_contact`, `com_banners` (services/provider.php, src/Extension, src/Controller, src/Model, src/View, tmpl).
- Joomla 3.10 ELTS: `../Joomla3.10.999`. Only to understand what the old code does (`JFactory`, `JControllerLegacy`, `JTable`, ...). Class aliases are in `libraries/classmap.php`.

## State of the code

- Mostly Joomla 3 style: about 142 of 267 PHP files use legacy classes, only 7 use `Joomla\CMS\` namespaces. No `services/provider.php`, no namespaced `src/`.
- Depends on `lib_compojoom` 5.3.6 (`site/libraries/compojoom`), which is also Joomla 3 style.
- Local fixes are marked `TLWebdesign patch`; keep them when refactoring.
- The client's CB plugin (separate private repo) uses `HotspotsGeocoder`; keep that class (or an alias) working.
