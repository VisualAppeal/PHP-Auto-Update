<?php 

require('update.php');

$update = new AutoUpdate(true);
$update->currentVersion = 1; //Must be an integer - you can't compare strings
$update->updateUrl = 'http://127.0.0.1/_va/dev/php-autoupdate/server'; //Replace with your server update directory

//Check for a new update
$latest = $update->checkUpdate();
if ($latest !== false) {
	if ($latest > $update->currentVersion) {
		//Install new update
		echo "New Version: ".$update->latestVersionName."<br>";
		echo "Installing Update...<br>";
		if ($update->update()) {
			echo "Update successful!";
		}
		else {
			echo "Update failed!";
		}
		
	}
	else {
		echo "Current Version is up to date";
	}
}
else {
	echo $update->getLastError();
}

?>