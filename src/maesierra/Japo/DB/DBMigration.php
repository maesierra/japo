<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 09/09/2018
 * Time: 16:36
 */

namespace maesierra\Japo\DB;


use Phinx\Console\PhinxApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

class DBMigration {
    private $oneSecond;

    /**
     * @var \Phinx\Console\PhinxApplication
     */
    public $app;

    /** @var  array */
    public $config;

    /** @var  string  */
    public $tempDir;


    public function __construct($config, $tempDir) {
        $this->oneSecond = new \DateInterval("PT1S");
        $this->app = new PhinxApplication();
        $this->config = $config;
        $this->tempDir = $tempDir;
    }


    public function run() {
        $configFile = "{$this->tempDir}/phinx.json";
        file_put_contents($configFile, json_encode($this->config));
        $command = ['migrate'];
        $command += ['-e' => 'development'];
        $command += ['-c' => $configFile];

        // Output will be written to a temporary stream, so that it can be
        // collected after running the command.
        $stream = fopen('php://temp', 'w+');

        // Execute the command, capturing the output in the temporary stream
        // and storing the exit code for debugging purposes.
        $this->exit_code = $this->app->doRun(new ArrayInput($command), new StreamOutput($stream));

        // Get the output of the command and close the stream, which will
        // destroy the temporary file.
        $result = stream_get_contents($stream, -1, 0);
        fclose($stream);

        echo  $result;

    }

    /**
     * @param $dumpFile string mysql dump file it must dump only one table
     * @param $table string destination table. It must exists
     * @param $outFolder string folder to put the migration files
     * @param int $maxInserts maximum number of inserts per file
     * @throws \Exception
     */
    public function createMigrationFromDumpFile($dumpFile, $table, $outFolder, $maxInserts = 2000) {
        $handle = fopen($dumpFile, "r");
        if (!$handle) {
            throw new \Exception("File $dumpFile cannot be open.");
        }
        $line = fgets($handle);
        $count = 1;
        $now = new \DateTime();
        $nLines = 0;
        $entries = [];
        $fileName = null;
        while ($line !== false) {
            if (preg_match('/array\((.*)\)/', $line, $matches)) {
                if ($nLines % $maxInserts == 0) {
                    $fileName = $this->writeToFile($fileName, $outFolder, $entries, $table, $now, $count);
                    $entries = [];
                }
                $entries[] = $matches[1];
                $nLines++;
            }
            $line = fgets($handle);
        }
        $this->writeToFile($fileName, $outFolder, $entries, $table, $now, $count);
        fclose($handle);
    }

    /**
     * @param $fileName string
     * @param $outFolder string
     * @param $entries array
     * @param $table string
     * @param $now \DateTime
     * @param $count int
     * @return string
     */
    private function writeToFile($fileName, $outFolder, $entries, $table, &$now, &$count) {
        if ($fileName) {
            //Convert table to classname
            $className = '';
            $toUpper = true;
            foreach (str_split($table) as $chr) {
                if ($toUpper) {
                    $className .= strtoupper($chr);
                    $toUpper = false;
                } else if ($chr == '_') {
                    $toUpper = true;
                } else {
                    $className .= $chr;
                }
            }
            $className .="Data$count";
            $entries = array_map(function ($e) {return "array($e)";}, $entries);
            $contents = "<?php"."\n".
"use Phinx\\Migration\\AbstractMigration;"."\n".
"class $className extends AbstractMigration {"."\n".
"    public function up() {"."\n".
"        \$this->table('$table')->insert(["."\n".
join(",\n", $entries)."\n".
"        ])->save();"."\n".
"    }"."\n".
"}";
            file_put_contents($outFolder.DIRECTORY_SEPARATOR.$fileName, $contents);
            $count++;
            $now = $now->add($this->oneSecond);
        }
        $fileName = "{$now->format('YmdHis')}_{$table}_data$count.php";
        return $fileName;
    }
}