<?php

namespace CaptainHook\App\Integration;

class HookPluginTest extends IntegrationTestCase
{
    public function testHookPluginRunsBeforeAndAfterHookAndActions(): void
    {
        $repoPath = $this->setUpRepository();

        $this->setConfig($repoPath, 'plugins', [
            [
                'plugin' => '\\CaptainHook\\App\\Plugin\\Hook\\SimplePlugin',
                'options' => [
                    'stuff' => 'cool things',
                ],
            ],
        ]);

        $this->enableHook($repoPath, 'pre-commit', [
            [
                'action' => 'echo "action1"',
            ],
            [
                'action' => 'echo "action2"',
            ],
        ]);

        $this->enableHook($repoPath, 'post-commit');

        $this->filesystem()->touch($repoPath . '/foo.txt');
        $this->mustRunInShell(['git', 'add', 'foo.txt'], $repoPath);

        $result = $this->runInShell(['git', 'commit', '-m', 'Add foo.txt'], $repoPath);

        $this->assertStringContainsString('Do cool things before pre-commit runs', $result->getStderr());
        $this->assertStringContainsString('Do cool things before action echo "action1" runs', $result->getStderr());
        $this->assertStringContainsString('Do cool things after action echo "action1" runs', $result->getStderr());
        $this->assertStringContainsString('Do cool things before action echo "action2" runs', $result->getStderr());
        $this->assertStringContainsString('Do cool things after action echo "action2" runs', $result->getStderr());
        $this->assertStringContainsString('Do cool things after pre-commit runs', $result->getStderr());

        $this->assertStringContainsString('Do cool things before post-commit runs', $result->getStderr());
        $this->assertStringContainsString('Do cool things after post-commit runs', $result->getStderr());
    }
}
