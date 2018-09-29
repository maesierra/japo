<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 29/09/2018
 * Time: 10:37
 */

namespace maesierra\Japo\DB;


use Symfony\Component\Filesystem\Filesystem;

if (file_exists('../../../../vendor/autoload.php')) include '../../../../vendor/autoload.php';
if (file_exists('vendor/autoload.php')) include ('vendor/autoload.php');


class DBMigrationTest extends \PHPUnit_Framework_TestCase {

    private $dumpFile;

    private $outFolder;

    /** @var  DBMigration */
    private $dbMigration;

    public function setUp() {
        $this->dbMigration = new DBMigration([], sys_get_temp_dir());
    }

    public function testCreateMigrationFromDumpfile() {
        $this->dumpFile = tempnam(sys_get_temp_dir(), "dmp");
        $this->outFolder = sys_get_temp_dir().DIRECTORY_SEPARATOR."migration_out";
        if (!file_exists($this->outFolder)) {
            mkdir($this->outFolder);
        }
        file_put_contents($this->dumpFile, "<?php
/**
 * Export to PHP Array plugin for PHPMyAdmin
 * @version 4.7.7
 */

/**
 * Database `maedb`
 */

/* `maedb`.`JDICT_ENTRY_GLOSS` */
\$JDICT_ENTRY_GLOSS = array(
  array('ID' => '1000000','GLOSS_ID' => '1','GLOSS' => 'repetition mark in katakana'),
  array('ID' => '1000010','GLOSS_ID' => '1','GLOSS' => 'voiced repetition mark in katakana'),
  array('ID' => '1000020','GLOSS_ID' => '1','GLOSS' => 'repetition mark in hiragana'),
  array('ID' => '1000030','GLOSS_ID' => '1','GLOSS' => 'voiced repetition mark in hiragana'),
  array('ID' => '1000040','GLOSS_ID' => '1','GLOSS' => 'ditto mark'),
  array('ID' => '1000050','GLOSS_ID' => '1','GLOSS' => '\"as above\" mark'),
  array('ID' => '1000060','GLOSS_ID' => '1','GLOSS' => 'repetition of kanji (sometimes voiced)')
);
");
        $this->dbMigration->createMigrationFromDumpFile($this->dumpFile, 'jdict_entry_gloss', $this->outFolder, 3);
        $dir = new \DirectoryIterator($this->outFolder);
        $nMigrationFiles = 0;
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isDot()) {
                continue;
            }
            $filename = $fileinfo->getFilename();
            if (!preg_match('/\d{14}_jdict_entry_gloss_data(\d+)\.php/', $filename, $matches)) {
                continue;
            }
            $nFile = $matches[1];
            if (!preg_match('/<\?php.*class (.*) extends AbstractMigration.*table\(\'(.*)\'\)->insert\(\[(.*)\]\)->save.*/s', file_get_contents($fileinfo->getRealPath()), $matches)) {
                continue;
            }
            $this->assertEquals("JdictEntryGlossData$nFile", $matches[1]);
            $this->assertEquals('jdict_entry_gloss', $matches[2]);
            $nMigrationFiles++;
        }
        $this->assertEquals(3, $nMigrationFiles);
    }

    public function tearDown() {
        $filesystem = new Filesystem();
        if (file_exists($this->dumpFile)) {
            try {
                $filesystem->remove($this->dumpFile);
            } catch (\Exception $e) {

            }
        }
        if (file_exists($this->outFolder)) {
            $filesystem->remove($this->outFolder);
        }
    }
}
