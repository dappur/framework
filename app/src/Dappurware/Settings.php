<?php

namespace Dappur\Dappurware;

use Dappur\Model\Config;
use Dappur\Model\ConfigGroups;

class Settings extends Dappurware
{
	public function getTimezones(){

        $zones_array = array();
        $timestamp = time();
        foreach(timezone_identifiers_list() as $key => $zone) {
            date_default_timezone_set($zone);
            $zones_array[$key]['zone'] = $zone;
            $zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
        }
        return $zones_array;
    }

    public function getThemeList(){
        $public_assets = array_filter(glob('../public/assets/*'), 'is_dir');
        $internal_assets = array_filter(glob('../app/views/*'), 'is_dir');

        $public_array = array();
        $internal_array = array();
        foreach ($public_assets as $key => $value) {
            $public_array[] = substr($value, strrpos($value, '/') + 1);
        }

        foreach ($internal_assets as $key => $value) {
            $internal_array[] = substr($value, strrpos($value, '/') + 1);
        }

        foreach ($internal_array as $key => $value) {
            if (!in_array($value, $public_array)) {
                unset($internal_array[$key]);
            }
        }

        return $internal_array;
    }

    public function getSettingsFile(){
    	$settings = file_get_contents( __DIR__ . '/../../bootstrap/settings.json');

        $settings = json_decode($settings, TRUE);

    	return $settings;
    }

    public function getBootswatch(){

        $bootswatch = json_decode(file_get_contents($this->container->settings['view']['bootswatch']['api_url']));

        $output = array();
        foreach ($bootswatch->themes as $key => $value) {
            $output[] = $value->name;
        }

        return $output;
    }

    public function getSettingsByGroup(){

        $groups = ConfigGroups::get();

        $sorted_groups = array();

        foreach ($groups as $gkey => $gvalue) {
            $sorted_groups[$gvalue['name']] = Config::where('group_id', '=', $gvalue['id'])
                ->select('config.*', 'config_types.name as type')
                ->leftJoin("config_types", "config_types.id", "=", "config.type_id")
                ->get();
        }

        return $sorted_groups;
    }

}