Changelog
=========
## Unreleased
### Removed
- State field from elements 

## 1.0.0-rc.14 - 2018-06-13
### Fixed
- User Type associations were not saving correctly when all associations were un-selected.

### Added
- Intro to unit tests

## 1.0.0-rc.13 - 2018-06-07
### Added
- Services have a configurable cache duration and dependency that can be set via the plugin's settings config.

### Removed
- OrganizationQuery no longer automatically adds 'types' as an eager loading key 

## 1.0.0-rc.12 - 2018-05-24
### Fixed
- Error when passed attempting to set types on an organization with a null value
- Removing user from organization was causing an error
- Organization type urls on listing page and handle slug on detail page 

## 1.0.0-rc.11 - 2018-05-17
### Fixed
- Reference to legacy sort order property

## 1.0.0-rc.10 - 2018-05-08
### Fixed
- Typo in migration file renaming which prevented the migration from running

## 1.0.0-rc.9 - 2018-05-07
### Changed
- User associations can be sorted by user and organization.

## 1.0.0-rc.8 - 2018-05-01
### Added
- Organization Type single select field type

## 1.0.0-rc.7 - 2018-04-30
### Fixed
- Element and Query behaviors were not getting set due to new Craft CMS register behaviors event. 

## 1.0.0-rc.6 - 2018-04-28
### Fixed
- Default field layout was being deleted with organization type layout was selected
- Organization type field layout defaults to main default if null (instead of loading an empty layout).

## ## 1.0.0-rc.5 - 2018-04-28
### Changed
- Renamed the concept of user categories to user types (not backwards compatible)

## ## 1.0.0-rc.4 - 2018-04-23
- Updated dependencies

## 1.0.0-rc.3 - 2018-04-17
### Changed
- load more action when viewing more than associated organization users
- fixed issue where Join Date was not getting updated correctly.

## 1.0.0-rc.2 - 2018-03-28
### Changed
- dependency templates were not registered in admin views

## 1.0.0-rc.1 - 2018-03-26
### Changed
- Renamed plugin class to `Organizations`
- Namespace is now using `organizations` instead of `organization`

## 1.0.0-rc - 2018-03-25
Initial release.
