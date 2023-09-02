<?php

use \Dappur\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddOauth extends Migration
{
    public function up()
    {
        $this->schema->create('oauth2_providers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('scopes')->nullable();
            $table->string('authorize_url');
            $table->string('token_url');
            $table->string('resource_url');
            $table->string('button')->nullable();
            $table->boolean('login')->default(0);
            $table->boolean('status')->default(0);
            $table->timestamps();
        });

        $this->schema->create('oauth2_users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('provider_id')->unsigned();
            $table->string('uid');
            $table->text('access_token');
            $table->text('token_secret')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('provider_id')->references('id')->on('oauth2_providers')->onDelete('cascade');
        });

        $oauth2_providers = array(
            array(
                "name" => "Facebook",
                "slug" => "facebook",
                "scopes" => "email,public_profile",
                "authorize_url" => "https://www.facebook.com/dialog/oauth",
                "token_url" => "https://graph.facebook.com/oauth/access_token",
                "resource_url" => "https://graph.facebook.com/me?fields=id,email,first_name,last_name",
                "button" => "facebook",
                "login" => 0,
                "status" => 0
            ),
            array(
                "name" => "Google",
                "slug" => "google",
                "scopes" => "email profile",
                "authorize_url" => "https://accounts.google.com/o/oauth2/auth",
                "token_url" => "https://accounts.google.com/o/oauth2/token",
                "resource_url" => "https://www.googleapis.com/oauth2/v1/userinfo",
                "button" => "google",
                "login" => 0,
                "status" => 0
            ),
            array(
                "name" => "Twitter",
                "slug" => "twitter",
                "scopes" => "",
                "authorize_url" => "https://api.twitter.com/oauth/authorize",
                "token_url" => "https://api.twitter.com/oauth2/token",
                "resource_url" => "account/verify_credentials",
                "button" => "twitter",
                "login" => 0,
                "status" => 0
            ),
            array(
                "name" => "LinkedIn",
                "slug" => "linkedin",
                "scopes" => "r_liteprofile,r_emailaddress",
                "authorize_url" => "https://www.linkedin.com/oauth/v2/authorization",
                "token_url" => "https://www.linkedin.com/oauth/v2/accessToken",
                "resource_url" => "https://api.linkedin.com/v2/me",
                "button" => "linkedin",
                "login" => 0,
                "status" => 0
            ),
            array(
                "name" => "Instagram",
                "slug" => "instagram",
                "scopes" => "basic",
                "authorize_url" => "https://api.instagram.com/oauth/authorize",
                "token_url" => "https://api.instagram.com/oauth/access_token",
                "resource_url" => "https://api.instagram.com/v1/users/self",
                "button" => "instagram",
                "login" => 0,
                "status" => 0
            ),
            array(
                "name" => "Github",
                "slug" => "github",
                "scopes" => "user",
                "authorize_url" => "https://github.com/login/oauth/authorize",
                "token_url" => "https://github.com/login/oauth/access_token",
                "resource_url" => "https://api.github.com/user",
                "button" => "github",
                "login" => 0,
                "status" => 0
            ),
            array(
                "name" => "Microsoft Live",
                "slug" => "microsoft",
                "scopes" => "wl.basic,wl.emails,wl.signin",
                "authorize_url" => "https://login.live.com/oauth20_authorize.srf",
                "token_url" => "https://login.live.com/oauth20_token.srf",
                "resource_url" => "https://apis.live.net/v5.0/me",
                "button" => "microsoft",
                "login" => 0,
                "status" => 0
            )
        );

        foreach ($oauth2_providers as $key => $value) {
            $ins = new \Dappur\Model\Oauth2Providers;
            $ins->name = $value['name'];
            $ins->slug = $value['slug'];
            $ins->scopes = $value['scopes'];
            $ins->authorize_url = $value['authorize_url'];
            $ins->token_url = $value['token_url'];
            $ins->resource_url = $value['resource_url'];
            $ins->button = $value['button'];
            $ins->login = $value['login'];
            $ins->status = $value['status'];
            $ins->save();
        }

        // Add Oauth2 Config Group
        $config = new Dappur\Model\ConfigGroups;
        $config->name = "Oauth2";
        $config->description = "Oauth2 Login Provider Settings";
        $config->save();

        $init_config = array(
            array($config->id, 'oauth2-enabled', 'Enable Oauth2', 6, 0)
        );

        // Seed Config Table
        foreach ($init_config as $key => $value) {
            $config = new Dappur\Model\Config;
            $config->group_id = $value[0];
            $config->name = $value[1];
            $config->description = $value[2];
            $config->type_id = $value[3];
            $config->value = $value[4];
            $config->save();
        }

        // Update Admin Role
        $admin_role = $this->sentinel->findRoleByName('Admin');
        $admin_role->addPermission('oauth2.*');
        $admin_role->save();
        // Update Manager Role
        $manager_role = $this->sentinel->findRoleByName('Manager');
        $manager_role->addPermission('oauth2.create');
        $manager_role->addPermission('oauth2.view');
        $manager_role->addPermission('oauth2.update');
        $manager_role->save();
        // Update Auditor Role
        $auditor_role = $this->sentinel->findRoleByName('Auditor');
        $auditor_role->addPermission('oauth2.view');
        $auditor_role->save();
    }

    public function down()
    {
        $this->schema->dropIfExists('oauth2_users');
        $this->schema->dropIfExists('oauth2_providers');
        
        // Delete Config and Group
        $delGroup = \Dappur\Model\ConfigGroups::where('name', 'Oauth2')->first();
        $delConfig = \Dappur\Model\Config::where('group_id', $delGroup->id)->delete();
        $delGroup->delete();
    }
}
