<?php

namespace Dappur\Dappurware;

use Dappur\Model\Config;
use Dappur\Model\ConfigGroups;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class Settings
{
    public function getBootswatch()
    {
        $settings = Settings::getSettingsFile();


        $bootswatch = json_decode(file_get_contents($settings['view']['bootswatch']['api_url']));

        $output = array();
        foreach ($bootswatch->themes as $value) {
            $output[] = $value->name;
        }

        return $output;
    }

    public function getSettingsFile()
    {
        $settings = file_get_contents(__DIR__ . '/../../../settings.json');

        $settings = json_decode($settings, true);

        return $settings;
    }

    public function getSettingsByGroup()
    {
        $groups = ConfigGroups::whereNull('page_name')->with('config')->get();

        return $groups;
    }

    public function getTimezones()
    {
        $zonesArray = array();
        $timestamp = time();
        foreach (timezone_identifiers_list() as $key => $zone) {
            $zonesArray[$key]['zone'] = $zone;
            $zonesArray[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
        }
        return $zonesArray;
    }

    public function getThemeList()
    {
        $internalAssets = array_filter(glob(__DIR__ . '/../../views/*'), 'is_dir');

        $internalArray = array();
        foreach ($internalAssets as $value) {
            $internalArray[] = substr($value, strrpos($value, '/') + 1);
        }

        return $internalArray;
    }
}
