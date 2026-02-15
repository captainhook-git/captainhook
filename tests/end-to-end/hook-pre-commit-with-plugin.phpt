--TEST--
captainhook pre-commit
--FILE--
<?php
define('CH_TEST_BIN', $_SERVER['CH_TEST_BIN'] ?? 'bin/captainhook');
echo shell_exec(CH_TEST_BIN . ' hook:pre-commit --no-ansi --configuration=captainhook.json.sample');
--EXPECTF--
pre-commit:%s
%S
%S
%S
captainhook executed all actions successfully, took: %ss
