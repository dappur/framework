<?php

namespace App\Dappurware;

Class SiteConfig extends Dappurware 
{

	public function getConfig() 
	{

	    $config = $this->db->table('config')->get();
	    $cfg = array();
	    foreach($config as $cfgkey => $cfgvalue){
	        $cfg[$cfgvalue->name] = $cfgvalue->value;
	    }

	    $cfg['copyright-year'] = date("Y");

	    //Set Default Timezone
	    date_default_timezone_set($cfg['timezone']);

	    return $cfg;
	}

	public function editConfig() {
		// TODO
	}

	public function addConfig() {
		// TODO
	}

	public function delConfig() {
		// TODO
	}

}