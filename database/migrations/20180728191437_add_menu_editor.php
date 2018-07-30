<?php

use \Dappur\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddMenuEditor extends Migration
{
    /**
    Write your reversible migrations using this method.

    Dappur Framework uses Laravel Eloquent ORM as it's database connector.

    More information on writing eloquent migrations is available here:
    https://laravel.com/docs/5.4/migrations

    Remember to use both the up() and down() functions in order to be able
    to roll back.

    Create Table Sample:
    $this->schema->create('sample', function (Blueprint $table) {
        $table->increments('id');
        $table->string('email')->unique();
        $table->string('last_name')->nullable();
        $table->string('first_name')->nullable();
        $table->timestamps();
    });

    Drop Table Sample:
    $this->schema->dropIfExists('sample');
    */
    
    public function up()
    {
        $this->schema->create('menus', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->json('json')->nullable();
            $table->timestamps();
        });

        $ins = new \Dappur\Model\Menus;
        $ins->id = 1;
        $ins->name = "Dappur Menu";
        $ins->json = '[{"text":"Home","href":"","icon":"fa fa-home","target":"_self","title":"","auth":"false","page":"home","guest":"false","roles":[],"active":["home"],"classes":[],"html_id":"","tooltip":"","permission":""},{"text":"Blog","href":"","icon":"fa fa-eye","target":"_self","title":"","auth":"false","page":"blog","guest":"false","roles":[],"active":["blog","blog-author","blog-category","blog-post","blog-tag"],"classes":[],"html_id":"","tooltip":"","permission":""},{"text":"Contact","href":"","icon":"fa fa-phone","target":"_self","title":"","auth":"false","page":"contact","guest":"false","roles":[],"active":["contact"],"classes":[],"html_id":"","tooltip":"","permission":""},{"text":"Login","href":"","icon":"fa fa-lock","target":"_self","title":"","auth":"false","page":"login","guest":"true","roles":[],"active":["activate","forgot-password","login"],"classes":[],"html_id":"","tooltip":"","permission":""},{"text":"Register","href":"","icon":"fa fa-user-circle-o","target":"_self","title":"","auth":"false","page":"register","guest":"true","roles":[],"active":["register"],"classes":[],"html_id":"","tooltip":"","permission":""},{"text":"{{user.username}}","href":"","icon":"fa fa-user-circle-o","target":"_self","title":"","auth":"true","page":"","guest":"false","roles":[],"active":["profile"],"classes":[],"html_id":"","tooltip":"","permission":"","children":[{"text":"Admin Dashboard","href":"","icon":"fa fa-dashboard","target":"_self","title":"","auth":"true","page":"dashboard","guest":"false","roles":[],"active":["dashboard"],"classes":[],"html_id":"","tooltip":"","permission":"dashboard.*"},{"text":"Profile","href":"","icon":"fa fa-clipboard","target":"_self","title":"","auth":"true","page":"profile","guest":"false","roles":[],"active":["profile","profile-incomplete"],"classes":[],"html_id":"","tooltip":"","permission":""},{"text":"Logout","href":"","icon":"fa fa-lock","target":"_self","title":"","auth":"true","page":"logout","guest":"false","roles":[],"active":[],"classes":[],"html_id":"","tooltip":"","permission":""}]}]';
        $ins->save();
        
        $ins = new \Dappur\Model\Menus;
        $ins->id = 2;
        $ins->name = "AdminLTE Sidebar";
        $ins->json = '[{"text":"Dashboard","href":"","icon":"fa fa-dashboard","target":"_self","title":"","auth":"true","page":"dashboard","guest":"false","roles":[],"active":["dashboard"],"classes":[],"html_id":"","tooltip":"","permission":"dashbaord.view"},{"text":"Users","href":"","icon":"fa fa-users","target":"_self","title":"","auth":"true","page":"","guest":"false","roles":[],"active":["admin-roles-add","admin-roles-edit","admin-users","admin-users-add","admin-users-edit"],"classes":[],"html_id":"","tooltip":"","permission":"user.view","children":[{"text":"All Users","href":"","icon":"fa fa-users","target":"_self","title":"","auth":"true","page":"admin-users","guest":"false","roles":[],"active":["admin-roles-add","admin-roles-edit","admin-users","admin-users-edit"],"classes":[],"html_id":"","tooltip":"","permission":"user.view"},{"text":"New User","href":"","icon":"fa fa-plus","target":"_self","title":"","auth":"false","page":"admin-users-add","guest":"false","roles":[],"active":["admin-users-add"],"classes":[],"html_id":"","tooltip":"","permission":"user.update"}]},{"text":"Emails","href":"","icon":"fa fa-envelope","target":"_self","title":"","auth":"true","page":"","guest":"false","roles":[],"active":["admin-email","admin-email-details","admin-email-new","admin-email-template","admin-email-template-add","admin-email-template-edit"],"classes":[],"html_id":"","tooltip":"","permission":"email.view","children":[{"text":"Sent Emails","href":"","icon":"fa fa-envelope-open","target":"_self","title":"","auth":"true","page":"admin-email","guest":"false","roles":[],"active":["admin-email","admin-email-details"],"classes":[],"html_id":"","tooltip":"","permission":"email.view"},{"text":"New Email","href":"","icon":"fa fa-send","target":"_self","title":"","auth":"true","page":"admin-email-new","guest":"false","roles":[],"active":["admin-email-new"],"classes":[],"html_id":"","tooltip":"","permission":"email.create"},{"text":"Templates","href":"","icon":"fa fa-envelope-square","target":"_self","title":"","auth":"false","page":"admin-email-template","guest":"false","roles":[],"active":["admin-email-template","admin-email-template-edit"],"classes":[],"html_id":"","tooltip":"","permission":"email.view"},{"text":"New Template","href":"","icon":"fa fa-plus","target":"_self","title":"","auth":"true","page":"admin-email-template-add","guest":"false","roles":[],"active":["admin-email-template-add"],"classes":[],"html_id":"","tooltip":"","permission":"email.create"}]},{"text":"Blog","href":"","icon":"fa fa-newspaper-o","target":"_self","title":"","auth":"true","page":"","guest":"false","roles":[],"active":["admin-blog","admin-blog-add","admin-blog-categories-edit","admin-blog-comment-details","admin-blog-comments","admin-blog-edit","admin-blog-preview","admin-blog-tags-edit"],"classes":[],"html_id":"","tooltip":"","permission":"blog.view","children":[{"text":"Blog Posts","href":"","icon":"fa fa-newspaper-o","target":"_self","title":"","auth":"true","page":"admin-blog","guest":"false","roles":[],"active":["admin-blog","admin-blog-categories-edit","admin-blog-edit","admin-blog-tags-edit"],"classes":[],"html_id":"","tooltip":"","permission":"blog.view"},{"text":"Comments","href":"","icon":"fa fa-comment","target":"_self","title":"","auth":"true","page":"admin-blog-comments","guest":"false","roles":[],"active":["admin-blog-comment-details","admin-blog-comments"],"classes":[],"html_id":"","tooltip":"","permission":"blog.view"},{"text":"New Post","href":"","icon":"fa fa-plus","target":"_self","title":"","auth":"true","page":"admin-blog-add","guest":"false","roles":[],"active":["admin-blog-add"],"classes":[],"html_id":"","tooltip":"","permission":"blog.create"}]},{"text":"Local CMS","href":"","icon":"fa fa-image","target":"_self","title":"","auth":"true","page":"","guest":"false","roles":[],"active":[],"classes":[],"html_id":"media-menu","tooltip":"","permission":"media.local"},{"text":"Cloudinary CMS","href":"","icon":"fa fa-soundcloud","target":"_self","title":"","auth":"true","page":"","guest":"false","roles":[],"active":[],"classes":[],"html_id":"cloudinary-menu","tooltip":"","permission":"media.cloudinary"},{"text":"Contact Requests","href":"","icon":"fa fa-address-book","target":"_self","title":"","auth":"true","page":"admin-contact","guest":"false","roles":[],"active":["admin-contact"],"classes":[],"html_id":"","tooltip":"","permission":"contact.view"},{"text":"Oauth2 Providers","href":"","icon":"fa fa-user-circle","target":"_self","title":"","auth":"true","page":"","guest":"false","roles":[],"active":["admin-oauth2","admin-oauth2-add","admin-oauth2-edit"],"classes":[],"html_id":"","tooltip":"","permission":"oauth2.view","children":[{"text":"Providers","href":"","icon":"fa fa-codepen","target":"_self","title":"","auth":"true","page":"admin-oauth2","guest":"false","roles":[],"active":["admin-oauth2","admin-oauth2-edit"],"classes":[],"html_id":"","tooltip":"","permission":"oauth2.view"},{"text":"New Provider","href":"","icon":"fa fa-plus","target":"_self","title":"","auth":"true","page":"admin-oauth2-add","guest":"false","roles":[],"active":["admin-oauth2-add"],"classes":[],"html_id":"","tooltip":"","permission":"oauth2.create"}]},{"text":"SEO","href":"","icon":"fa fa-sitemap","target":"_self","title":"","auth":"true","page":"","guest":"false","roles":[],"active":["admin-seo","admin-seo-add","admin-seo-edit"],"classes":[],"html_id":"","tooltip":"","permission":"seo.view","children":[{"text":"All Configs","href":"","icon":"fa fa-sitemap","target":"_self","title":"","auth":"true","page":"admin-seo","guest":"false","roles":[],"active":["admin-seo-add","admin-seo-edit"],"classes":[],"html_id":"","tooltip":"","permission":"seo.update"},{"text":"New SEO Config","href":"","icon":"fa fa-plus","target":"_self","title":"","auth":"true","page":"admin-seo-add","guest":"false","roles":[],"active":["admin-seo-add"],"classes":[],"html_id":"","tooltip":"","permission":"seo.create"}]},{"text":"Pages","href":"","icon":"fa fa-book","target":"_self","title":"","auth":"true","page":"","guest":"false","roles":[],"active":["admin-pages","admin-pages-add","admin-pages-edit"],"classes":[],"html_id":"","tooltip":"","permission":"pages.view","children":[{"text":"All Pages","href":"","icon":"fa fa-bookmark","target":"_self","title":"","auth":"true","page":"admin-pages","guest":"false","roles":[],"active":["admin-pages","admin-pages-edit"],"classes":[],"html_id":"","tooltip":"","permission":"pages.view"},{"text":"New Page","href":"","icon":"fa fa-plus","target":"_self","title":"","auth":"true","page":"admin-pages-add","guest":"false","roles":[],"active":["admin-pages-add"],"classes":[],"html_id":"","tooltip":"","permission":"pages.create"}]},{"text":"Menus","href":"","icon":"fa fa-map","target":"_self","title":"","auth":"true","page":"admin-menus","guest":"false","roles":[],"active":["admin-menus"],"classes":[],"html_id":"","tooltip":"","permission":"menus.update"},{"text":"Global Settings","href":"","icon":"fa fa-gears","target":"_self","title":"","auth":"true","page":"settings-global","guest":"false","roles":[],"active":["settings-global"],"classes":[],"html_id":"","tooltip":"","permission":"settings.global"},{"text":"Developer Tools","href":"","icon":"fa fa-life-bouy","target":"_self","title":"","auth":"true","page":"","guest":"false","roles":["developer"],"active":["developer-logs"],"classes":[],"html_id":"","tooltip":"","permission":"settings.developer","children":[{"text":"Logfiles","href":"","icon":"fa fa-clipboard","target":"_self","title":"","auth":"true","page":"developer-logs","guest":"false","roles":["developer"],"active":["developer-logs"],"classes":[],"html_id":"","tooltip":"","permission":"settgins.developer"}]}]';
        $ins->save();

        // Update Admin Role
        $admin_role = $this->sentinel->findRoleByName('Admin');
        $admin_role->addPermission('menus.*');
        $admin_role->save();

        // Add Menu Config Types
        $configType = new Dappur\Model\ConfigTypes;
        $configType->name = 'menu';
        $configType->save();

        // Add Dappur Menu to Site Config
        $addMenu = new Dappur\Model\Config;
        $addMenu->group_id = 1;
        $addMenu->name = 'site-menu';
        $addMenu->description = "Site Menu";
        $addMenu->type_id = $configType->id;
        $addMenu->value = 1;
        $addMenu->save();
        
        // Add Dappur Menu to Site Config
        $addMenu2 = new Dappur\Model\Config;
        $addMenu2->group_id = 2;
        $addMenu2->name = 'dashboard-menu';
        $addMenu2->description = "Dashboard Menu";
        $addMenu2->type_id = $configType->id;
        $addMenu2->value = 2;
        $addMenu2->save();
    }

    public function down()
    {
        $this->schema->dropIfExists('menus');

        // Update Admin Role
        $admin_role = $this->sentinel->findRoleByName('Admin');
        $admin_role->removePermission('menus.*');
        $admin_role->save();

        \Dappur\Model\Config::whereIn('name', ['site-menu', 'dashboard-menu'])->delete();
        \Dappur\Model\ConfigTypes::where('name', 'menu')->delete();
    }
}
