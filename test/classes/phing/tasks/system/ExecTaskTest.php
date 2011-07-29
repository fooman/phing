<?php
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

require_once 'phing/BuildFileTest.php';

/**
 * Tests the Exec Task
 *
 * @author  Michiel Rook <mrook@php.net>
 * @version $Id$
 * @package phing.tasks.system
 */
class ExecTaskTest extends BuildFileTest
{
    public function setUp()
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/ExecTest.xml'
        );
    }

    protected function getTargetByName($name)
    {
        foreach ($this->project->getTargets() as $target) {
            if ($target->getName() == $name) {
                return $target;
            }
        }
        throw new Exception(sprintf('Target "%s" not found', $name));
    }

    protected function getTaskFromTarget($target, $taskname, $pos = 0)
    {
        $rchildren = new ReflectionProperty(get_class($target), 'children');
        $rchildren->setAccessible(true);
        $n = -1;
        foreach ($rchildren->getValue($target) as $child) {
            if ($child instanceof Task && ++$n == $pos) {
                return $child;
            }
        }
        throw new Exception(
            sprintf('%s #%d not found in task', $taskname, $pos)
        );
    }

    protected function getConfiguredTask($target, $task, $pos = 0)
    {
        $target = $this->getTargetByName($target);
        $task = $this->getTaskFromTarget($target, $task);
        $task->maybeConfigure();
        return $task;
    }

    protected function assertPropertyIsSetTo($property, $value, $propertyName = null)
    {
        $task = $this->getConfiguredTask(
            'testPropertySet' . ucfirst($property), 'ExecTask'
        );

        if ($propertyName === null) {
            $propertyName = $property;
        }
        $rprop = new ReflectionProperty('ExecTask', $propertyName);
        $rprop->setAccessible(true);
        $this->assertEquals($value, $rprop->getValue($task));
    }

    public function testPropertySetCommand()
    {
        $this->assertPropertyIsSetTo('command', "echo 'foo'");
    }

    public function testPropertySetDir()
    {
        $this->assertPropertyIsSetTo(
            'dir',
            new PhingFile(
                realpath(dirname(__FILE__) . '/../../../../etc/tasks/system')
            )
        );
    }

    public function testPropertySetOs()
    {
        $this->assertPropertyIsSetTo('os', "linux");
    }

    public function testPropertySetEscape()
    {
        $this->assertPropertyIsSetTo('escape', true);
    }

    public function testPropertySetLogoutput()
    {
        $this->assertPropertyIsSetTo('logoutput', true, 'logOutput');
    }

    public function testPropertySetPassthru()
    {
        $this->assertPropertyIsSetTo('passthru', true);
    }

    public function testPropertySetSpawn()
    {
        $this->assertPropertyIsSetTo('spawn', true);
    }

    public function testPropertySetReturnProperty()
    {
        $this->assertPropertyIsSetTo('returnProperty', 'retval');
    }

    public function testPropertySetOutputProperty()
    {
        $this->assertPropertyIsSetTo('outputProperty', 'outval');
    }

    public function testPropertySetCheckReturn()
    {
        $this->assertPropertyIsSetTo('checkreturn', true);
    }

    public function testPropertySetOutput()
    {
        $this->assertPropertyIsSetTo(
            'output',
            new PhingFile(
                realpath(dirname(__FILE__) . '/../../../../etc/tasks/system')
                . '/outputfilename'
            )
        );
    }

    public function testPropertySetError()
    {
        $this->assertPropertyIsSetTo(
            'error',
            new PhingFile(
                realpath(dirname(__FILE__) . '/../../../../etc/tasks/system')
                . '/errorfilename'
            )
        );
    }

    public function testPropertySetLevelError()
    {
        $this->assertPropertyIsSetTo('levelError', Project::MSG_ERR, 'logLevel');
    }

    public function testPropertySetLevelWarning()
    {
        $this->assertPropertyIsSetTo('levelWarning', Project::MSG_WARN, 'logLevel');
    }

    public function testPropertySetLevelInfo()
    {
        $this->assertPropertyIsSetTo('levelInfo', Project::MSG_INFO, 'logLevel');
    }

    public function testPropertySetLevelVerbose()
    {
        $this->assertPropertyIsSetTo('levelVerbose', Project::MSG_VERBOSE, 'logLevel');
    }

    public function testPropertySetLevelDebug()
    {
        $this->assertPropertyIsSetTo('levelDebug', Project::MSG_DEBUG, 'logLevel');
    }

    /**
     * @expectedException BuildException
     * @expectedExceptionMessage Unknown log level "unknown"
     */
    public function testPropertySetLevelUnknown()
    {
        $this->getConfiguredTask('testPropertySetLevelUnknown', 'ExecTask');
    }


    public function testDoNotExecuteOnWrongOs()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('Not found in unknownos');
        $this->assertNotContains(
            'this should not be executed',
            $this->getOutput()
        );
    }

    public function testExecuteOnCorrectOs()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('this should be executed');
    }


    /**
     * @expectedException BuildException
     * @expectedExceptionMessage '/this/dir/does/not/exist' is not a valid directory
     */
    public function testFailOnNonExistingDir()
    {
        $this->executeTarget(__FUNCTION__);
    }


    public function testChangeToDir()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('ExecTaskTest.php');
    }

    public function testCheckreturnTrue()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertTrue(true);
    }

    /**
     * @expectedException BuildException
     * @expectedExceptionMessage Task exited with code 1
     */
    public function testCheckreturnFalse()
    {
        $this->executeTarget(__FUNCTION__);
    }

    public function testOutputProperty()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('The output property\'s value is: "foo"');
    }

    public function testReturnProperty()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('The return property\'s value is: "1"');
    }

    public function testEscape()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('foo | cat');
    }

    public function testPassthru()
    {
        ob_start();
        $this->executeTarget(__FUNCTION__);
        $out = ob_get_clean();
        $this->assertEquals("foo\n", $out);
        //foo should not be in logs, except for the logged command
        $this->assertInLogs('echo foo');
        $this->assertNotContains('foo', $this->logBuffer);
    }

    public function testOutput()
    {
        $file = tempnam(sys_get_temp_dir(), 'phing-exectest-');
        $this->project->setProperty('execTmpFile', $file);
        $this->executeTarget(__FUNCTION__);
        $this->assertContains('outfoo', file_get_contents($file));
        unlink($file);
    }

    public function testError()
    {
        $file = tempnam(sys_get_temp_dir(), 'phing-exectest-');
        $this->project->setProperty('execTmpFile', $file);
        $this->executeTarget(__FUNCTION__);
        $this->assertContains('errfoo', file_get_contents($file));
        unlink($file);
    }

    public function testSpawn()
    {
        $start = time();
        $this->executeTarget(__FUNCTION__);
        $end = time();
        $this->assertLessThan(
            4, $end - $start,
            'Time between start and end should be lower than 4 seconds'
            . ' - otherwise it looks as spawning did not work'
        );
    }
}

?>