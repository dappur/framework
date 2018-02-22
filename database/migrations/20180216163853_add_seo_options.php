<?php

use \Dappur\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddSeoOptions extends Migration
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
        $this->schema->create('seo', function (Blueprint $table) {
            $table->increments('id');
            $table->string('page')->nullable();
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->string('image')->nullable();
            $table->string('video')->nullable();
            $table->boolean('default')->default(0);
            $table->timestamps();
        });

        $ins_seo = new \Dappur\Model\Seo;
        $ins_seo->page = "home";
        $ins_seo->title = "Dappur PHP Framework";
        $ins_seo->description = "A stylish PHP application framework crafted using Slim, Twig, Eloquent and Sentinel designed to get you from clone to production in a matter of minutes.";
        $ins_seo->image = "https://res.cloudinary.com/dappur/image/upload/v1519256235/seo/bbn48kaoq35hm7zbuuzk.jpg";
        $ins_seo->default = 1;
        $ins_seo->save();

        // Add SEO Config Group
        $config = new Dappur\Model\ConfigGroups;
        $config->name = "SEO Settings";
        $config->description = "SEO Settings";
        $config->save();

        $init_config = array(
            array($config->id, 'fb-admins', 'Facebook Admins', 2, ''),
            array($config->id, 'fb-app-id', 'Facebook App ID', 2, ''),
            array($config->id, 'fb-page-id', 'Facebook Page ID', 2, ''),
            array($config->id, 'tw-author', 'Twitter Author', 2, ''),
            array($config->id, 'tw-publisher', 'Twitter Publisher', 2, '')
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
        $admin_role->addPermission('seo.*');
        $admin_role->save();
        // Update Manager Role
        $manager_role = $this->sentinel->findRoleByName('Manager');
        $manager_role->addPermission('seo.create');
        $manager_role->addPermission('seo.view');
        $manager_role->addPermission('seo.update');
        $manager_role->save();
        // Update Auditor Role
        $auditor_role = $this->sentinel->findRoleByName('Auditor');
        $auditor_role->addPermission('seo.view');
        $auditor_role->save();

    }

    public function down()
    {
        // Remove Config Group
        $config = new Dappur\Model\ConfigGroups;
        $config = $config->where("name", "SEO Settings")->first();
        if ($config) {
            $config->delete();
        }

        $this->schema->dropIfExists('seo');
    }
}
