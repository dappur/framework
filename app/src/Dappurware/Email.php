<?php

namespace Dappur\Dappurware;

use Dappur\Model\Users;
use Dappur\Model\Config;
use Dappur\Model\ConfigGroups;


class Email extends Dappurware
{
    
    public function getPlaceholders(){

        $output = array();

        $users = Users::first()->toArray();

        foreach ($users as $key => $value) {
            $output['User Info'][] = array("name" => $key, "type" => "user");
        }

        $config = Config::get()->toArray();
        $config_groups = ConfigGroups::get()->toArray();

        foreach ($config as $cvalue) {
            $output[$cvalue['group_id']][] = array("name" => $cvalue['name'], "type" => "config", "value" => $cvalue['value']);
        }

        foreach ($config_groups as $cgvalue) {

            if ($output[$cgvalue['id']]) {
                $output[$cgvalue['name']] = $output[$cgvalue['id']];
                unset($output[$cgvalue['id']]);
            }
            
        }
        
        return $output;

    }

}