<?php
namespace Deployer;

require 'recipe/common.php';

set('shared_dirs', ['var/log', 'var/sessions']);
set('shared_files', ['.env.local','.env.test']);
set('writable_dirs', ['var']);
#set('writable_mode', 'chmod');
#set('http_user', 'admin3cie');
set('migrations_config', '');

set('bin/php', function () {
    return '/opt/plesk/php/'.$_SERVER['PHP_VERSION'].'/bin/php';
});
set('bin/composer', function () {
    return '/opt/plesk/php/'.$_SERVER['PHP_VERSION'].'/bin/php /usr/lib/plesk-9.0/composer.phar';
});

set('bin/yarn', function () {
    return run('which yarn');
});

set('composer_options', '{{composer_action}} --verbose --prefer-dist --no-progress --no-interaction --optimize-autoloader --ignore-platform-reqs');

set('bin/console', function () {
    return parse('{{release_path}}/bin/console');
});

set('console_options', function () {
    return '--no-interaction';
});

// Project name
set('application', $_SERVER['NAME_APP']);

// Project repository
set('repository', $_SERVER['GITHUB_REPO']);

// Writable dirs by web server
set('allow_anonymous_stats', false);

// Hosts
host($_SERVER["HOST_TO_DEPLOY"])
    ->set('deploy_path', $_SERVER["PATH_TO_DEPLOY"])
    ->user($_SERVER["USERNAME"])
    ->forwardAgent();

desc('Migrate database');
task('database:migrate', function () {
    $options = '--allow-no-migration';
    if (get('migrations_config') !== '') {
        $options = sprintf('%s --configuration={{release_path}}/{{migrations_config}}', $options);
    }

    run(sprintf('{{bin/php}} {{bin/console}} doctrine:migrations:migrate %s {{console_options}}', $options));
});

desc('Clear cache');
task('deploy:cache:clear', function () {
    run('{{bin/php}} {{bin/console}} cache:clear {{console_options}} --no-warmup');
});

desc('Warm up cache');
task('deploy:cache:warmup', function () {
    run('{{bin/php}} {{bin/console}} cache:warmup {{console_options}}');
});

desc('Deploy project');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:vendors',
    'deploy:writable',
    'deploy:cache:clear',
    'deploy:cache:warmup',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
]);

task('yarn:install', function () {
    if (has('previous_release')) {
        if (test('[ -d {{previous_release}}/node_modules ]')) {
            run('cp -R {{previous_release}}/node_modules {{release_path}}');
        }
    }
    run("cd {{release_path}} && {{bin/yarn}}");
});

task('yarn:build', function () {
    run("cd {{release_path}} && {{bin/yarn}} build");
});

after('deploy', 'success');

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

before('deploy:symlink', 'database:migrate');

after('deploy:update_code', 'yarn:install');
after('yarn:install','yarn:build');