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
            $table->timestamp('last_login')->nullable();
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
                'blog_tags.*' => true,
                'blog_categories.*' => true,
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
                'blog_tags.*' => true,
                'blog_tags.delete' => false,
                'blog_categories.*' => true,
                'blog_categories.delete' => false,
                'dashboard.*' => true
            )
        ));

        // Create Contributor Role
        $this->sentinel->getRoleRepository()->createModel()->create(array(
            'name' => 'Contributor',
            'slug' => 'contributor',
            'permissions' => array(
                'dashboard.view' => true,
                'blog.*' => true,
                'blog_categories.view' => true,
                'blog_categories.create' => true,
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
            array(1, 'error-email', 'Email Errors To', 2, ''),
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

        $this->schema->create('blog_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        $this->schema->create('blog_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        $this->schema->create('blog_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('category_id')->unsigned()->nullable();
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->string('slug')->unique();
            $table->text('content')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('video_provider')->nullable();
            $table->string('video_id')->nullable();
            $table->timestamp('publish_at')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
            $table->foreign('category_id')->references('id')->on('blog_categories')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        $this->schema->create('blog_posts_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('post_id')->unsigned();
            $table->text('comment');
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('post_id')->references('id')->on('blog_posts')->onDelete('cascade');
        });

        $this->schema->create('blog_posts_replies', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('comment_id')->unsigned();
            $table->text('reply');
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('comment_id')->references('id')->on('blog_posts_comments')->onDelete('cascade');
        });

        $this->schema->create('blog_posts_tags', function (Blueprint $table) {
            $table->integer('post_id')->unsigned();
            $table->integer('tag_id')->unsigned();
            $table->primary(['post_id', 'tag_id']);
            $table->timestamps();
            $table->foreign('post_id')->references('id')->on('blog_posts')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('blog_tags')->onDelete('cascade');
        });

        $this->schema->create('users_profile', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->text('about');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        $ins_profile = new \Dappur\Model\UsersProfile;
        $ins_profile->user_id = 1;
        $ins_profile->about = 'Tail pork chop rump short ribs, hamburger prosciutto cow biltong pig tenderloin. Corned beef porchetta rump, turkey buffalo tail tenderloin hamburger alcatra t-bone cupim swine prosciutto pastrami. Pork belly picanha t-bone corned beef pork chop. Shank swine brisket pork. Shankle turkey shoulder andouille.';
        $ins_profile->save();

        $uncategorized = new Dappur\Model\BlogCategories;
        $uncategorized->name = "Uncategorized";
        $uncategorized->slug = "uncategorized";
        $uncategorized->save();

        $add_tag = new Dappur\Model\BlogTags;
        $add_tag->name = "Sample";
        $add_tag->slug = "sample";
        $add_tag->save();


        $blog_posts = array(
            array(
                'id' => 1, 
                'user_id' => 1, 
                'category_id' => 1, 
                'title' => 'Sample Post - No Featured Media', 
                'description' => 'Sample Post - No Featured Media', 
                'slug' => 'sample-post-no-featured-media', 
                'content' => '<div class=\"anyipsum-output\"><p>Bacon ipsum dolor amet shoulder sausage porchetta frankfurter venison meatloaf kielbasa ham hock. Tail kielbasa bresaola pig pork loin, salami turkey shank cupim fatback. Strip steak short loin picanha pig turducken andouille tail, bresaola sirloin. Meatloaf ham pork chop, prosciutto flank t-bone tongue bresaola drumstick ball tip alcatra burgdoggen. Andouille biltong short loin picanha salami tail. Pork loin shoulder pancetta, kevin beef spare ribs salami. Strip steak salami filet mignon, jowl tail ham biltong venison picanha jerky prosciutto boudin pork belly.</p><p>Frankfurter beef tri-tip, short ribs pancetta pork belly kielbasa meatball bacon. Flank pork belly short ribs, bresaola corned beef hamburger salami drumstick chicken cupim. Boudin pork chop meatloaf prosciutto biltong, short loin ham hock ball tip jowl shankle corned beef salami sausage. Flank rump bresaola, cupim pork loin strip steak jowl salami landjaeger short ribs corned beef cow.</p><p>Picanha meatball pancetta short loin leberkas capicola ham hock landjaeger tenderloin jowl. Turkey cow turducken, alcatra fatback shank rump tri-tip pork loin pastrami capicola tail ham sirloin tongue. Ribeye hamburger boudin pork chop tongue pancetta pig turducken sausage. Andouille filet mignon pig tri-tip, fatback cupim ball tip ribeye porchetta flank swine meatloaf kevin. Ground round corned beef boudin pork loin, venison chicken meatloaf ham pork belly alcatra ball tip ham hock picanha porchetta. Pig tri-tip beef ribs shank tongue pork chop cow sirloin porchetta rump kevin sausage short ribs. Ham hock pork chop kevin ground round shank sirloin ham swine filet mignon chicken.</p><p>Tail pork chop rump short ribs, hamburger prosciutto cow biltong pig tenderloin. Corned beef porchetta rump, turkey buffalo tail tenderloin hamburger alcatra t-bone cupim swine prosciutto pastrami. Pork belly picanha t-bone corned beef pork chop. Shank swine brisket pork. Shankle turkey shoulder andouille. Pork loin ribeye spare ribs bresaola, sirloin porchetta andouille cow. Alcatra jowl boudin cow meatball bresaola kevin frankfurter, pork chop beef ribs capicola ground round filet mignon.</p><p>Chicken ribeye bacon, short ribs tongue shoulder ground round picanha bresaola. Tenderloin kevin turducken meatball ground round turkey jerky cupim prosciutto biltong flank. Tail cow chicken pork belly. Ham hock rump corned beef meatloaf.</p></div><div class=\"anyipsum-form-header\">Does your lorem ipsum text long for something a little meatier? Give our generator a try&hellip; it&rsquo;s tasty!</div>', 
                'featured_image' => NULL, 
                'video_provider' => NULL, 
                'video_id' => NULL, 
                'publish_at' => '2017-12-06 21:14:00', 
                'status' => 1, 
                'created_at' => '2017-12-06 21:14:59', 
                'updated_at' => '2018-01-25 09:25:10'
            ),
            array(
                'id' => 2, 
                'user_id' => 1, 
                'category_id' => 1, 
                'title' => 'Sample Post - Featured Image', 
                'description' => 'Sample Post - Featured Image', 
                'slug' => 'sample-post-featured-image', 
                'content' => '<div class=\"anyipsum-output\"><p>Bacon ipsum dolor amet shoulder sausage porchetta frankfurter venison meatloaf kielbasa ham hock. Tail kielbasa bresaola pig pork loin, salami turkey shank cupim fatback. Strip steak short loin picanha pig turducken andouille tail, bresaola sirloin. Meatloaf ham pork chop, prosciutto flank t-bone tongue bresaola drumstick ball tip alcatra burgdoggen. Andouille biltong short loin picanha salami tail. Pork loin shoulder pancetta, kevin beef spare ribs salami. Strip steak salami filet mignon, jowl tail ham biltong venison picanha jerky prosciutto boudin pork belly.</p><p>Frankfurter beef tri-tip, short ribs pancetta pork belly kielbasa meatball bacon. Flank pork belly short ribs, bresaola corned beef hamburger salami drumstick chicken cupim. Boudin pork chop meatloaf prosciutto biltong, short loin ham hock ball tip jowl shankle corned beef salami sausage. Flank rump bresaola, cupim pork loin strip steak jowl salami landjaeger short ribs corned beef cow.</p><p>Picanha meatball pancetta short loin leberkas capicola ham hock landjaeger tenderloin jowl. Turkey cow turducken, alcatra fatback shank rump tri-tip pork loin pastrami capicola tail ham sirloin tongue. Ribeye hamburger boudin pork chop tongue pancetta pig turducken sausage. Andouille filet mignon pig tri-tip, fatback cupim ball tip ribeye porchetta flank swine meatloaf kevin. Ground round corned beef boudin pork loin, venison chicken meatloaf ham pork belly alcatra ball tip ham hock picanha porchetta. Pig tri-tip beef ribs shank tongue pork chop cow sirloin porchetta rump kevin sausage short ribs. Ham hock pork chop kevin ground round shank sirloin ham swine filet mignon chicken.</p><p>Tail pork chop rump short ribs, hamburger prosciutto cow biltong pig tenderloin. Corned beef porchetta rump, turkey buffalo tail tenderloin hamburger alcatra t-bone cupim swine prosciutto pastrami. Pork belly picanha t-bone corned beef pork chop. Shank swine brisket pork. Shankle turkey shoulder andouille. Pork loin ribeye spare ribs bresaola, sirloin porchetta andouille cow. Alcatra jowl boudin cow meatball bresaola kevin frankfurter, pork chop beef ribs capicola ground round filet mignon.</p><p>Chicken ribeye bacon, short ribs tongue shoulder ground round picanha bresaola. Tenderloin kevin turducken meatball ground round turkey jerky cupim prosciutto biltong flank. Tail cow chicken pork belly. Ham hock rump corned beef meatloaf.</p></div><div class=\"anyipsum-form-header\">Does your lorem ipsum text long for something a little meatier? Give our generator a try&hellip; it&rsquo;s tasty!</div>', 
                'featured_image' => 'https://baconmockup.com/1200/630', 
                'video_provider' => NULL, 
                'video_id' => NULL, 
                'publish_at' => '2017-12-06 21:18:00', 
                'status' => 1, 
                'created_at' => '2017-12-06 21:19:25', 
                'updated_at' => '2018-01-25 09:25:04'
            ),
            array(
                'id' => 3, 
                'user_id' => 1, 
                'category_id' => 1, 
                'title' => 'Sample Post - Featured Video', 
                'description' => 'Sample Post - Featured Video', 
                'slug' => 'sample-post-featured-video', 
                'content' => '<p>Bacon ipsum dolor amet pancetta short loin picanha drumstick, hamburger beef ribs doner shoulder frankfurter sirloin biltong kielbasa pastrami prosciutto. Boudin cupim burgdoggen, flank ground round shank turkey shankle tail kevin landjaeger. Filet mignon leberkas tongue pig biltong. Venison tri-tip buffalo kielbasa tail leberkas, flank brisket pastrami andouille.</p><p>Shankle rump ground round, pork burgdoggen bresaola spare ribs bacon pork chop cow sausage. Pastrami pork loin kielbasa frankfurter bacon fatback tri-tip swine turducken sirloin. Meatloaf tongue ball tip beef ribs doner fatback rump hamburger pig corned beef kevin meatball buffalo jerky spare ribs. Meatball biltong beef shoulder alcatra sausage swine pork loin tail chicken. Tongue ham hock flank swine beef ribs porchetta pancetta landjaeger strip steak pork loin fatback jerky meatball spare ribs.</p><p>Tail strip steak ham jowl kevin doner shoulder pig shank swine drumstick frankfurter. Bacon drumstick pork belly ribeye andouille sausage tri-tip cow fatback. Filet mignon pig jerky strip steak bresaola meatball brisket beef ribs tail burgdoggen sausage tenderloin t-bone. Andouille landjaeger tri-tip, pork chop chicken t-bone boudin. Kevin ball tip boudin t-bone pork. Short loin jerky pork loin chicken buffalo.</p><p>Burgdoggen capicola sausage pig, frankfurter prosciutto turkey andouille. Pig leberkas short loin tri-tip frankfurter. Landjaeger chuck t-bone, ham kevin strip steak short ribs. Shank flank tail turducken. Meatball jowl pastrami ham hock sirloin kielbasa hamburger. Pig shank bacon pork chop rump fatback.</p><p>Venison shoulder beef ribs, strip steak t-bone tenderloin ground round brisket shankle pork belly. Jowl sausage shankle chuck, rump short ribs short loin. Prosciutto kevin brisket, andouille short loin sausage cow hamburger pancetta shankle capicola strip steak. Ball tip ground round burgdoggen turducken bacon flank, landjaeger leberkas shank short ribs beef swine cupim jerky biltong. Doner t-bone sirloin picanha. Cow boudin filet mignon salami, leberkas kevin ham hock burgdoggen meatloaf beef drumstick sirloin fatback venison. Tenderloin ham boudin rump frankfurter tail pork chop ground round pig landjaeger pastrami tongue flank tri-tip beef.</p>', 
                'featured_image' => NULL, 
                'video_provider' => 'youtube', 
                'video_id' => '1bSDtlARvPI', 
                'publish_at' => '2017-12-06 22:50:00', 
                'status' => 1, 
                'created_at' => '2017-12-06 22:50:53', 
                'updated_at' => '2018-01-25 09:23:01'
            )
        );

        foreach ($blog_posts as $bkey => $bvalue) {
            $add_post = new \Dappur\Model\BlogPosts;
            $add_post->id = $bvalue['id'];
            $add_post->user_id = $bvalue['user_id'];
            $add_post->category_id = $bvalue['category_id'];
            $add_post->title = $bvalue['title'];
            $add_post->description = $bvalue['description'];
            $add_post->slug = $bvalue['slug'];
            $add_post->content = $bvalue['content'];
            $add_post->featured_image = $bvalue['featured_image'];
            $add_post->video_provider = $bvalue['video_provider'];
            $add_post->video_id = $bvalue['video_id'];
            $add_post->publish_at = $bvalue['publish_at'];
            $add_post->status = $bvalue['status'];
            $add_post->created_at = $bvalue['created_at'];
            $add_post->updated_at = $bvalue['updated_at'];
            $add_post->save();
        }

        $blog_posts_tags = array(
            array(
                'post_id' => 1,
                'tag_id' => 1
            ),
            array(
                'post_id' => 2,
                'tag_id' => 1
            ),
            array(
                'post_id' => 3,
                'tag_id' => 1
            )
        );

        foreach ($blog_posts_tags as $bptkey => $bptvalue) {
            $add_bpt = new \Dappur\Model\BlogPostsTags;
            $add_bpt->post_id = $bptvalue['post_id'];
            $add_bpt->tag_id = $bptvalue['tag_id'];
            $add_bpt->save();
        }

        $post_comments = array(
            array(
                'id' => 1,
                'user_id' => 1,
                'post_id' => 3,
                'comment' => 'This is a sample comment.',
                'status' => 1
            ),
            array(
                'id' => 2,
                'user_id' => 1,
                'post_id' => 3,
                'comment' => 'This is a sample pending comment.',
                'status' => 0
            )
        );

        foreach ($post_comments as $ckey => $cvalue) {
            $add_comment = new \Dappur\Model\BlogPostsComments;
            $add_comment->id = $cvalue['id'];
            $add_comment->user_id = $cvalue['user_id'];
            $add_comment->post_id = $cvalue['post_id'];
            $add_comment->comment = $cvalue['comment'];
            $add_comment->status = $cvalue['status'];
            $add_comment->save();
        }

        $add_reply = new \Dappur\Model\BlogPostsReplies;
        $add_reply->user_id = 1;
        $add_reply->comment_id = 1;
        $add_reply->reply = 'This is a sample reply.';
        $add_reply->status = 1;
        $add_reply->save();

        $add_reply = new \Dappur\Model\BlogPostsReplies;
        $add_reply->user_id = 1;
        $add_reply->comment_id = 1;
        $add_reply->reply = 'This is a sample pending reply.';
        $add_reply->status = 0;
        $add_reply->save();

        $config = new Dappur\Model\ConfigGroups;
        $config->name = "Blog";
        $config->description = "Blog Settings";
        $config->page_name = null;
        $config->save();

        //Initial Config Table Options
        $init_config = array(
            array('group_id' => $config->id, 'name' => 'blog-enabled', 'description' => 'Enable Blog', 'type_id' => 6, 'value' => 1),
            array('group_id' => $config->id, 'name' => 'blog-per-page', 'description' => 'Blog Posts Per Page', 'type_id' => 2, 'value' => 2),
        );

        foreach ($init_config as $ikey => $ivalue) {
            $ins_config = new \Dappur\Model\Config;
            $ins_config->group_id = $ivalue['group_id'];
            $ins_config->type_id = $ivalue['type_id'];
            $ins_config->name = $ivalue['name'];
            $ins_config->description = $ivalue['description'];
            $ins_config->value = $ivalue['value'];
            $ins_config->save();
        }
        
    }

    public function down()
    {
        $this->schema->dropIfExists('users_profile');
        $this->schema->dropIfExists('blog_posts_tags');
        $this->schema->dropIfExists('blog_posts_replies');
        $this->schema->dropIfExists('blog_posts_comments');
        $this->schema->dropIfExists('blog_posts');
        $this->schema->dropIfExists('blog_tags');
        $this->schema->dropIfExists('blog_categories');
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
