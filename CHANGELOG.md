# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 4.0.0 - TBD
### Added
- Switched to [PSR-11](https://www.php-fig.org/psr/psr-11) for container interoperability. While `container-interop`
  was already PSR-11 compliant, there is no reason to maintain it
- Added auto-wiring to help make the container less reliant on full manual configuration

### Changed
- `set` no longer creates a `callable`, instead it stores the value given to it

## 3.0.0 - 2016-03-11
### Added
- Implemented [Container Interoperability](https://github.com/container-interop/container-interop). Zit was already
  `container-interop` compatible, but it now implements the interface and throws an exception that implements
  `Interop\Container\Exception\NotFoundException` when a item is not found. This exception extends
  `\InvalidArgumentException`, so 3.0.0 should be nearly 100% backwards-compatible, but I'm bumping the major version
  just in case.
- Added DocBlocks throughout the code

### Changed
- `setFactory()` now accepts any `callable` instead of specifically a `Closure`
- `set()` is now fluent (returns the container for chaining)
- Switched to md4 hashing for speed improvements (we don't need the security of md5)

### Fixed
- Fixed a typo in the exception message thrown from `__call` if a method does not exist

### Removed
- Dropped support for PHP 5.3
- Removed deprecated function `setParam`


## 2.0.0 - 2014-07-25
### Added
- Added the "Factory" variant.  This could cause backwards compatibility issues if you have set up objects that end with the word "factory".

### Changed
- Updated the `delete` method so that it clears out objects, callbacks, and factories, which could have some abnormal BC issues as well.

### Deprecated
- Deprecated the `setParam` method in favor of checking whether the parameter passed to `set` is callable.



