<?php

/*
 * Set the script max execution time
 */
ini_set('max_execution_time', 60);

define('UPDATE_DIR_TEMP', dirname(__FILE__).'/temp/');
define('UPDATE_DIR_INSTALL', dirname(__FILE__).'/../');

class AutoUpdate {
	/*
	 * Enable logging
	 */
	private $_log = false;
	
	/* 
	 * Log file
	 */
	public $logFile = '.updatelog';
	
	/*
	 * The last error
	 */
	private $_lastError = null;
	
	/*
	 * Current version
	 */
	public $currentVersion = 0;
	
	/*
	 * Name of the latest version
	 */
	public $latestVersionName = '';
	
	/*
	 * The latest version
	 */
	public $latestVersion = null;
	
	/*
	 * Url to the latest version of the update
	 */
	public $latestUpdate = null;
	
	/*
	 * Url to the update folder on the server
	 */
	public $updateUrl = 'http://example.com/updates/';
	
	/*
	 * Version filename on the server
	 */
	public $updateIni = 'update.ini';
	
	/*
	 * Temporary download directory
	 */
	public $tempDir = UPDATE_DIR_TEMP;
	
	/*
	 * Remove temprary directory after installation
	 */
	public $removeTempDir = true;
	
	/*
	 * Install directory
	 */
	public $installDir = UPDATE_DIR_INSTALL;
	
	/*
	 * Create new folders with this privileges
	 */
	public $dirPermissions = 0755;
	
	/*
	 * Update script filename
	 */
	public $updateScriptName = '_upgrade.php';
	
	/*
	 * Create new instance
	 *
	 * @param bool $log Default: false
	 */
	public function __construct($log = false) {
		$this->_log = $log;
	}
	
	/* 
	 * Log a message if logging is enabled
	 *
	 * @param string $message The message
	 *
	 * @return void
	 */
	public function log($message) {
		if ($this->_log) {
			$this->_lastError = $message;
			
			$log = fopen($this->logFile, 'a');
			
			if ($log) {
				$message = date('<Y-m-d H:i:s>').$message."\n";
				fputs($log, $message);
				fclose($log);
			}
			else {
				die('Could not write log file!');
			}
		}
	}
	
	/*
	 * Get the latest error
	 *
	 * @return string Last error
	 */
	public function getLastError() {
		if (!is_null($this->_lastError))
			return $this->_lastError;
		else
			return false;
	}
	
