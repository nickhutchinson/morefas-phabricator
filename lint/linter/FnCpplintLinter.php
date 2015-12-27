<?php

/**
 * Uses Google's `cpplint` to check code.
 */
final class FnCpplintLinter extends ArcanistExternalLinter {

  public function getInfoName() {
    return 'CppLint';
  }

  public function getInfoURI() {
    return 'https://github.com/google/styleguide/tree/gh-pages/cpplint';
  }

  public function getInfoDescription() {
    return pht('Google\'s linter for C++ code');
  }

  public function getLinterName() {
    return 'CPPLINT';
  }

  public function getLinterConfigurationName() {
    return 'fn-cpplint';
  }

  public function getDefaultBinary() {
    return 'cpplint';
  }

  public function getInstallInstructions() {
    return pht('`wget https://raw.githubusercontent.com/google/styleguide/gh-pages/cpplint/cpplint.py`');
  }

  protected function getDefaultMessageSeverity($code) {
    return ArcanistLintSeverity::SEVERITY_WARNING;
  }

  protected function parseLinterOutput($path, $err, $stdout, $stderr) {
    $lines = explode("\n", $stderr);

    $messages = array();
    foreach ($lines as $line) {
      $line = trim($line);
      $matches = null;
      $regex = '/^.+?:(\d+):\s*(.*)\s*\[(.*)\] \[(\d+)\]$/';
      if (!preg_match($regex, $line, $matches)) {
        continue;
      }
      foreach ($matches as $key => $match) {
        $matches[$key] = trim($match);
      }

      $severity = $this->getLintMessageSeverity($matches[3]);

      $message = new ArcanistLintMessage();
      $message->setPath($path);
      $message->setLine($matches[1]);
      $message->setCode($matches[3]);
      $message->setName($matches[3]);
      $message->setDescription($matches[2]);
      $message->setSeverity($severity);

      $messages[] = $message;
    }

    return $messages;
  }

  protected function getLintCodeFromLinterConfigurationKey($code) {
    if (!preg_match('@^[a-z_]+/[a-z_]+$@', $code)) {
      throw new Exception(
        pht(
          'Unrecognized lint message code "%s". Expected a valid cpplint '.
          'lint code like "%s" or "%s".',
          $code,
          'build/include_order',
          'whitespace/braces'));
    }

    return $code;
  }

}
