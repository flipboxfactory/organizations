Changelog
=========
## 1.0.4 - 2019-01-29
### Changed
- Managing organization user roles has moved to an element action.

### Added
- Added a 'state' to Organization Users

## 1.0.3 - 2019-01-25
### Changed
- Query params are prepared in a more simple and direct way

## 1.0.2 - 2019-01-15
### Changed
- Gracefully handle looking up a record by the unique handle or id.

## 1.0.1 - 2019-01-11
### Changed
- Passing an element to Organization::findOne() will check and return the element

## 1.0.0 - 2019-01-10
### Added
- GA release

## 1.0.0-rc.25 - 2018-08-01
### Changed
- Reset query relations table joins on new execution of query.

## 1.0.0-rc.24 - 2018-07-26
### Fixed
- Rare instance when multiple of the same relations could be added to a User Query

## 1.0.0-rc.23 - 2018-07-25
### Added
- User type association HUD is grouped by source headings
- Sidebar navigation to easily get to/from settings and elements

## 1.0.0-rc.22 - 2018-07-06
### Added
- Organization Type field type can customize the drop down first option

## 1.0.0-rc.21 - 2018-06-25
### Fixed
- User's organization list should appear even if no organizations are present.

## 1.0.0-rc.20 - 2018-06-21
### Changed
- User detail sidebar can add/remove organizations.

## 1.0.0-rc.19 - 2018-06-20
### Added
- User detail sidebar lists the organizations that a user is associated to.

## 1.0.0-rc.18 - 2018-06-20
### Fixed
- Bug when saving a user association another association may get deleted.

## 1.0.0-rc.17 - 2018-06-19
### Fixed
- Issue where saving a new organization without a type associated would throw and exception.

## 1.0.0-rc.16 - 2018-06-18
### Fixed
- Issue where 'organizationId' and or 'userId' were not getting applied properly to user association queries.

## 1.0.0-rc.15 - 2018-06-18
### Removed
- The concept of state from elements per [#4](https://github.com/flipboxfactory/organizations/issues/4).

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
