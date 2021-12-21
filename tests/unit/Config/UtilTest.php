<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Config;

use CaptainHook\App\Config;
use Exception;
use PHPUnit\Framework\TestCase;

class UtilTest extends TestCase
{
    /**
     * Tests Util::validateJsonConfiguration
     */
    public function testEnabledMissing(): void
    {
        $this->expectException(Exception::class);

        Util::validateJsonConfiguration(['pre-commit' => ['actions' => []]]);
    }

    /**
     * Tests Util::validateJsonConfiguration
     */
    public function testActionsMissing(): void
    {
        $this->expectException(Exception::class);

        Util::validateJsonConfiguration(['pre-commit' => ['enabled' => true]]);
    }

    /**
     * Tests Util::validateJsonConfiguration
     */
    public function testActionsNoArray(): void
    {
        $this->expectException(Exception::class);

        Util::validateJsonConfiguration(['pre-commit' => ['enabled' => true, 'actions' => false]]);
    }

    /**
     * Tests Util::validateJsonConfiguration
     */
    public function testActionMissing(): void
    {
        $this->expectException(Exception::class);

        Util::validateJsonConfiguration(
            [
                'pre-commit' => [
                    'enabled' => true,
                    'actions' => [
                        ['options' => []]
                    ]
                ]
            ]
        );

        $this->assertTrue(true);
    }

    /**
     * Tests Util::validateJsonConfiguration
     */
    public function testActionEmpty(): void
    {
        $this->expectException(Exception::class);

        Util::validateJsonConfiguration(
            [
                'pre-commit' => [
                    'enabled' => true,
                    'actions' => [
                        ['action'  => '']
                    ]
                ]
            ]
        );
    }

    /**
     * Tests Util::validateJsonConfiguration
     */
    public function testConditionExecMissing(): void
    {
        $this->expectException(Exception::class);

        Util::validateJsonConfiguration(
            [
                'pre-commit' => [
                    'enabled' => true,
                    'actions' => [
                        [
                            'action'    => 'foo',
                            'conditions' => [
                                [
                                    'args' => [
                                        'foo' => 'fiz',
                                        'bar' => 'baz'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        );
    }

    /**
     * Tests Util::validateJsonConfiguration
     */
    public function testConditionArgsNoArray(): void
    {
        $this->expectException(Exception::class);

        Util::validateJsonConfiguration(
            [
                'pre-commit' => [
                    'enabled' => true,
                    'actions' => [
                        [
                            'action'    => 'foo',
                            'conditions' => [
                                [
                                    'exec' => '\\Foo',
                                    'args' => 'fooBarBaz'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        );
    }


    /**
     * Tests Util::validateJsonConfiguration
     */
    public function testValid(): void
    {
        Util::validateJsonConfiguration(
            [
                'pre-commit' => [
                    'enabled' => true,
                    'actions' => [
                        ['action'  => 'foo']
                    ]
                ]
            ]
        );

        $this->assertTrue(true);
    }

    /**
     * Tests Util::validateJsonConfiguration
     */
    public function testValidWithCondition(): void
    {
        Util::validateJsonConfiguration(
            [
                'config' => [
                    'plugins' => [
                        [
                            'plugin' => '\\Foo\\Bar',
                        ]
                    ],
                ],
                'pre-commit' => [
                    'enabled' => true,
                    'actions' => [
                        [
                            'action'    => 'foo',
                            'conditions' => [
                                [
                                    'exec' => '\\Fiz\\Baz',
                                    'args' => [
                                        'foo' => 'fiz',
                                        'bar' => 'baz'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        );

        $this->assertTrue(true);
    }

    public function testPluginsMustBeAnArray(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Config error: \'plugins\' must be an array');

        Util::validateJsonConfiguration([
            'config' => [
                'plugins' => 'foobar',
            ],
        ]);
    }

    public function testPluginMissing(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Config error: \'plugin\' missing');

        Util::validateJsonConfiguration([
            'config' => [
                'plugins' => [
                    [
                        'foo' => 'bar',
                    ],
                ],
            ],
        ]);
    }

    public function testPluginEmpty(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Config error: \'plugin\' can\'t be empty');

        Util::validateJsonConfiguration([
            'config' => [
                'plugins' => [
                    [
                        'plugin' => '',
                    ],
                ],
            ],
        ]);
    }

    public function testMergeSettings()
    {
        $s1 = [
            Config::SETTING_INCLUDES => [
                'foo',
                'bar'
            ],
            CONFIG::SETTING_COLORS => true,
        ];
        $s2 = [
            Config::SETTING_INCLUDES => [
                'baz'
            ],
            CONFIG::SETTING_GIT_DIR => '/var/.git'
        ];

        $merged = Util::mergeSettings($s2, $s1);

        $this->assertEquals(3, count($merged[Config::SETTING_INCLUDES]));
        $this->assertContains('baz', $merged[Config::SETTING_INCLUDES]);
    }
}
