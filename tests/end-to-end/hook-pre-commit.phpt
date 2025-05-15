--TEST--
captainhook pre-commit
--FILE--
<?php
define('CH_TEST_BIN', $_SERVER['CH_TEST_BIN'] ?? 'bin/captainhook');
echo shell_exec(CH_TEST_BIN . ' hook:pre-commit --no-ansi --configuration=tests/_files/e2e/config-ok.json');
--EXPECTF--
pre-commit: 
 - echo foo                                                          : done
captainhook executed all actions successfully, took: %ss
