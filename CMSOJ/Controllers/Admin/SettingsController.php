<?php
namespace CMSOJ\Controllers\Admin;

use CMSOJ\Template;
use CMSOJ\Models\Setting;


class SettingsController
{
    public function index()
    {
        $settings = (new Setting())->allSettings();
        
        return Template::view("CMSOJ/Views/admin/settings/index.html", [
            'settings' => $settings,
            'title' => 'Settings'
        ]);
    }

    public function save()
    {
        foreach ($_POST as $key => $value) {
            (new Setting())->setValue($key, $value);
        }

        $_SESSION['flash_success'] = "Settings saved!";
        header("Location: /admin/settings");
        exit;
    }
}
