#### v4.0.5
- [#1] Implement `getDefaultDriver` on `FactoryManager` for Laravel 4.2 compatibility. Thanks to [@barryvdh](https://github.com/barryvdh).

#### v4.0.4
- Change `Asset::prepareFilters` code to avoid name resolution conflicts under
some situations.

#### v4.0.3
- Fix `Manifest::all()` to return an array instead of a `Illuminate\Support\Collection`
object. This fixes `FileSystemCleaner` operations and the `--tidy-up` command
which were using `array_keys` on that method output.

#### v4.0.2
- Fix `Asset::prepareFilters()` method to work properly.

#### v4.0.1
- Change `composer.json` dependencies to make it compatible with Laravel 4.1.
- Change deprecated `FileSystem::getRemote()` calls to `FileSystem::get()`.
- Fix test suite.

#### v4.0.0 Beta 3

- Split the collections and aliases into their own configuration files.
- Filter method chaining with syntactical sugar by prefixing with `and`, e.g., `andWhenProductionBuild()`.

#### v4.0.0 Beta 2

- Added logging when assets, directories, and filters are not found or fail to load.
- Allow logging to be enabled or disabled via configuration.
- Warn users when cURL is being used to detect an assets group.
- Allow an array of filters to be applied to an asset.
- Added `whenProductionBuild` and `whenDevelopmentBuild` as filter requirements.
- `CssMin` and `JsMin` are only applied on a production build and not on the production environment.
- Added `raw` method as an alias to `exclude`.
- Entire directory or collection can be set as raw so original path is used instead of assets being built.
- Development builds only happen for a collection that is used on the loaded request.
- Added `rawOnEnvironment` to serve the asset raw on a given environment or environments.


#### v4.0.0 Beta 1

- Collections are displayed with `basset_javascripts()` and `basset_stylesheets()`.
- Simplified the asset finding process.
- Can no longer prefix paths with `path:` for an absolute path, use a relative path from public directory instead.
- Requirements can be applied to filters to prevent application if certain conditions are not met.
- Filters can find any missing constructor arguments such as the path to Node, Ruby, etc.
- Default `application` collection is bundled.
- `basset:compile` command is now `basset:build`.
- Old collection builds are cleaned automatically but can be cleaned manually with `basset --tidy-up`.
- Packages can be registered with `Basset::package()` and assets can be added using the familiar namespace syntax found throughout Laravel.
- `Csso` support with `CssoFilter`.
- Fixed issues with `UriRewriteFilter`.
- Development collections are pre-built before every page load.
- Build and serve pre-compressed collections.
- Use custom format when displaying collections.
- Added in Blade view helpers: `@javascripts`, `@stylesheets`, and `@assets`.
- Assets maintain the order that they were added.
