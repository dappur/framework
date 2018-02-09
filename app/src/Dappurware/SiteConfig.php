<?php

namespace Dappur\Dappurware;

use Dappur\Model\ConfigGroups;

Class SiteConfig
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

	    return $cfg;
	}

}