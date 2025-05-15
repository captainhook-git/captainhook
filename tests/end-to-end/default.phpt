--TEST--
captainhook
--FILE--
<?php
define('CH_TEST_BIN', $_SERVER['CH_TEST_BIN'] ?? 'bin/captainhook');
echo shell_exec(CH_TEST_BIN . ' --no-ansi');
--EXPECTF_EXTERNAL--
../_files/e2e/help.txt
