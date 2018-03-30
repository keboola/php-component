# Changelog


## master

**BC break:**
- [\#19](https://github.com/keboola/php-component/pull/19): path when writing manifests is now relative to `out/files` (or `out/tables` respectively).
    - before: `$manager->writeTableManifest('/data/out/tables/table.csv', /*...*/)`
    - after:`$manager->writeTableManifest('table.csv', /*...*/)`
- [\#23](https://github.com/keboola/php-component/pull/23): `BaseComponent::setEnvironment()` is now static to allow calling in tests bootstrap without instantiating the component itself.

**Feature:**
- [\#20](https://github.com/keboola/php-component/pull/20): options object `WriteTableManifestOptions` to allow setting only some of the manifest parameters when writing manifest

## 2.1.0

**Feature:**

- [\#12](https://github.com/keboola/php-component/pull/12): more options when writing a manifest
- [\#12](https://github.com/keboola/php-component/pull/12): new method `writeTableManifestFromArray()`

## 2.0.0

**BC break:**
- [\#10](https://github.com/keboola/php-component/pull/10): Typehints added in `BaseConfigDefinition` which means previous implementation without typehints are not valid anymore.
