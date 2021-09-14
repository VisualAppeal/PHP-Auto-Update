<?php

require(__DIR__ . '/../../../vendor/autoload.php');

use \VisualAppeal\AutoUpdate;

$update = new AutoUpdate(__DIR__ . '/temp', __DIR__ . '/../', 60);
$update->setCurrentVersion('0.1.0');
// Replace with your server update directory
$update->setUpdateUrl('http://127.0.0.1:8090/example/server');

// Custom logger (optional)
$logger = new \Monolog\Logger("default");
$logger->pushHandler(new Monolog\Handler\StreamHandler(__DIR__ . '/update.log'));
$update->setLogger($logger);

// Cache (optional but recommended)
$cache = new Desarrolla2\Cache\File(__DIR__ . '/cache');
$update->setCache($cache, 3600);

// Check for a new update
if ($update->checkUpdate() === false) {
    die('Could not check for updates! See log file for details.');
}

if ($update->newVersionAvailable()) {
    // Install new update
    echo 'New Version: ' . $update->getLatestVersion() . '<br>';
    echo 'Installing Updates: <br>';
    echo '<pre>';
    var_dump(array_map(function ($version) {
        return (string) $version;
    }, $update->getVersionsToUpdate()));
    echo '</pre>';

    // Optional - empty log file
    $f = @fopen(__DIR__ . '/update.log', 'rb+');
    if ($f !== false) {
        ftruncate($f, 0);
        fclose($f);
    }

    // Optional Callback function - on each version update
    function eachUpdateFinishCallback($updatedVersion)
    {
        echo '<h3>CALLBACK for version ' . $updatedVersion . '</h3>';
    }
    $update->onEachUpdateFinish('eachUpdateFinishCallback');

    // Optional Callback function - on each version update
    function onAllUpdateFinishCallbacks($updatedVersions)
    {
        echo '<h3>CALLBACK for all updated versions:</h3>';
        echo '<ul>';
        foreach ($updatedVersions as $v) {
            echo '<li>' . $v . '</li>';
        }
        echo '</ul>';
    }
    $update->setOnAllUpdateFinishCallbacks('onAllUpdateFinishCallbacks');

    // This call will only simulate an update.
    // Set the first argument (simulate) to "false" to install the update
    // i.e. $update->update(false);
    $result = $update->update();

    if ($result === true) {
        echo 'Update simulation successful<br>';
    } else {
        echo 'Update simulation failed: ' . $result . '!<br>';

        if ($result = AutoUpdate::ERROR_SIMULATE) {
            echo '<pre>';
            var_dump($update->getSimulationResults());
            echo '</pre>';
        }
    }
} else {
    echo 'Current Version is up to date<br>';
}

echo 'Log:<br>';
echo nl2br(file_get_contents(__DIR__ . '/update.log'));
