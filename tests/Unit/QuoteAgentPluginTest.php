<?php

declare(strict_types=1);

namespace QuoteAgent\Tests\Unit;

use PHPUnit\Framework\TestCase;
use QuoteAgent\QuoteAgent;
use Shopware\Core\Framework\Plugin;

final class QuoteAgentPluginTest extends TestCase
{
    public function testPluginClassExists(): void
    {
        $this->assertTrue(class_exists(QuoteAgent::class));
    }

    public function testPluginExtendsShopwarePlugin(): void
    {
        $reflection = new \ReflectionClass(QuoteAgent::class);

        $this->assertTrue($reflection->isSubclassOf(Plugin::class));
    }

    public function testPluginIsFinal(): void
    {
        $reflection = new \ReflectionClass(QuoteAgent::class);

        $this->assertTrue($reflection->isFinal());
    }
}
