<?php

require_once 'phing/BuildFileTest.php';

/**
 * Tests the Relentless Task
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class RelentlessTest extends BuildFileTest
{
    public function setUp()
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/RelentlessTest.xml'
        );
    }

    public function testRelentless()
    {
        $this->expectLogContaining(__FUNCTION__, 'Executing: task 3');
    }

    public function testTerse()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertNotInLogs('Executing: task 3');
    }

    /**
     * @expectedException BuildException
     * @expectedExceptionMessage Relentless execution: 1 of 5 tasks failed.
     */
    public function testFailure()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('Task task 3 failed: baz');
    }
}
