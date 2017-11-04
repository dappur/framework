<?php

namespace Dappur\Dappurware;
use Dappur\Model\ConfigGroups;

Class SiteConfig extends Dappurware 
{

	public function getGlobalConfig() 
	{
		$cfg = array();

		$config = ConfigGroups::whereNull('page_name')->with('config')->get();

		foreach($config as $group_key => $group_value){
	        foreach($group_value->config as $cfgkey => $cfgvalue){
	        	$cfg[$cfgvalue->name] = $cfgvalue->value;
		    }
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