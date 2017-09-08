<?php

use \Dappur\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddEmailSystem extends Migration
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
        


        $this->schema->create('emails_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('slug')->unique()->nullable();
            $table->string('description')->nullable();
            $table->string('subject')->nullable();
            $table->text('html')->nullable();
            $table->text('plain_text')->nullable();
            $table->text('placeholders')->nullable();
            $table->timestamps();
        });

        $this->schema->create('emails', function (Blueprint $table) {
            $table->increments('id');
            $table->string('secure_id');
            $table->integer('template_id')->unsigned()->nullable();
            $table->text('send_to')->nullable();
            $table->string('subject')->nullable();
            $table->text('html')->nullable();
            $table->text('plain_text')->nullable();
            $table->timestamps();
            $table->foreign('template_id')->references('id')->on('emails_templates')->onDelete('set null');
        });

        $this->schema->create('emails_drafts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('subject')->nullable();
            $table->text('html')->nullable();
            $table->text('plain_text')->nullable();
            $table->timestamps();
        });

        // Initial Email Templates
        $templates = array(
            array(
                "id" => 1,
                "name" => 'Password Reset',
                "slug" => 'password-reset',
                "description" => 'Password Reset Email',
                "subject" => 'Password Reset Request from {{  settings_site_name  }}',
                "html" => '<h1>{{ settings_site_name }}</h1>' . "\r\n\r\n" . '<p>Hello&nbsp;{{ user_first_name }},</p>' . "\r\n\r\n" . '<p>You are receiving this email because you recently requested a password reset. &nbsp;</p>' . "\r\n\r\n" . '<h3><a href="{{ reset_url }}">Reset Password Now</a></h3>' . "\r\n\r\n" . '<p>If you did not request this reset, then please disregard this email.</p>' . "\r\n\r\n" . '<p>Thank You,</p>' . "\r\n\r\n" . '<p>{{ settings_site_name }}</p>\r\n', 'Hello {{ user_first_name }},' . "\r\n\r\n" . 'You are receiving this email because you recently requested a password reset.  To continue resetting your password, please click the following link:' . "\r\n\r\n" . '{{ reset_url }}' . "\r\n\r\n" . 'If you did not request this reset, then please disregard this email.' . "\r\n\r\n" . 'Thank You,' . "\r\n\r\n" . '{{ settings_site_name }}',
                "plain_text" => 'Hello {{ user_first_name }},' . "\r\n\r\n" . 'You are receiving this email because you recently requested a password reset.  To continue resetting your password, please click the following link:' . "\r\n\r\n" . '{{ reset_url }}' . "\r\n\r\n" . 'If you did not request this reset, then please disregard this email.' . "\r\n\r\n" . 'Thank You,' . "\r\n\r\n" . '{{ settings_site_name }}',
                "placeholders" => '["reset_url"]'
            ),
            array(
                "id" => 2,
                "name" => 'Registration Complete',
                "slug" => 'registration',
                "description" => 'Registration Complete Email',
                "subject" => 'Welcome to {{  settings_site_name  }}, {{  user_first_name  }}!',
                "html" => '<h1>{{ settings_site_name }}</h1>' . "\r\n\r\n" . '<p>Hello&nbsp;{{ user_first_name }},</p>' . "\r\n\r\n" . '<p>Welcome to &nbsp;{{ settings_site_name }}. &nbsp;Here are your login details:</p>' . "\r\n\r\n" . '<p>Username:&nbsp;{{ user_username }}<br />' . "\r\n" . 'Password: Chosen at Registration</p>' . "\r\n\r\n" . '<h3><a href="https://{{ settings_domain }}">Visit&nbsp;{{ settings_site_name }}</a></h3>' . "\r\n\r\n" . '<p>Thank You,</p>' . "\r\n\r\n" . '<p>{{ settings_site_name }}</p>',
                "plain_text" => 'Hello {{ user_first_name }},' . "\r\n\r\n" . 'Welcome to {{  settings_site_name  }}.  Here are your login details:' . "\r\n\r\n" . 'Username: {{  user_username  }}' . "\r\n" . 'Password: Chosen at Registration' . "\r\n\r\n" . 'Visit https://{{  settings_domain  }}' . "\r\n\r\n" . 'Thank You,' . "\r\n\r\n" . '{{ settings_site_name }}',
                "placeholders" => null
            ),
            array(
                "id" => 3,
                "name" => 'Activation Email',
                "slug" => 'activation',
                "description" => 'Account Activation Email',
                "subject" => 'Activate Your {{  settings_site_name  }} Account',
                "html" => '<h1>{{ settings_site_name }}</h1>' . "\r\n\r\n" . '<p>Hello&nbsp;{{ user_first_name }},</p>' . "\r\n\r\n" . '<p>Thank you for creating your account. &nbsp;In order to ensure the best possible experience, we require that you verify your email address before you can begin using your account. &nbsp;To do so, simply click the following link and you will be immediately logged in to your account.</p>' . "\r\n\r\n" . '<h3><a href="{{ confirm_url }}">Confirm Email Now</a></h3>' . "\r\n\r\n" . '<p>Thank You,</p>' . "\r\n\r\n" . '<p>{{ settings_site_name }} Team</p>',
                "plain_text" => '{{ settings_site_name }}' . "\r\n\r\n" . 'Hello {{  user_first_name  }},' . "\r\n\r\n" . 'Thank you for creating your account.  In order to ensure the best possible experience, we require that you verify your email address before you can begin using your account.  To do so, simply click the following link and you will be immediately logged in to your account.' . "\r\n\r\n" . '{{ confirm_url }}' . "\r\n\r\n" . 'Thank You,' . "\r\n\r\n" . '{{ settings_site_name }} Team',
                "placeholders" => '["confirm_url"]'
            )
        );

        // Seed Initial Templates
        foreach ($templates as $tevalue) {
            $add_template = new \Dappur\Model\EmailsTemplates;
            $add_template->id = $tevalue['id'];
            $add_template->name = $tevalue['name'];
            $add_template->slug = $tevalue['slug'];
            $add_template->description = $tevalue['description'];
            $add_template->subject = $tevalue['subject'];
            $add_template->html = $tevalue['html'];
            $add_template->plain_text = $tevalue['plain_text'];
            $add_template->placeholders = $tevalue['placeholders'];
            $add_template->save();
        }
    }

    public function down()
    {
        $this->schema->dropIfExists('emails');
        $this->schema->dropIfExists('emails_templates');

    }
}
