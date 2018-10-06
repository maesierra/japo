<?php
namespace Deployer;

require 'recipe/common.php';

// Project name
set('application', 'japo');

// Project repository
set('repository', 'ssh://maesierra@maesierra.net/home/maesierra/repo/git/japo');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', false);

// Shared files/dirs between deploys 
set('shared_files', []);
set('shared_dirs', []);

// Writable dirs by web server 
set('writable_dirs', []);
set('allow_anonymous_stats', false);
set('default_stage', 'local');

// Hosts

host('localhost')
    ->user('vagrant')
    ->port(2222)
    ->identityFile('vagrant/private_key')
    ->addSshOption('UserKnownHostsFile', '/dev/null')
    ->addSshOption('StrictHostKeyChecking', 'no')
    ->set('deploy_path', '~/{{application}}')
    ->stage('local');
    

// Tasks

desc('Deploy your project');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    'deploy:vendors',
    'deploy:clear_paths',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success'
]);



// [Optional] If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
