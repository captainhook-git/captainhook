--TEST--
captainhook commit-msg
--FILE--
<?php
define('CH_TEST_BIN', $_SERVER['CH_TEST_BIN'] ?? 'bin/captainhook');
echo shell_exec(
    // call command without color
    CH_TEST_BIN . ' info --list-actions --no-ansi ' .
    // use e2e configuration
    '--configuration=tests/_files/e2e/config-ok.json'
);
--EXPECTF--
Hooks:
  commit-msg
   - \CaptainHook\App\Hook\Message\Action\Beams
  pre-push
   - echo foo
  pre-commit
   - echo foo
  prepare-commit-msg
  post-commit
  post-merge
  post-checkout
  post-rewrite
  post-change
