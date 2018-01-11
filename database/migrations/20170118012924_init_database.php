<?php

use \Dappur\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class InitDatabase extends Migration
{
    /**
    *
    * Write your reversible migrations using this method.
    *
    * More information on writing eloquent migrations is available here:
    * https://laravel.com/docs/5.4/migrations
    *
    * Remember to use both the up() and down() functions in order to be able to roll back. 
    */
   
    public function up()
    {   
        // Create Users Table
        $this->schema->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();
            $table->text('permissions');
            $table->boolean('status')->default(1);
            $table->timestamp('last_login');
            $table->timestamps();
        });

        // Create Activations Table
        $this->schema->create('activations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->string('code');
            $table->boolean('completed')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        // Create Persistences Table
        $this->schema->create('persistences', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->string('code')->unique();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        // Create Reminders Table
        $this->schema->create('reminders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->string('code');
            $table->boolean('completed')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        // Create Roles Table
        $this->schema->create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('permissions');
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        // Create Roles_Users Table
        $this->schema->create('role_users', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->primary(array('user_id', 'role_id'));
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });

        // Create Throttle Table
        $this->schema->create('throttle', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->string('type');
            $table->string('ip')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        // Create Config Groups Table
        $this->schema->create('config_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->string('page_name')->unique()->nullable();
            $table->timestamps();
        });

        // Create Config Types Table
        $this->schema->create('config_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Create Config Table
        $this->schema->create('config', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_id')->unsigned()->nullable();
            $table->integer('type_id')->unsigned()->nullable();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->text('value')->nullable();
            $table->timestamps();
            $table->foreign('group_id')->references('id')->on('config_groups')->onDelete('set null');
            $table->foreign('type_id')->references('id')->on('config_types')->onDelete('set null');
        });

        // Create Admin Role
        $this->sentinel->getRoleRepository()->createModel()->create(array(
            'name' => 'Admin',
            'slug' => 'admin',
            'permissions' => array(
                'user.*' => true,
                'email.*' => true,
                'settings.*' => true,
                'role.*' => true,
                'permission.*' => true,
                'media.*' => true,
                'blog.*' => true,
                'dashboard.*' => true
            )
        ));

        // Create Developer Role
        $this->sentinel->getRoleRepository()->createModel()->create(array(
            'name' => 'Developer',
            'slug' => 'developer',
            'permissions' => array(
                'developer.*' => true
                )
        ));

        // Create Manager Role
        $this->sentinel->getRoleRepository()->createModel()->create(array(
            'name' => 'Manager',
            'slug' => 'manager',
            'permissions' => array(
                'user.*' => true,
                'user.delete' => false,
                'role.*' => true,
                'role.delete' => false,
                'permission.*' => true,
                'permission.delete' => false,
                'media.*' => true,
                'media.delete' => false,
                'blog.*' => true,
                'blog.delete' => false,
                'dashboard.*' => true
            )
        ));

        //Create User Role
        $this->sentinel->getRoleRepository()->createModel()->create(array(
            'name' => 'User',
            'slug' => 'user',
            'permissions' => array(
                'user.account' => true
                )
        ));

        // Create Auditor Role
        $this->sentinel->getRoleRepository()->createModel()->create(array(
            'name' => 'Auditor',
            'slug' => 'auditor',
            'permissions' => array(
                'user.view' => true,
                'settings.view' => true,
                'role.view' => true,
                'permission.view' => true,
                'blog.view' => true,
                'dashboard.view' => true
            )
        ));

        //Create Admin User
        $admin_role = $this->sentinel->findRoleByName('Admin');
        $developer_role = $this->sentinel->findRoleByName('Developer');
        $admin = $this->sentinel->registerAndActivate([
            'first_name' => "Admin",
            'last_name' => "User",
            'username' => 'admin',
            'email' => "admin@example.com",
            'password' => "admin123",
            'permissions' => array()
        ]);
        $admin_role->users()->attach($admin);
        $developer_role->users()->attach($admin);

        //Initial Config Types
        $init_config_types = array(
            array(1, "timezone"),
            array(2, "string"),
            array(3, "theme"),
            array(4, "bootswatch"),
            array(5, "image"),
            array(6, "boolean")
        );

        // Seed Config Table
        foreach ($init_config_types as $key => $value) {
            $config = new Dappur\Model\ConfigTypes;
            $config->id = $value[0];
            $config->name = $value[1];
            $config->save();
        }

        //Initial Config Groups
        $init_config_groups = array(
            array(1, "Site Settings", null, null),
            array(2, "Dashboard Settings", null, null),
            array(3, "Contact", "Contact Page Config", 'contact')
        );

        // Seed Config Table
        foreach ($init_config_groups as $key => $value) {
            $config = new Dappur\Model\ConfigGroups;
            $config->id = $value[0];
            $config->name = $value[1];
            $config->description = $value[2];
            $config->page_name = $value[3];
            $config->save();
        }

        //Initial Config Table Options
        $init_config = array(
            array(1, 'timezone', 'Site Timezone', 1, 'America/Los_Angeles'),
            array(1, 'site-name', 'Site Name', 2, 'Dappur'),
            array(1, 'domain', 'Site Domain', 2, 'example.com'),
            array(1, 'support-email', 'Support Email', 2, 'support@example.com'),
            array(1, 'from-email', 'From Email', 2, 'noreply@example.com'),
            array(1, 'theme', 'Site Theme', 3, 'dappur'),
            array(1, 'bootswatch', 'Site Bootswatch', 4, 'cerulean'),
            array(1, 'logo', 'Site Logo', 5, 'https://res.cloudinary.com/dappur/image/upload/c_scale,w_600/v1479072913/site-images/logo-horizontal.png'),
            array(1, 'header-logo', 'Header Logo', 5, 'https://res.cloudinary.com/dappur/image/upload/c_scale,h_75/v1479072913/site-images/logo-horizontal.png'),
            array(2, 'dashboard-theme', 'Dashboard Theme', 3, 'dashboard'),
            array(2, 'dashboard-bootswatch', 'Dashboard Bootswatch', 4, 'slate'),
            array(2, 'dashboard-logo', 'Dashboard Logo', 5, 'https://res.cloudinary.com/dappur/image/upload/c_scale,h_75/v1479072913/site-images/logo-horizontal.png'),
            array(1, 'ga', 'Google Analytics UA', 2, ''),
            array(1, 'activation', 'Activation Required', 6, 1),
            array(1, 'maintenance-mode', 'Maintenance Mode', 6, 0),
            array(1, 'privacy-service', 'Privacy Service Statement', 2, 'SERVICE'),
            array(3, 'contact-email', 'Contact Email', 2, 'contact@example.com'),
            array(3, 'contact-phone', 'Contact Phone', 2, '(000) 000-0000'),
            array(3, 'contact-street', 'Contact Street', 2, '123 Harbor Blvd.'),
            array(3, 'contact-city', 'Contact City', 2, 'Oxnard'),
            array(3, 'contact-state', 'Contact State', 2, 'CA'),
            array(3, 'contact-zip', 'Contact Zip', 2, '93035'),
            array(3, 'contact-country', 'Contact Country', 2, 'USA'),
            array(3, 'contact-map-url', 'Map Iframe Url', 2, 'https://goo.gl/oDcRix'),
            array(3, 'contact-map-show', 'Show Map', 6, 1),
            array(3, 'contact-send-email', 'Send Confirmation Email', 6, 1)
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

        // Email Templates
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

        // Sent Emails Table
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

        // Email Drafts
        $this->schema->create('emails_drafts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('secure_id');
            $table->text('send_to')->nullable();
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
                "description" => 'Password reset email to user',
                "subject" => 'Password Reset Request from {{  settings_site_name  }}',
                "html" => '<h1>{{ settings_site_name }}</h1>' . "\r\n\r\n" . '<p>Hello&nbsp;{{ user_first_name }},</p>' . "\r\n\r\n" . '<p>You are receiving this email because you recently requested a password reset. &nbsp;</p>' . "\r\n\r\n" . '<h3><a href="{{ reset_url }}">Reset Password Now</a></h3>'."\r\n\r\n".'<p>If you did not request this reset, then please disregard this email.</p>' . "\r\n\r\n" . '<p>Thank You,</p>' . "\r\n\r\n" . '<p>{{ settings_site_name }}</p>' . "\r\n\r\n",
                "plain_text" => 'Hello {{ user_first_name }},' . "\r\n\r\n" . 'You are receiving this email because you recently requested a password reset.  To continue resetting your password, please click the following link:' . "\r\n\r\n" . '{{ reset_url }}' . "\r\n\r\n" . 'If you did not request this reset, then please disregard this email.' . "\r\n\r\n" . 'Thank You,' . "\r\n\r\n" . '{{ settings_site_name }}',
                "placeholders" => '["reset_url"]'
            ),
            array(
                "id" => 2,
                "name" => 'Registration Complete',
                "slug" => 'registration',
                "description" => 'Registration Complete Email',
                "subject" => 'Welcome to {{  settings_site_name  }}, {{  user_first_name  }}!',
                "html" => '<h1>{{ settings_site_name }}</h1>' . "\r\n\r\n" . '<p>Hello&nbsp;{{ user_first_name }},</p>' . "\r\n\r\n" . '<p>Welcome to &nbsp;{{ settings_site_name }}. &nbsp;Here are your login details:</p>' . "\r\n\r\n" . '<p>Username:&nbsp;{{ user_username }}<br />' . "\r\n" . 'Password: Chosen at Registration</p>' . "\r\n\r\n" . '<h3><a href="https://{{ settings_domain }}">Visit&nbsp;{{ settings_site_name }}</a></h3>' . "\r\n\r\n" . '<p>Thank You,</p>'."\r\n\r\n".'<p>{{ settings_site_name }}</p>',
                "plain_text" => 'Hello {{ user_first_name }},' . "\r\n\r\n" . 'Welcome to {{  settings_site_name  }}.  Here are your login details:' . "\r\n\r\n" . 'Username: {{  user_username  }}' . "\r\n" . 'Password: Chosen at Registration' . "\r\n\r\n" . 'Visit https://{{  settings_domain  }}' . "\r\n\r\n" . 'Thank You,' . "\r\n\r\n" . '{{ settings_site_name }}',
                "placeholders" => null
            ),
            array(
                "id" => 3,
                "name" => 'Activation Email',
                "slug" => 'activation',
                "description" => 'Account Activation Email',
                "subject" => 'Activate Your {{  settings_site_name  }} Account',
                "html" => '<h1>{{ settings_site_name }}</h1>'."\r\n\r\n".'<p>Hello&nbsp;{{ user_first_name }},</p>'."\r\n\r\n".'<p>Thank you for creating your account. &nbsp;In order to ensure the best possible experience, we require that you verify your email address before you can begin using your account. &nbsp;To do so, simply click the following link and you will be immediately logged in to your account.</p>'."\r\n\r\n".'<h3><a href="{{ confirm_url }}">Confirm Email Now</a></h3>'."\r\n\r\n".'<p>Thank You,</p>'."\r\n\r\n".'<p>{{ settings_site_name }} Team</p>',
                "plain_text" => '{{ settings_site_name }}' . "\r\n\r\n" . 'Hello {{  user_first_name  }},' . "\r\n\r\n" . 'Thank you for creating your account.  In order to ensure the best possible experience, we require that you verify your email address before you can begin using your account.  To do so, simply click the following link and you will be immediately logged in to your account.' . "\r\n\r\n" . '{{ confirm_url }}' . "\r\n\r\n" . 'Thank You,' . "\r\n\r\n" . '{{ settings_site_name }} Team',
                "placeholders" => '["confirm_url"]'
            ),
            array(
                "id" => 4,
                "name" => 'User Contact Confirmation',
                "slug" => 'contact-confirmation',
                "description" => 'Contact confirmation sent to the user',
                "subject" => 'Contact Confirmation from {{  settings_site_name  }}',
                "html" => '<h1>{{ settings_site_name }}</h1>'."\r\n\r\n".'<p>Hello {{ name }},</p>'."\r\n\r\n".'<p>We have received your contact request and if it requires a reply, we will be in touch with you soon. &nbsp;here is the information that you submitted:</p>'."\r\n\r\n".'<p><strong>Phone:</strong>&nbsp;{{ phone }}<br />'."\r\n".'<strong>Comment:</strong>&nbsp;{{ comment }}</p>'."\r\n\r\n".'<h3><a href="https://{{ settings_domain }}">Visit {{ settings_site_name }}</a></h3>'."\r\n\r\n".'<p>Thank You,</p>'."\r\n\r\n".'<p>{{ settings_site_name }} Team</p>',
                "plain_text" => '{{ settings_site_name }}'."\r\n\r\n".'Hello {{ name }},'."\r\n\r\n".'We have received your contact request and if it requires a reply, we will be in touch with you soon. Here is the information that you submitted:'."\r\n\r\n".'Name: {{ name }}'."\r\n".'Phone: {{ phone }}'."\r\n".'Comment: {{ comment }}'."\r\n\r\n".'Visit https://{{  settings_domain  }}'."\r\n\r\n".'Thank You,'."\r\n\r\n".'{{ settings_site_name }} Team',
                "placeholders" => '["name","phone","comment"]'
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

        // Add Contact Requests Table
        $this->schema->create('contact_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });
        
    }

    public function down()
    {
        $this->schema->dropIfExists('contact_requests');
        $this->schema->dropIfExists('emails_drafts');
        $this->schema->dropIfExists('emails');
        $this->schema->dropIfExists('emails_templates');
        $this->schema->dropIfExists('activations');
        $this->schema->dropIfExists('persistences');
        $this->schema->dropIfExists('reminders');
        $this->schema->dropIfExists('role_users');
        $this->schema->dropIfExists('throttle');
        $this->schema->dropIfExists('roles');
        $this->schema->dropIfExists('users');
        $this->schema->dropIfExists('config');
    }
}
