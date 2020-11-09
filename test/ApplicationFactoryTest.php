<?php

declare(strict_types=1);

namespace AlexTartanTest\Mezzio\SymfonyConsole;

use AlexTartan\Mezzio\SymfonyConsole\ApplicationFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console as SymfonyConsole;

/**
 * @covers \AlexTartan\Mezzio\SymfonyConsole\ApplicationFactory
 */
class ApplicationFactoryTest extends TestCase
{
    private Prophet $prophet;

    protected function setUp(): void
    {
        $this->prophet = new Prophet();
    }

    protected function tearDown(): void
    {
        $this->prophet->checkPredictions();
    }

    public function testFactory01(): void
    {
        $config = [];

        /** @var ContainerInterface|ObjectProphecy $container */
        $container = $this->prophet->prophesize(ContainerInterface::class);
        $container->get('config')->willReturn($config);

        $applicationFactory = new ApplicationFactory();
        $application        = $applicationFactory($container->reveal());

        static::assertSame('Application Console', $application->getName());
        static::assertSame('UNKNOWN', $application->getVersion());
    }

    public function testFactory02(): void
    {
        $config = [
            'mezzio-symfony-console' => [
                'name'     => 'dummy application name',
                'version'  => '1.2.3',
                'commands' => [
                    'dummy-command',
                ],
            ],
        ];

        $dummyCommand = new SymfonyConsole\Command\Command('dummy-command');

        /** @var ContainerInterface|ObjectProphecy $container */
        $container = $this->prophet->prophesize(ContainerInterface::class);
        $container->get('config')->willReturn($config);
        $container->get('dummy-command')->willReturn($dummyCommand);
        $container->has('dummy-command')->willReturn(true);

        $applicationFactory = new ApplicationFactory();
        $application        = $applicationFactory($container->reveal());

        static::assertSame('dummy application name', $application->getName());
        static::assertSame('1.2.3', $application->getVersion());
        static::assertSame($dummyCommand, $application->get('dummy-command'));
    }

    public function testFactory03(): void
    {
        $config = [
            'mezzio-symfony-console' => [
                'name'     => 'dummy application name',
                'version'  => '1.2.3',
                'commands' => [
                    'dummy:command-name' => 'dummy-command',
                ],
            ],
        ];

        $dummyCommand = new SymfonyConsole\Command\Command('command-name');

        /** @var ContainerInterface|ObjectProphecy $container */
        $container = $this->prophet->prophesize(ContainerInterface::class);
        $container->get('config')->willReturn($config);
        $container->has('dummy-command')->willReturn(true);
        $container->get('dummy-command')->willReturn($dummyCommand);

        $applicationFactory = new ApplicationFactory();
        $application        = $applicationFactory($container->reveal());

        static::assertSame('dummy application name', $application->getName());
        static::assertSame('1.2.3', $application->getVersion());
        static::assertSame($dummyCommand, $application->get('dummy:command-name'));
        static::assertSame('dummy:command-name', $application->get('dummy:command-name')->getName());
    }
}
