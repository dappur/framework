# Changelog

## [Unreleased]
### No Changes

## [3.2.0]
## Notes
- This update requires a couple of changes to the theme files
	- Please see [53015cd](https://github.com/dappur/theme-dappur/commit/53015cdf33c2a947195f6ecd6068945ccdbd3680) for the required changes

### Added
- Twig StringLoader extension for custom routes

### Removed
- Hardcoded home, privacy and terms pages
- Header and footer from error pages

### Changed
- Updated dependencies in `composer.lock` file
- Cleaned up `/app/bootstrap/routes` files
- Moved the following pages out of the template and into the `Pages` section of the dashboard
	- `/` => `app\views\dappur\home.twig`
	- `/privacy` => `app\views\dappur\privacy.twig`
	- `/terms` => `app\views\dappur\terms.twig`
- Moved custom routes out of `public\index.php` and into `app\routes\custom.php`

## [3.1.2] - 2018-10-11
### Notes
- This updated required a small change for the menu display on the templates.
	- Please see [7dcf1f7](https://github.com/dappur/theme-dappur/commit/7dcf1f776b01415b32378509e352c6b12345e3e8) for changes to `dappur/inc/navbar.twig`
	- Please see [461d6fe](https://github.com/dappur/theme-AdminLTE/commit/461d6fe2b917a822d8fb7b0bdbfc3de2774b5d31) for changes to `AdminLTE/inc/admin-sidebar.twig`
	- Please see [2ea8d16](https://github.com/dappur/theme-AdminLTE/commit/2ea8d16c3fe28fc421a5679bcf9d90c8110cbc20) for changes to `AdminLTE/menus.twig`

### Added
- Config Boolean option to menu items.  If set, the menu item will only show if the assiciated config boolean is set to `true`.  Please see notes for template changes

## [3.1.1] - 2018-10-11
### Added
- Can now toggle in settings error emails by type.

### Fixed
- Vagrant ports now mirror on both host/vm for http and mysql.  If you have multiple projects, you simply only have to change the ports at the top of the Vagrantfile

### Changed
- Code cleanup in dependencies
- updated `settings.json.dist`

## [3.1.0] - 2018-09-16
### Notes
The manual installation process has changed.  Please see the [Installation Instructions](https://github.com/dappur/framework#install-with-dapp) in the README.  It is recommended that you use the [dApp CLI](https://github.com/dappur/dapp) for a simple installation.

### Removed
- Both frontend and dashboard themes have been removed from the view folder. They have been moved to their own repositories.
	- [Dappur - Frontend Theme](https://github.com/dappur/theme-dappur)
	- [AdminLTE - Dashboard Theme](https://github.com/dappur/theme-AdminLTE)

### Changed
- Removed redundant `Controller.php` classes

## [3.0.3] - 2018-09-14
### Notes
This version includes support for Vagrant initially submitted by [dshimkoski](https://github.com/dshimkoski).  By using vagrant, you have instant access to a set up dev environment including:
- **Web Frontend:** http://localhost:8181
- **PhpmyAdmin:** http://localhost:8181/phpmyadmin
- **Raw Mysql:** localhost:8306
I couldn't get the `vagrant push` commands to work on my setup (Windows 10), but the goal of that would be to migrate and rollback the dev environment.  For now, those commands can be passed via `vagrant ssh -c "cd /vagrant && phinx migrate"` and `vagrant ssh -c "cd /vagrant && phinx rollback"`.  I have also changed the Vagrant `provision.sh` file so that you can re-run the provision script to migrate the database up.

### Changed
- Recaptcha is not configurable as on or off in the global settings.  Default is off.
- Changed mail settings for more generic smtp settings
- `token_secret` and `refresh_token` to text()
- `page_config` variable to `pageConfig`

### Fixed
- Cloudinary cloud name on seo settings and blog settings

## [3.0.2] - 2018-08-16
### Added
- Slim Session to global middleware

### Removed
- Removed `session_start()` and `session_set_cookie_params()` from `public/index.php`

## [3.0.1] - 2018-08-10
### Notes
In an effort to find some remaining bugs, I created a site using v3.  This update contains fixes for issues there were found during the process.  Import and Export has also been added to the custom routes and menus admin. 

### Added
- Mailgun api key and api base url to `settings.json`
- RouteName middleware to custom routes for active menu items
- Recent blog posts and recent blog comments as global twig variable
- Import/Export to Custom Pages and Menus
- Blog Sidebar and page header support to custom pages

### Fixed
- Image input on settings controls
- Added group to page settings
- Redirect when adding a page config item
- Page config middleware was only pulling the first group when multiple were present.

### Removed
- Test function from controller

## [3.0.0] - 2018-08-08
### Notes
This is the next major version release of Dappur.  Dappurware has also been updated to version 3.0.0 so that the major version will match between the two packages.

With this version release, I think I am finally happy with the way everything looks and works.  I will continue to patch bugs and make other fixes but that will be the extent of the work on the core framework/template.  My goal when I began over a year and a half ago was to create a framework that contained all of the major features that most clients request and be able to deploy itself from a github branch using ssh.  I feel as though that requirement has been met.  I plan on using the extra time for:
- Writing proper documentation
- Clean up of the Dappurware codebase
- Finish the initial version of Dapp, a command line tool
- Unit Testing

Once those items are complete, focus will be shifted to feature enhancements.  The goal is to create an addon installer within Dapp that will automatically copy necessary routes and migrations, allowing a quick and simple command line install process.
- Universal shopping cart system with pre-built support for several payment providers including:
	- Authorize.net
	- Paypal
	- Stripe
	- If you have any suggestions, send me a DM or open an issue
- Notification System with integrated emails that can be triggered within the controller.
- More robust initial admin dashboard with stats and numbers

### Added
- Cookie conse javascript notification
- Composer.lock back into repository
- Codemirror on page builder for syntax on custom js/css.

### Fixed
- Updated permission redirect to dashboard for all controllers

### Changed
- Removed composer.lock from the main repo

## [2.5.0] - 2018-07-29
### Added
- New bootstrap menu editor integrated into dashboard using the Jquery Menu Editor package from davicotico
	- Easiy add and edit the dashboard and frontend menus from the dashboard
	- Import and Export menus
	- Visual interface allowing full control over each menu item
		- Text & Icon
		- Link to Page
		- Pages to toggle for active class
		- Limit menu items to certain roles
		- Limit pages to display for authed or guest users only
		- Limit page to display based on permission
		- Control target of href
		- Tooltip text
		- HTML Element ID
		- Custom classes for each item
- Two Factor Authentication using the package from RobThree
- Option in dashboard to change user password and disable 2fa
- Create custom pages and routes with a WYSIWYG bootstrap editor. Uses Grid-Editor by Frontwise

### Fixed
- CMS Popup Window sizes in the dashboard.
- Bug in Dashboard edit role not allowing save

### Changed
- Integrated Frontwise grid editor into blog editor
- Cleaned up javascript on admin dashboard
- Cleaned up Migration template
- AdminLTE breadcrumbs and page titles
- Changed dashboard panels to AdminLTE boxes

### Removed
- Removed initial migration SQL dump.
- Removed Dashboard Bootswatch config
- Remove composer.lock from gitignore.
- `My Account` from dashboard in favor of Frontend Profile Page

## [2.4.0] - 2018-07-19
### Added
- AdminLTE Bootstrap Theme for the dashboard has replaced the old dashboard template
- Import/Export of site and page settings
- Cron job support using Jobby
- Gravatar Twig Extension
- Developer log viewer using JSTree in the dashboard

### Changed
- Fixed jsonDecode Twig extension
- Cleaned Up admin routes
- Site and page settings can now be individually edited without refreshing the page.
- Fixed Breadcrumbs on all admin pages

### Removed
- Removed settings.json from the developer dashboard.

## [2.3.2] - 2018-04-17
### Added
- Made deployment middleware work with github release events
- Added environment support to phinx.php

### Changed
- Update for PHPMailer v6
- Fixed `Insert Into Post` for the Cloudinary inputs
- Fixed featured image in dashboard media.js
- Fixed deploy URL failing CSRF check

### Removed
- Removed Dappur name from template img alt tags

## [2.3.1]
### Added
- DataTables searchable ajax on data intensive dashboard tables:
	- Users
	- Sent Emails
	- Blog Posts
	- Blog Comments
	- Contact Requests
- Initial deployment file

### Changed
- Oauth Provider `access_token` to text from varchar.

## [2.3.0]
### Added
- Separated dappurware into its own support package.
- Social Profiles to Users page in Dashboard
- Session helper to the container
- PHPMD and PHPCS into require-dev
- Admin section for Oauth2 Providers
- Oauth 2 Login Provider Support
- Added profile check for incomplete profiles

### Changed
- Simplified cloudinary twig extension to most used functions
- Fixed CSRF issue on local cms media upload
- Exclude oauth pub route from seo settings
- Exclude all global except site settings from email placeholders
- Fixed Flash messages so that each message shown in a seperate panel

### Removed
- Removed paths from the settings file.


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

### Updated
- Composer.lock dependencies

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


[Unreleased]: https://github.com/dappur/framework/compare/v3.2.0...HEAD
[3.2.0]: https://github.com/dappur/framework/compare/v3.1.2...v3.2.0
[3.1.2]: https://github.com/dappur/framework/compare/v3.1.1...v3.1.2
[3.1.1]: https://github.com/dappur/framework/compare/v3.1.0...v3.1.1
[3.1.0]: https://github.com/dappur/framework/compare/v3.0.3...v3.1.0
[3.0.3]: https://github.com/dappur/framework/compare/v3.0.2...v3.0.3
[3.0.2]: https://github.com/dappur/framework/compare/v3.0.1...v3.0.2
[3.0.1]: https://github.com/dappur/framework/compare/v3.0.0...v3.0.1
[3.0.0]: https://github.com/dappur/framework/compare/v2.5.0...v3.0.0
[2.5.0]: https://github.com/dappur/framework/compare/v2.4.0...v2.5.0
[2.4.0]: https://github.com/dappur/framework/compare/v2.3.2...v2.4.0
[2.3.2]: https://github.com/dappur/framework/compare/v2.3.1...v2.3.2
[2.3.1]: https://github.com/dappur/framework/compare/v2.3.0...v2.3.1
[2.3.0]: https://github.com/dappur/framework/compare/v2.2.0...v2.3.0
[2.2.0]: https://github.com/dappur/framework/compare/v2.1.3...v2.2.0
[2.1.3]: https://github.com/dappur/framework/compare/v2.1.2...v2.1.3
[2.1.2]: https://github.com/dappur/framework/compare/v2.1.1...v2.1.2
[2.1.1]: https://github.com/dappur/framework/compare/v2.1.0...v2.1.1
[2.1.0]: https://github.com/dappur/framework/compare/v2.0.0...v2.1.0