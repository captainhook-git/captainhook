--TEST--
captainhook pre-push
--FILE--
<?php
define('CH_TEST_BIN', $_SERVER['CH_TEST_BIN'] ?? 'bin/captainhook');
echo shell_exec(
    // use some pre-push stdin
    'cat tests/_files/e2e/config-ok.json | ' .
    // run the pre-push hook
    CH_TEST_BIN . ' hook:pre-push --no-ansi --configuration=tests/_files/e2e/config-ok.json'
);
--EXPECTF--
pre-push: 
 - echo foo                                                          : done
captainhook executed all actions successfully, took: %ss
