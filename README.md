## Installation

* Copy the `update` folder to your project (what should be updated)
* Create a update.ini on your server (where the updates are from)

**Important: Please notice that PHP needs write permissions to update the files on your webserver**

## Description

The update folder consists of two files:

* `index.php` - includes the class and calls the update 
* `update.php` - update class

You should not call the update out of your project (what happens if you try to update a file which is in use at the moment?). At your server you need a folder with a configuration file `update.ini` and the ziped versions.

### update.ini

The `update.ini` should look like this:

	[1]
	version = 0.1
	url = http://example.com/updates/0.1.zip
	
	[2]
	version = 0.2
	url = http://example.com/updates/0.2.zip

You can name the versions whatever you want but the sections (for example `[1]`) has to be an integer so that the class can compare the versions. You cannot compare `1.4.3-13.r1441` with `1.4.3-12.r1445` without problems. The `url` has to be absolute.

### Update files
Zip your project and rename it to `version.zip`. The zip file should include all of the project files.

### Check for an update
	
	/*
	 * Index.php in the folder update
	 */
	
	require('update.php');

	$update = new AutoUpdate(true); //Enable logging

	$update->currentVersion = 1;
	
	/*
	 * In this example the folder 'updateUrl' includes 3 files, 0.1.zip, 0.2.zip and update.ini
	 */
	$update->updateUrl = 'http://example.com/updates';

	$latest = $update->checkUpdate();
	
	if ($latest !== false) {
		if ($latest > $update->currentVersion) {
			//Install new update
			echo "New Version: ".$update->latestVersionName."<br>";
			
			//You can also stop here and let the user decide when to update.
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


[![Analytics](https://ga-beacon.appspot.com/UA-46905576-1/your-repo/page-name)](https://github.com/VisualAppeal/PHP-Auto-Update)
