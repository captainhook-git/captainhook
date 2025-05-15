--TEST--
captainhook commit-msg
--FILE--
<?php
define('CH_TEST_BIN', $_SERVER['CH_TEST_BIN'] ?? 'bin/captainhook');
echo shell_exec(
    // call command without color
    CH_TEST_BIN . ' hook:commit-msg --no-ansi ' .
    // use e2e configuration
    '--configuration=tests/_files/e2e/config-ok.json ' .
    // use e2e dummy commit message
    'tests/_files/e2e/commit-msg-ok.txt'
);
--EXPECTF--
commit-msg: 
 - Verify commit message format                                      : done
captainhook executed all actions successfully, took: %ss
