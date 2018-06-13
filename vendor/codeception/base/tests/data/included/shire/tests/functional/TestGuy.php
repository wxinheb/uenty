<?php //[STAMP] 481f1bf8d02786cb5c4fc661670aab66

// This class was automatically generated by build task
// You should not change it manually as it will be overwritten on next build
// @codingStandardsIgnoreFile

namespace Shire;
use Codeception\Module\Filesystem;
use Shire\Codeception\Module\TestHelper;


class TestGuy extends \Codeception\Actor
{
   
    
    public function amInPath($path) {
        return $this->scenario->runStep(new \Codeception\Step\Condition('amInPath', func_get_args()));
    }

 
    
    public function openFile($filename) {
        return $this->scenario->runStep(new \Codeception\Step\Action('openFile', func_get_args()));
    }

 
    
    public function deleteFile($filename) {
        return $this->scenario->runStep(new \Codeception\Step\Action('deleteFile', func_get_args()));
    }

 
    
    public function deleteDir($dirname) {
        return $this->scenario->runStep(new \Codeception\Step\Action('deleteDir', func_get_args()));
    }

 
    
    public function copyDir($src, $dst) {
        return $this->scenario->runStep(new \Codeception\Step\Action('copyDir', func_get_args()));
    }

 
    
    public function canSeeInThisFile($text) {
        return $this->scenario->runStep(new \Codeception\Step\ConditionalAssertion('seeInThisFile', func_get_args()));
    }
    
    public function seeInThisFile($text) {
        return $this->scenario->runStep(new \Codeception\Step\Assertion('seeInThisFile', func_get_args()));
    }

 
    
    public function canSeeFileContentsEqual($text) {
        return $this->scenario->runStep(new \Codeception\Step\ConditionalAssertion('seeFileContentsEqual', func_get_args()));
    }
    
    public function seeFileContentsEqual($text) {
        return $this->scenario->runStep(new \Codeception\Step\Assertion('seeFileContentsEqual', func_get_args()));
    }

 
    
    public function cantSeeInThisFile($text) {
        return $this->scenario->runStep(new \Codeception\Step\ConditionalAssertion('dontSeeInThisFile', func_get_args()));
    }
    
    public function dontSeeInThisFile($text) {
        return $this->scenario->runStep(new \Codeception\Step\Assertion('dontSeeInThisFile', func_get_args()));
    }

 
    
    public function deleteThisFile() {
        return $this->scenario->runStep(new \Codeception\Step\Action('deleteThisFile', func_get_args()));
    }

 
    
    public function canSeeFileFound($filename, $path = null) {
        return $this->scenario->runStep(new \Codeception\Step\ConditionalAssertion('seeFileFound', func_get_args()));
    }
    
    public function seeFileFound($filename, $path = null) {
        return $this->scenario->runStep(new \Codeception\Step\Assertion('seeFileFound', func_get_args()));
    }

 
    
    public function cantSeeFileFound($filename, $path = null) {
        return $this->scenario->runStep(new \Codeception\Step\ConditionalAssertion('dontSeeFileFound', func_get_args()));
    }
    
    public function dontSeeFileFound($filename, $path = null) {
        return $this->scenario->runStep(new \Codeception\Step\Assertion('dontSeeFileFound', func_get_args()));
    }

 
    
    public function cleanDir($dirname) {
        return $this->scenario->runStep(new \Codeception\Step\Action('cleanDir', func_get_args()));
    }

 
    
    public function writeToFile($filename, $contents) {
        return $this->scenario->runStep(new \Codeception\Step\Action('writeToFile', func_get_args()));
    }

 
    
    public function canSeeEquals($expected, $actual) {
        return $this->scenario->runStep(new \Codeception\Step\ConditionalAssertion('seeEquals', func_get_args()));
    }
    
    public function seeEquals($expected, $actual) {
        return $this->scenario->runStep(new \Codeception\Step\Assertion('seeEquals', func_get_args()));
    }
}
