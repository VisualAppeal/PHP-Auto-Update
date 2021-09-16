<?php

require(__DIR__ . '/../vendor/autoload.php');

use PHPUnit\Framework\TestCase;

use VisualAppeal\AutoUpdate;
use VisualAppeal\Exceptions\DownloadException;
use VisualAppeal\Exceptions\ParserException;

class AutoUpdateTest extends TestCase
{
    /**
     * AutoUpdate instance.
     *
     * @var AutoUpdate
     */
    private $_update;

    /**
     * Setup the auto update.
     */
    protected function setUp(): void
    {
        $this->_update = new AutoUpdate(__DIR__ . DIRECTORY_SEPARATOR . 'temp', __DIR__ . DIRECTORY_SEPARATOR . 'install');
        $this->_update->setCurrentVersion('0.1.0');
        $this->_update->setUpdateUrl(__DIR__ . DIRECTORY_SEPARATOR . 'fixtures');
        $logger = new Monolog\Logger("default");
        $logger->pushHandler(new Monolog\Handler\StreamHandler(__DIR__ . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . 'update.log'));
        $this->_update->setLogger($logger);
    }

    /**
     * Unset the auto update.
     */
    protected function tearDown(): void
    {
        unset($this->_update);
        $this->_update = null;
    }

    /**
     * Test creation of class instance.
     */
    public function testInit(): void
    {
        self::assertInstanceOf(AutoUpdate::class, $this->_update);
    }

    /**
     * Test if errors get catched if no update file was found.
     *
     * @throws ParserException|\Psr\SimpleCache\InvalidArgumentException
     */
    public function testErrorUpdateCheck(): void
    {
        $this->expectException(DownloadException::class);

        $this->_update->setUpdateFile('404.json');
        $this->_update->checkUpdate();

        self::assertFalse($this->_update->newVersionAvailable());
        self::assertCount(0, $this->_update->getVersionsToUpdate());
    }

    /**
     * Test if new update is available with a json file.
     *
     * @throws DownloadException
     * @throws ParserException|\Psr\SimpleCache\InvalidArgumentException
     */
    public function testJsonNewVersion(): void
    {
        $this->_update->setUpdateFile('updateAvailable.json');
        $response = $this->_update->checkUpdate();

        self::assertTrue($response);
        self::assertTrue($this->_update->newVersionAvailable());
        self::assertEquals('0.2.1', $this->_update->getLatestVersion());

        $newVersions = $this->_update->getVersionsToUpdate();
        self::assertCount(2, $newVersions);
        self::assertEquals('0.2.0', $newVersions[0]);
        self::assertEquals('0.2.1', $newVersions[1]);
    }

    /**
     * Test if NO new update is available with a json file.
     *
     * @throws DownloadException
     * @throws ParserException|\Psr\SimpleCache\InvalidArgumentException
     */
    public function testJsonNoNewVersion(): void
    {
        $this->_update->setUpdateFile('noUpdateAvailable.json');
        $response = $this->_update->checkUpdate();

        self::assertEquals(AutoUpdate::NO_UPDATE_AVAILABLE, $response);
        self::assertFalse($this->_update->newVersionAvailable());
        self::assertCount(0, $this->_update->getVersionsToUpdate());
    }

    /**
     * Test if new update is available with a ini file.
     *
     * @throws DownloadException
     * @throws ParserException|\Psr\SimpleCache\InvalidArgumentException
     */
    public function testIniNewVersion(): void
    {
        $this->_update->setUpdateFile('updateAvailable.ini');
        $response = $this->_update->checkUpdate();

        self::assertTrue($response);
        self::assertTrue($this->_update->newVersionAvailable());
        self::assertEquals('0.2.1', $this->_update->getLatestVersion());

        $newVersions = $this->_update->getVersionsToUpdate();
        self::assertCount(2, $newVersions);
        self::assertEquals('0.2.0', $newVersions[0]);
        self::assertEquals('0.2.1', $newVersions[1]);
    }

    /**
     * Test if NO new update is available with a ini file.
     *
     * @throws DownloadException
     * @throws ParserException|\Psr\SimpleCache\InvalidArgumentException
     */
    public function testIniNoNewVersion(): void
    {
        $this->_update->setUpdateFile('noUpdateAvailable.ini');
        $response = $this->_update->checkUpdate();

        self::assertEquals(AutoUpdate::NO_UPDATE_AVAILABLE, $response);
        self::assertFalse($this->_update->newVersionAvailable());
        self::assertCount(0, $this->_update->getVersionsToUpdate());
    }

    /**
     * Ensure that a new dev version is available.
     *
     * @throws DownloadException
     * @throws ParserException|\Psr\SimpleCache\InvalidArgumentException
     */
    public function testBranchDev(): void
    {
        $this->_update->setUpdateFile('updateAvailable.json');
        $this->_update->setBranch('dev');
        $response = $this->_update->checkUpdate();

        self::assertTrue($response);
    }

    /**
     * Ensure that no new master version is available
     *
     * @throws DownloadException
     * @throws ParserException|\Psr\SimpleCache\InvalidArgumentException
     */
    public function testBranchMaster(): void
    {
        $this->_update->setUpdateFile('noUpdateAvailable.json');
        $this->_update->setBranch('master');
        $response = $this->_update->checkUpdate();

        self::assertEquals(AutoUpdate::NO_UPDATE_AVAILABLE, $response);
    }

    /**
     * Test the trailing slash method.
     */
    public function testTrailingSlashes(): void
    {
        $dir = DIRECTORY_SEPARATOR . 'test';
        self::assertEquals(DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR, $this->_update->addTrailingSlash($dir));

        $dir = DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR;
        self::assertEquals(DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR, $this->_update->addTrailingSlash($dir));
    }
}
