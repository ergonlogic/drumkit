<?php
namespace Drumkit;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;


/**
 * Defines application features from the specific context.
 */
class DrumkitContext extends RawDrupalContext implements SnippetAcceptingContext {

  protected $debug = FALSE;

  protected $ignoreFailures = FALSE;

  private $process;

  private $tempDir;

  private $orig_dir;


  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {
    $this->setOrigDir();
  }

  /**
   * Clean up temporary directories.
   */
  public function __destruct() {
    $this->rmdir($this->tempDir);
  }

  private function getOrigDir() {
    return $this->orig_dir;
  }

  private function setOrigDir() {
    if (!isset($this->orig_dir)) {
      $this->orig_dir = getcwd();
    }
  }

  protected function getOutput() {
    return $this->process->getOutPut();
  }

  /**
   * Run a command in a sub-process, and set its output.
   */
  private function exec($command) {
    $this->process = new Process("{$command}");
    $this->process->setTimeout(300);
    $this->process->run();

    if ($this->debug) {
      print_r("--- DEBUG START ---\n");
      print_r($this->getOutput());
      print_r("\n--- DEBUG END -----");
    }
  }

  private function succeed($command) {
    $this->exec($command);
    if (!$this->process->isSuccessful()) {
      throw new ProcessFailedException($this->process);
    }
  }

  /**
   * Run a command that is expected to fail in a sub-process, and set its output.
   */
  private function fail($command) {
    $this->exec($command);

    if ($this->process->isSuccessful()) {
      throw new \RuntimeException($this->process->getOutput());
    }
  }

  /**
   * Create a temporary directory
   */
  private function makeTempDir() {
    $tempfile = tempnam(sys_get_temp_dir(), 'behat_cli_');
    if (file_exists($tempfile)) {
      unlink($tempfile);
    }
    mkdir($tempfile);
    $this->tempDir = $tempfile;
  }

  /**
   * Recursively delete a directory and its contents.
   */
  private function rmdir($dir) {
    $this->iRun('rm -rf ' . $dir);
  }

  /**
   * Set a debug flag when running scenarios tagged @debug.
   *
   * @BeforeScenario @debug
   */
  public function setDebugFlag() {
    $this->debug = TRUE;
  }

  /**
   * In case we switched to a temporary directory, switch back to the original
   * directory before the next scenario.
   *
   * @AfterScenario
   */
  public function returnToOrigDir() {
    chdir($this->getOrigDir());
  }

  /**
   * @When I run :command
   */
  public function iRun($command)
  {
    if ($this->ignoreFailures) {
      return $this->exec($command);
    } 
    $this->succeed($command);
  }

  /**
   * @When I run :cmd on :host
   */
  public function iRunOn($cmd, $host) {
    $this->iRun("ssh $host $cmd");
    if (!$this->process->isSuccessful()) {
      throw new ProcessFailedException($this->process);
    }
  }

  /**
   * @Given The :pkg deb package is installed on :host
   */
  public function theDebPackageIsInstalledOn($pkg, $host) {
    $this->ignoreFailures = TRUE;
    $this->iRunOn("dpkg -l $pkg", $host);
    if (!preg_match("/ii[ ]+$pkg/", $this->getOutput())) {
      throw new \Exception("'$pkg' is not installed, dpkg output was:\n" . $this->getOutput());
    }
  }

  /**
   * @Then I should get:
   */
  public function iShouldGet(PyStringNode $expectedOutput)
  {
    foreach ($expectedOutput->getStrings() as $string) {
      $string = trim($string);
      if (!empty($string) && strpos($this->getOutput(), $string) === FALSE) {
        throw new \Exception("'$string' was not found in command output:\n------\n" . $this->getOutput() . "\n------\n");
      }
    }
  }

  /**
   * @Then I should not get:
   */
  public function iShouldNotGet(PyStringNode $unexpectedOutput)
  {
    foreach ($unexpectedOutput->getStrings() as $string) {
      $string = trim($string);
      if (!empty($string) && strpos($this->getOutput(), $string) !== FALSE) {
        throw new \RuntimeException("'$string' was found in command output:\n------\n" . $this->getOutput() . "\n------\n");
      }
    }
  }

  /**
   * @Given I am in a temporary directory
   */
  public function iAmInATemporaryDirectory()
  {
    $this->makeTempDir();
    chdir($this->tempDir);
  }

  /**
   * Execute a script in our project, even if we've moved to a temporary directory.
   *
   * @When I execute :script
   */
  public function iExecute($script)
  {
    $script = $this->getOrigDir() . DIRECTORY_SEPARATOR . $script;
    $this->succeed($script);
  }

  /**
   * @Then executing :script should fail
   */
  public function executingShouldFail($script)
  {
    $script = $this->getOrigDir() . DIRECTORY_SEPARATOR . $script;
    $this->fail($script);
  }

  /**
   * @Then the following files should exist:
   */
  public function theFollowingFilesShouldExist(PyStringNode $files)
  {
     foreach ($files->getStrings() as $file) {
      if (!file_exists($file)) {
        throw new \RuntimeException("Expected file '$file' was not found.");
      }
    }
  }

  /**
   * @Then the following files should not exist:
   */
  public function theFollowingFilesShouldNotExist(PyStringNode $files)
  {
    foreach ($files->getStrings() as $file) {
      if (file_exists($file)) {
        throw new \RuntimeException("Unxpected file '$file' was found.");
      }
    }
  }

  /**
   * @Given I bootstrap drumkit
   */
  public function iBootstrapDrumkit()
  {
    $this->iAmInATemporaryDirectory();
    $this->iRun("cp -r " . $this->getOrigDir() ." ./.mk");
    $this->iRun("echo 'include .mk/GNUmakefile' > Makefile");
  }

  /**
   * @Given I bootstrap this code
   */
  public function iBootstrapThisCode()
  {
    $this->iAmInATemporaryDirectory();
    $this->iRun("bash -c 'shopt -s dotglob && cp -r " . $this->getOrigDir() . "/* .'");
  }

  /**
   * @Then the file :file should contain:
   */
  public function theFileShouldContain($file, PyStringNode $lines)
  {
    foreach ($lines->getStrings() as $line) {
      $contents = file_get_contents($file);
      if (strpos($contents, $line) === FALSE) {
        throw new \RuntimeException("'$line' was not found in '$file'.");
      }
    }
  }
}
