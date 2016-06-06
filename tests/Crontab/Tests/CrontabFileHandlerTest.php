<?php

use Crontab\Crontab;
use Crontab\CrontabFileHandler;

/**
 * CrontabFileHandlerTest
 *
 * @author Jacob Kiers <jacob@alphacomm.nl>
 */
class CrontabFileHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Crontab
     */
    private $crontab;

    /**
     * @var CrontabFileHandler
     */
    private $crontabFileHandler;

    /**
     * @var string with the path to the temporary file
     */
    private $tempFile;

    /**
     * @var string with the path to the fixture file
     */
    private $fixtureFile;

    public function setUp()
    {
        $fixturesDir = __DIR__.'/../../fixtures';
        $this->fixtureFile = $fixturesDir."/crontab";
        $this->tempFile = tempnam(sys_get_temp_dir(), 'cron');
        $this->crontabFileHandler = new CrontabFileHandler();
        $this->crontab = new Crontab();
    }

    public function tearDown()
    {
        if(file_exists($this->tempFile)) 
        {
            chmod($this->tempFile, 0600);
            unlink($this->tempFile);
        }
    }


    public function testParseFromFile()
    {
        $this->crontabFileHandler->parseFromFile($this->crontab, $this->fixtureFile);
        $this->assertCount(12, $this->crontab->getJobs());

        $jobs = $this->crontab->getJobs();
        $job1 = array_shift($jobs);
        $job2 = array_shift($jobs);
        $job3 = array_shift($jobs);
        $job4 = array_shift($jobs);

        $this->assertEquals('cmd', $job1->getCommand());
        $this->assertEquals('cmd2', $job2->getCommand());
        $this->assertEquals('indentedCommand with whitespaces', $job3->getCommand());
        // Job 4 contains a variable
        $this->assertEquals('$BIN_PATH/cmd3', $job4->getCommand());
    }

    public function testParseVariablesFromFile()
    {
        $this->crontabFileHandler->parseFromFile($this->crontab, $this->fixtureFile);
        $this->assertCount(2, $this->crontab->getVariables());

        $variables = $this->crontab->getVariables();
        $var1 = array_shift($variables);
        $var2 = array_shift($variables);

        $this->assertEquals('MAILTO', $var1->getName());
        $this->assertEquals('root@localhost.localdomain', $var1->getValue());
        $this->assertEquals("MAILTO=root@localhost.localdomain", $var1->render());
        $this->assertEquals('BIN_PATH', $var2->getName());
    }
    private function stripWhiteSpace($string)
    {
        return implode(PHP_EOL, array_map(function ($str) {
            return preg_replace('/\s+/', ' ', $str);
        }, preg_split("/$/", trim($string), null, PREG_SPLIT_NO_EMPTY)));
    }

    public function testWriteToFileIsSuccessfulWhenFileIsWritable()
    {
        $this->crontabFileHandler->parseFromFile($this->crontab, $this->fixtureFile);

        $this->crontabFileHandler->writeToFile($this->crontab, $this->tempFile);

        $fixtureContents = $this->stripWhiteSpace(file_get_contents($this->fixtureFile));
        $fileContents = $this->stripWhiteSpace(file_get_contents($this->tempFile));

        $this->assertSame($fixtureContents, $fileContents);
    }

    public function testWriteToFileContainsVariables()
    {
        $this->crontabFileHandler->parseFromFile($this->crontab, $this->fixtureFile);
        $this->crontabFileHandler->writeToFile($this->crontab, $this->tempFile);

        $fixtureContents = $this->stripWhiteSpace(file_get_contents($this->fixtureFile));
        $fileContents = $this->stripWhiteSpace(file_get_contents($this->tempFile));

        $this->assertSame($fixtureContents, $fileContents);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWriteToFileThrowsExceptionWhenFileIsNotWritable()
    {
        $this->crontabFileHandler->parseFromFile($this->crontab, $this->fixtureFile);

        touch($this->tempFile);
        chmod($this->tempFile, 0400);

        $this->crontabFileHandler->writeToFile($this->crontab, $this->tempFile);
        // Expected an InvalidArgumentException because the file is not writable.
    }

    public function testParseFromFileWithSpecialWords()
    {
        $this->crontabFileHandler->parseFromFile($this->crontab, $this->fixtureFile);

        $this->assertCount(12, $this->crontab->getJobs());

        $specialsContents = $this->stripWhiteSpace(file_get_contents($this->fixtureFile));
        $output = $this->stripWhiteSpace($this->crontab->render());

        $this->assertEquals($specialsContents, $output);

        $jobs = $this->crontab->getJobs();
        $job1 = array_shift($jobs);
        $job2 = array_shift($jobs);
        $job3 = array_shift($jobs);
        $job4 = array_shift($jobs);

        $this->assertInstanceOf('Crontab\\Job', $job1);
        $this->assertInstanceOf('Crontab\\Job', $job2);
        $this->assertInstanceOf('Crontab\\Job', $job3);
        $this->assertInstanceOf('Crontab\\Job', $job4);

        // Remaining Jobs are JobSpecial instances
        foreach ($jobs as $job) {
            $this->assertInstanceOf('Crontab\\JobSpecial', $job);
        }
    }
}
