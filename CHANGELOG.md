# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.0.0alpha1 - 2018-02-27

### Added

- Nothing.

### Changed

- [zendframework/zend-expressive-authentication-basic#5](https://github.com/zendframework/zend-expressive-authentication-basic/pull/5)
  changes the constructor of the `Mezzio\Authentication\Basic\BasicAccess`
  class to accept a callable `$responseFactory` instead of a
  `Psr\Http\Message\ResponseInterface` response prototype. The
  `$responseFactory` should produce a `ResponseInterface` implementation when
  invoked.

- [zendframework/zend-expressive-authentication-basic#5](https://github.com/zendframework/zend-expressive-authentication-basic/pull/5)
  updates the `BasicAccessFactory` to no longer use
  `Mezzio\Authentication\ResponsePrototypeTrait`, and instead always
  depend on the `Psr\Http\Message\ResponseInterface` service to correctly return
  a PHP callable capable of producing a `ResponseInterface` instance.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.2.0 - 2018-02-26

### Added

- Nothing.

### Changed

- [zendframework/zend-expressive-authentication-basic#4](https://github.com/zendframework/zend-expressive-authentication-basic/pull/4)
  changes the zendframework/zend-expressive-authentication minimum supported
  version to 1.0.0alpha3.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.3 - 2018-02-26

### Added

- Nothing.

### Changed

- [zendframework/zend-expressive-authentication-basic#3](https://github.com/zendframework/zend-expressive-authentication-basic/pull/3)
  updates the zendframework/zend-expressive-authentication minimum supported
  version to 0.3.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.2 - 2017-12-13

### Added

- [zendframework/zend-expressive-authentication-basic#1](https://github.com/zendframework/zend-expressive-authentication-basic/pull/1)
  adds support for the 1.0.0-dev branch of mezzio-authentication.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.1 - 2017-11-28

### Added

- Adds support for mezzio-authentication 0.2.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.0 - 2017-11-09

Initial release.

### Added

- Everything.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
