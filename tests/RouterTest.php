<?php

if (file_exists(__DIR__ . '/vendor/autoload.php') ) {
    include_once __DIR__ . '../vendor/autoload.php';
}

use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    protected \DWPL\RequestRouter\Router $R;

    public function setUp(): void
    {
        parent::setUp();
        $this->R = new \DWPL\RequestRouter\Router();
    }

    public function testDefaultRouteEmpty(): void {
        $this->R->handle(["a"]);

        $this->assertNull(
            null,
            "handle() call with 'null' default handler should not cause routing errors"
        );
    }

    public function testDefaultRouteSet() : void {
        $path_to_handle = ["b"];

        $DefaultHandler = $this->createMock(\DWPL\RequestRouter\Handler::class);
        $DefaultHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(
                function(array $path) use ($path_to_handle) : void {
                    $this->assertEquals(
                        $path_to_handle,
                        $path,
                        "default handler was called with wrong path value");
                }
            );

        $this->R->setDefaultHandler($DefaultHandler);
        $this->R->handle($path_to_handle);
    }

    public function testCustomHandler_SubpathsHandled() : void {
        $path_in_handler = ["v", "g", "e"];
        $path_to_call = array_merge(["c"], $path_in_handler);

        $DefaultHandler = $this->createMock(\DWPL\RequestRouter\Handler::class);
        $DefaultHandler->expects($this->never())->method('handle');

        $CustomHandler = $this->createMock(\DWPL\RequestRouter\Handler::class);
        $CustomHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(
                function(array $path) use ($path_in_handler) : void {
                    $this->assertEquals($path_in_handler, $path);
                }
            );

        $this->R->setDefaultHandler($DefaultHandler);
        $this->R->addRoute(["c"], $CustomHandler);

        $this->R->handle($path_to_call);
    }

    public function testCustomHandler_CorrectPathPassed() : void {
        $CustomHandler = $this->createMock(\DWPL\RequestRouter\Handler::class);
        $CustomHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(
                function(array $path) : void {
                    $this->assertEquals([], $path);
                }
            );

        $path_to_call = ["v", "g", "e"];
        $this->R->addRoute($path_to_call, $CustomHandler);
        $this->R->handle($path_to_call);
    }

    public function testEmbededRourers() : void {
        $CustomHandler = $this->createMock(\DWPL\RequestRouter\Handler::class);
        $CustomHandler
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(
                function(array $path) : void {
                    $this->assertEquals(["c"], $path);
                }
            );

        $R2 = new \DWPL\RequestRouter\Router();
        $R2->addRoute(["b"], $CustomHandler);

        $this->R->addRoute(["a"], $R2);
        $this->R->handle(["a", "b", "c"]);
    }

    public function uriSplitDataProvider() : array
    {
        return [
            "empty" => ["", ['']],
            "root" => ["/", ['']],
            "no_subs" => ['/a', ['a']],
            "dir" => ['/a/', ['a', '']],
            "subs" => ['/b/c/f', ['b', 'c', 'f']],
            "no_/_prefix" => ['v/r', ['v', 'r']],
        ];
    }

    /** @dataProvider uriSplitDataProvider */
    public function testURISplit(string $uri, array $expected_result) {
        $result = \DWPL\RequestRouter\Router::splitURI($uri);
        $this->assertEquals($expected_result, $result);
    }
}
