<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 08/10/2018
 * Time: 0:13
 */

namespace maesierra\Japo;


require_once 'vendor/autoload.php';

use Composer\Script\Event;
use maesierra\Japo\AppContext\JapoAppConfig;

class ComposerInstallScript {

    public static function postPackageInstall(Event $event) {
        $japoAppConfig = JapoAppConfig::get();
        $homePath = $japoAppConfig->homePath;
        if ($homePath) {
            putenv( "PUBLIC_URL=$homePath");
        }
        passthru("npm run-script build --prefix react/japo/");
        passthru("npm run-script post-build --prefix react/japo/");
        passthru("cp react/japo/build/* . -r");
        passthru("cp react/japo/build/.htaccess .htaccess");
        putenv( "PUBLIC_URL");
    }

}