	private function _removeDir($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dir."/".$object) == "dir") 
						$this->_removeDir($dir."/".$object); 
					else 
						unlink($dir."/".$object);
				}
			}
			reset($objects);
			rmdir($dir);
		}
	}
	
	/*
	 * Check for a new version
	 *
	 * @return string The latest version
	 */
	public function checkUpdate() {
		$this->log('Checking for a new update. . .');
		
		$updateFile = $this->updateUrl.'/update.ini';
		
		$update = @file_get_contents($updateFile);
		if ($update === false) {
			$this->log('Could not retrieve update file `'.$updateFile.'`!');
			return false;
		}
		else {
			$versions = parse_ini_string($update, true);
			if (is_array($versions)) {
				$keyOld = 0;
				$latest = 0;
				$update = '';
				
				foreach ($versions as $key => $version) {
					if ($key > $keyOld) {
						$keyOld = $key;
						$latest = $version['version'];
						$update = $version['url']; 
					}
				}
				
				$this->log('New version found `'.$latest.'`.');
				$this->latestVersion = $keyOld;
				$this->latestVersionName = $latest;
				$this->latestUpdate = $update;
				
				return $keyOld;
			}
			else {
				$this->log('Unable to parse update file!');
				return false;
			}
		}
	}
	
	/*
	 * Download the update
	 *
	 * @param string $updateUrl Url where to download from
	 * @param string $updateFile Path where to save the download
	 */
	public function downloadUpdate($updateUrl, $updateFile) {
		$this->log('Downloading update...');
		$update = @file_get_contents($updateUrl);
		
		if ($update === false) {
			$this->log('Could not download update `'.$updateUrl.'`!');
			return false;
		}
		
		$handle = fopen($updateFile, 'w');
		
		if (!$handle) {
			$this->log('Could not save update file `'.$updateFile.'`!');
			return false;
		}
		
		if (!fwrite($handle, $update)) {
			$this->log('Could not write to update file `'.$updateFile.'`!');
			return false;
		}
		
		fclose($handle);
		
		return true;
	}
	
	/*
	 * Install update
	 *
	 * @param string $updateFile Path to the update file
	 */
	public function install($updateFile) {
		$zip = zip_open($updateFile);
			
		while ($file = zip_read($zip)) {				
			$filename = zip_entry_name($file);
			$foldername = $this->installDir.dirname($filename);
			
			$this->log('Updating `'.$filename.'`!');
			
			if (!is_dir($foldername)) {
				if (!mkdir($foldername, $this->dirPermissions, true)) {
					$this->log('Could not create folder `'.$foldername.'`!');
				}
			}
			
			$contents = zip_entry_read($file, zip_entry_filesize($file));
			
			//Skip if entry is a directory
			if (substr($filename, -1, 1) == '/')
				continue;
			
			//Write to file
			if (file_exists($this->installDir.$filename)) {
				if (!is_writable($this->installDir.$filename)) {
					$this->log('Could not update `'.$this->installDir.$filename.'`, not writable!');
					return false;
				}
			} else {
				$this->log('The file `'.$this->installDir.$filename.'`, does not exist!');			
				$new_file = fopen($this->installDir.$filename, "w") or $this->log('The file `'.$this->installDir.$filename.'`, could not be created!');
				fclose($new_file);
				$this->log('The file `'.$this->installDir.$filename.'`, was succesfully created.');
			}
			
			$updateHandle = @fopen($this->installDir.$filename, 'w');
			
			if (!$updateHandle) {
				$this->log('Could not update file `'.$this->installDir.$filename.'`!');
				return false;
			}
			
			if (!fwrite($updateHandle, $contents)) {
				$this->log('Could not write to file `'.$this->installDir.$filename.'`!');
				return false;
			}
			
			fclose($updateHandle);
			
			//If file is a update script, include
			if ($filename == $this->updateScriptName) {
				$this->log('Try to include update script `'.$this->installDir.$filename.'`.');
				require($this->installDir.$filename);
				$this->log('Update script `'.$this->installDir.$filename.'` included!');
				unlink($this->installDir.$filename);
			}
		}
		
		zip_close($zip);
		
		if ($this->removeTempDir) {
			$this->log('Temporary directory `'.$this->tempDir.'` deleted.');
			$this->_removeDir($this->tempDir);
		}
		
		$this->log('Update `'.$this->latestVersion.'` installed.');
		
		return true;
	}
	
	
	/*
	 * Update to the latest version
	 */
	public function update() {
		//Check for latest version
		if ((is_null($this->latestVersion)) or (is_null($this->latestUpdate))) {
			$this->checkUpdate();
		}
		
		if ((is_null($this->latestVersion)) or (is_null($this->latestUpdate))) {
			return false;
		}
		
		//Update
		if ($this->latestVersion > $this->currentVersion) {
			$this->log('Updating...');
			
			//Add slash at the end of the path
			if ($this->tempDir[strlen($this->tempDir)-1] != '/');
				$this->tempDir = $this->tempDir.'/';
			
			if ((!is_dir($this->tempDir)) and (!mkdir($this->tempDir, 0777, true))) {
				$this->log('Temporary directory `'.$this->tempDir.'` does not exist and could not be created!');
				return false;
			}
			
			if (!is_writable($this->tempDir)) {
				$this->log('Temporary directory `'.$this->tempDir.'` is not writeable!');
				return false;
			}
			
			$updateFile = $this->tempDir.'/'.$this->latestVersion.'.zip';
			$updateUrl = $this->updateUrl.'/'.$this->latestVersion.'.zip';
			
			//Download update
			if (!is_file($updateFile)) {
				if (!$this->downloadUpdate($updateUrl, $updateFile)) {
					$this->log('Failed to download update!');
					return false;
				}
				
				$this->log('Latest update downloaded to `'.$updateFile.'`.');
			}
			else {
				$this->log('Latest update already downloaded to `'.$updateFile.'`.');
			}
			
			//Unzip
			return $this->install($updateFile);
		}
		else {
			$this->log('No update available!');
			return false;
		}
	}
}