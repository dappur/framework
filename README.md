# Dappur PHP Framework
PHP App Framework built on the Slim PHP Micro Framework, Twig templating engine, Eloquent ORM database interactions, Phinx database migrations, Sentinel user management, Monolog w/ Logentries Support, form validation with CSRF protection, cookie management, database controlled config and Cloudinary CMS integration.

This is a lightweight full featured framework intended for PHP developers who need an open source fast and reliable framework.  Have your new projects up and running in minutes with the provided basic bootstrap pages and basic bootstrap admin.

----------

### Created Using
* [Slim](https://github.com/slimphp/Slim) - Slim is a PHP micro framework that helps you quickly write simple yet powerful web applications and APIs
* [Slim Twig-View](https://github.com/slimphp/Twig-View) - Slim Framework 3 view helper built on top of the Twig 2 templating component
* [Slim Flash Messaging](https://github.com/slimphp/Slim-Flash) - Slim Framework Flash message service provider
* [Slim CSRF](https://github.com/slimphp/Slim-Csrf) - Slim Framework 3 CSRF protection middleware
* [Slim Validation](https://github.com/awurth/slim-validation) - A validator for Slim micro-framework using [Respect\Validation](https://github.com/Respect/Validation)
* [Cartalyst Sentinel](https://github.com/cartalyst/sentinel) - PHP 5.4+ Fully-featured Authentication & Authorization System
* [Illuminate Database](https://github.com/illuminate/database) - The Illuminate Database component is a full database toolkit for PHP, providing an expressive query builder, ActiveRecord style ORM, and schema builder.
* [Monolog Logging](https://github.com/Seldaek/monolog) - Send logs to files, sockets, inboxes, databases and various web services. 
* [Fig Cookies](https://github.com/dflydev/dflydev-fig-cookies) - Cookies for PSR-7 HTTP Message Interface.
* [Phinx Database Migrations](https://github.com/robmorgan/phinx) - Phinx makes it ridiculously easy to manage the database migrations for your PHP app.
* [Cloudinary Image CDN](https://github.com/cloudinary/cloudinary_php) - Cloudinary is a cloud service that offers a solution to a web application's entire image management pipeline.

----------

### //TODO
* Create Usage Pages

----------

### Pre-Requisites
[Composer](https://getcomposer.org/) - Dependency manager is required in order to use the Dappur PHP Framework.  Installation instructions are [located here](https://getcomposer.org/doc/00-intro.md).

[Phinx](https://phinx.org/) - Phinx is required in order to utilize the database migrations.  It is recommended that you install Phinx globally via composer by running:

    composer global require robmorgan/phinx

----------

### Install Via Composer Create-Project
You can start a new project user the Composer `create-project` command.

    composer create-project dappur/framework new_app


This will clone the Dappur Framework into a new project directory called `new_app`.   It will also automatically install and update all of the required dependencies.

----------

### Project Structure
    |-- app (Non-Public App Files)
        |-- bootstrap (Container Bootstrap Folder)
            |-- controllers.php (Bind Route Controllers)
            |-- dependencies.php (Bind All Dependencies)
            |-- middleware.php (Add Global Middleware)
            |-- sentinel.php (Sentinel Configuration)
            |-- settings.php.dist (App Configuration Template)
        |-- routes (Routes)
            |-- admin.php
            |-- app.php
            |-- auth.php
        |-- src (Source Folder)
            |-- Controller (Route Controllers - Source)
                |-- AdminController.php 
                |-- AuthController.php
                |-- Controller.php
                |-- AppController.php
            |-- Customware (Custom Php Classes)
                |-- Customware.php
            |-- Dappurware (Official Dappur Classes)
                |-- Dappurware.php
                |-- Sentinel.php
                |-- SiteConfig.php
            |-- Middleware (Slim Middleware Classes)
                |-- AdminMiddleware.php
                |-- AuthMiddleware.php
                |-- CsrfMiddleware.php
                |-- GuestMiddleware.php
                |-- Middleware.php
            |-- Migration (Eloquent Migration Class)
                |-- Migration.php
            |-- Model (Database Models)
                |-- Config
                |-- Roles
                |-- RoleUsers
                |-- Users
            |-- TwigExtension (Twig Extensions)
                |-- Asset.php
                |-- Cloudinary.php
                |-- Csrf.php
                |-- JsonDecode.php
        |-- views (Twig Templates)
            |-- default (Default Template Folder)
                |-- Admin (Default Template Admin)
                    |-- inc
                        |-- flash.twig
                        |-- header.twig
                        |-- navi.twig
                        |-- sidebar.twig
                    |-- macros
                        |-- global-config.twig
                    |-- dashboard.twig
                    |-- global-settings.twig
                    |-- roles-edit.twig
                    |-- users-add.twig
                    |-- users-edit.twig
                    |-- users.twig
                |-- App (Default Template App)
                    |-- inc
                        |-- flash.twig
                        |-- navbar.twig
                    |-- macros
                        |-- form.twig
                    |-- home.twig
                    |-- login.twig
                    |-- register.twig
                |-- admin.twig
                |-- app.twig
    |-- database (Database/Migration Files)
        |-- migrations (Phinx Migrations Folder)
            |-- 20170118012924_init_database.php
        |-- sql (Raw SQL Folder)
            |-- init-database.sql
        |-- templates (Phinx Migration Template)
            |-- create-template.php
    |-- public (Public Directory)
        |-- assets (Public Template Assets)
            |-- default (Default template Public Assets)
                |-- css (CSS Scripts)
                |-- fonts (Fonts)
                |-- js (Javascript)
        |-- .htaccess
        |-- index.php
    |-- storage (Log and Cache Storage)
        |-- cache (Cache Folder)
            |-- twig (Twig Cache - If Enabled)
        |-- log (Log Folder)
            |-- monolog (Monolog Logs)
    |-- phinx.php (Phinx Config File) 

----------

### Configure Project and Database
Configuring your new project is simple.  Rename `settings.php.dist` to `settings.php` and configure the following options:
```
db->host
db->port
db->database
db->username
db->password
logger->name
logger->log_path
logger->le_token (Optional)
cloudinary (Optional)
```
Once you have the `settings.php` file configured, all you have to do is navigate to your root project directory from a terminal and run the first migration:
```
phinx migrate
```

----------

### Run & Test Project
Once you have successfully done the initial migration, you can simply use PHP's built in web server to test your application by running the following from your root project directory:
```bash
php -S localhost:8181 -t public/
```

You should then see a confirmation similar to: 
```bash
PHP Development Server started at
Listening on http://localhost:8181
Document root is /User/Dappur/ProjectRoot/public
Press Ctrl-C to quit.
```
You can then navigate to [http://localhost:8181](http://localhost:8181) to view your project.

----------

### Pre-Made Bootstrap Template
This framework comes with several pre-made Bootstrap 3 pages to help get your project moving. These basic pages include:

 - Home Page 
 - Login Page 
 - Registration Page

All of these pages and their respective controllers/views provide you an insight into how the framework functions including form validation, CSRF, working with Eloquent ORM and other plugins.  You can expand on the default template or create a completely new template using Twig and the front-end framework of your choosing.

**Home Page**
![Home Page](http://res.cloudinary.com/dappur/image/upload/v1492305016/framework/screenshots/home-page.png)

**Registration Page**
![Registration](http://res.cloudinary.com/dappur/image/upload/v1492305016/framework/screenshots/register.png)

**Login Page**
![Login](http://res.cloudinary.com/dappur/image/upload/v1492305016/framework/screenshots/login.png)

----------

### Admin Interface
In addition to the few basic front end templates, this framework also comes pre-built with a basic Bootstrap 3 admin dashboard.  The dashboard allows an admin to:

- Create/View/Update/Delete Users
- Create/View/Update/Delete Roles
- Manage role and individual user permissions
- Create/View/Update Global Settings which are accessible from within the app container.

This dashboard can be accessed automatically by logging in with the admin user credentials.

**Default Admin Username:** admin
**Default Admin Password:** admin123

It is HIGHLY recommended that you change the default admin password to one of your choosing by modifying line 138 of `database/migrations/20170118012924_init_database.php`.

**Admin Dashboard**
![Admin Dashboard](http://res.cloudinary.com/dappur/image/upload/v1492305016/framework/screenshots/admin-dashboard.png)

**My Account**
![My Account](http://res.cloudinary.com/dappur/image/upload/v1492305016/framework/screenshots/my-account.png)

**User Management**
![Users](http://res.cloudinary.com/dappur/image/upload/v1492305016/framework/screenshots/users.png)

**Add User**
![Add User](http://res.cloudinary.com/dappur/image/upload/v1492305016/framework/screenshots/users-add.png)

**Edit User Roles**
![Roles Edit](http://res.cloudinary.com/dappur/image/upload/v1492305016/framework/screenshots/roles-edit.png)

**Global Settings**
![Settings](http://res.cloudinary.com/dappur/image/upload/v1492305016/framework/screenshots/settings.png)

**Cloudinary Media Library**
![Settings](http://res.cloudinary.com/dappur/image/upload/v1492305016/framework/screenshots/media-library.png)