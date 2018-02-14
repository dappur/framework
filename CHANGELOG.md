# Changelog
All notable changes to this project will be documented in this file as of v2.1.0.

## [Unreleased]
### Added
- Added Recaptcha to the login page.
- Sweet Alert 2 has been added to the base.twig for the front end theme
- Added page config for terms & conditions.
- Made change password feature more secure/interactive.

### Changed
- Moved settings.json to the document root.
- Fixed bug with page settings menu items not showing as active
- Updated Readme
- Fixed bug in assets allowing traverse of server directories.

### Removed
- Removed mcrypt_create_iv() usage in Dappurware\Email;
- Removed unecessary array from database settings.

## [2.1.2] - 2018-02-11
### Added
- Changelog

### Changed
- Fixed permissions in AdminEmail and AdminSettings controllers.

## [2.1.1] - 2018-02-10
### Changed
- Updated Readme and composer descriptions.
- Changed the way Controllers check for permissions from sentinel.
- Updated init-database.sql file for newest migration

## [2.1.0] - 2018-02-09
### Added
- HTML global/page config option.
- Blog integration with admin
- Email site errors (in Site Settings now).
- Can now send email to users individually from the Admin.
- Profile page for users

### Changed
- Various fixes to local cms frontend.
- Updated error management system.
- Various layout and Admin fixes
- Settings.json now has en environment that is defined to select db.
- Code cleanup and various reported/unreported bug fixes.
- Internalized all assets, they are now served from the view folder and not from the public dir.


[Unreleased]: https://github.com/dappur/framework/compare/v2.1.2...HEAD
[2.1.2]: https://github.com/dappur/framework/compare/v2.1.1...v2.1.2
[2.1.1]: https://github.com/dappur/framework/compare/v2.1.0...v2.1.1
[2.1.0]: https://github.com/dappur/framework/compare/v2.0.0...v2.1.0