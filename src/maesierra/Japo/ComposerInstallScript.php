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
use maesierra\Japo\AppContext\JapoAppContext;

class ComposerInstallScript {

    public static function postPackageInstall(Event $event) {
        $japoAppConfig = JapoAppConfig::get();
        $homePath = $japoAppConfig->homePath;
        if ($homePath) {
            putenv( "PUBLIC_URL=$homePath");
        }
        passthru("npm run-script build --prefix react/japo/");
        passthru("npm run-script post-build --prefix react/japo/");
        if (!file_exists('webroot')) {
            mkdir('webroot');
            echo "Created webroot folder\n";
        }
        foreach (new \DirectoryIterator('react/japo/build') as $fileinfo) {
            if ($fileinfo->isDot()) {
                continue;
            }
            $filename = $fileinfo->getFilename();
            if (!file_exists("webroot/$filename")) {
                passthru("ln -s \"{$fileinfo->getRealPath()}\" \"webroot/$filename\"");
                echo "Created link to webroot/$filename from {$fileinfo->getRealPath()}.\n";
            }
        }
        passthru("ln -s \"".realpath("api")."\" webroot/api");
        echo "Created link to ".realpath("api").".\n";
        file_put_contents("webroot/api/.htaccess",
            "RewriteEngine On\n".
                "RewriteCond %{REQUEST_FILENAME} !-f\n".
                "RewriteCond %{REQUEST_FILENAME} !-d\n".
                "RewriteRule ^ {$japoAppConfig->serverPath}/index.php [QSA,L]\n"
        );
        echo "Generated .htaccess file for webroot/api.\n";
        putenv( "PUBLIC_URL");
        JapoAppContext::context()->dbMigration->run();
    }

}