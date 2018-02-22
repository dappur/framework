# Changelog
All notable changes to this project will be documented in this file as of v2.1.0.

## [Unreleased]

## [2.2.0]
### Added
- Get featured image from youtube video on blog and seo
- Prevent directory listing of uploads folder
- Added twitter player card width and height options
- Added option to delete SEO video in admin
- Added per page SEO config options

### Changed
- Required featured image if video on blog (for seo purposes)
- Mage SEO image required
- Changed default OG image
- Cleaned Up navbar Logo
- Alphabetized controllers.php file
- Required page numbers to be numeric on blog
- Updated Readme
- Updated framework description

### Removed
- Removed logo from homepage in leiu of html config

## [2.1.3] - 2018-02-13
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

### Notes
- As of this version, all database changed will be reflected in individual migration files.  However, the inital migration SQL file will contain a dump of ALL of the migrations.

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


[Unreleased]: https://github.com/dappur/framework/compare/v2.2.0...HEAD
[2.2.0]: https://github.com/dappur/framework/compare/v2.1.3...v2.2.0
[2.1.3]: https://github.com/dappur/framework/compare/v2.1.2...v2.1.3
[2.1.2]: https://github.com/dappur/framework/compare/v2.1.1...v2.1.2
[2.1.1]: https://github.com/dappur/framework/compare/v2.1.0...v2.1.1
[2.1.0]: https://github.com/dappur/framework/compare/v2.0.0...v2.1.0