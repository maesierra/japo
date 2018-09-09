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

    /**
     * @var \Phinx\Console\PhinxApplication
     */
    public $app;

    /** @var  array */
    public $config;

    /** @var  string  */
    public $tempDir;


    public function __construct($config, $tempDir) {
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
}