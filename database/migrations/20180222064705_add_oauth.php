<?php

use \Dappur\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddOauth extends Migration
{
    /**
    *
    * Write your reversible migrations using this method.
    * 
    * Dappur Framework uses Laravel Eloquent ORM as it's database connector.
    *
    * More information on writing eloquent migrations is available here:
    * https://laravel.com/docs/5.4/migrations
    *
    * Remember to use both the up() and down() functions in order to be able to roll back. 
    * 
    * 	Create Table Sample
    * 	$this->schema->create('sample', function (Blueprint $table) {
	*     	$table->increments('id');
	*    	$table->string('email')->unique();
	*    	$table->string('last_name')->nullable();
	*    	$table->string('first_name')->nullable();
	*    	$table->timestamps();
    *  	});
    * 
    * 	Drop Table Sample
    * 	$this->schema->dropIfExists('sample');
    */
    
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
            $table->string('access_token');
            $table->string('token_secret')->nullable();
            $table->string('refresh_token')->nullable();
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
                "scopes" => null,
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
                "scopes" => null,
                "authorize_url" => "https://www.linkedin.com/oauth/v2/authorization",
                "token_url" => "https://www.linkedin.com/oauth/v2/accessToken",
                "resource_url" => "https://api.linkedin.com/v1/people/~:(id,email-address)?format=json",
                "button" => "linkedin",
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

    }

    public function down()
    {
        $this->schema->dropIfExists('oauth2_providers');
        $this->schema->dropIfExists('oauth2_users');
    }
}
