<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 25/09/2018
 * Time: 20:37
 */

namespace maesierra\Japo\App\Controller;


use maesierra\Japo\Auth\User;
use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;



class DefaultControllerTest extends TestCase {

    /** @var  MockObject */
    private $request;
    /** @var  MockObject */
    private $response;

    /**
     * @var MockObject
     */
    private $logger;

    /**
     * @var MockObject
     */
    private $body;

    /**
     * @var DefaultController
     */
    private $controller;

    public function setUp():void {
        /** @var Logger $logger */
        $logger = $this->createMock(Logger::class);
        $this->logger = $logger;
        $this->controller = new DefaultController(null, $logger);
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->body = $this->createMock(StreamInterface::class);
        $this->response->method('getBody')->willReturn($this->body);
    }

    public function testDefaultAction() {
        $user = new User([
            'id' => 0,
            'nickname' => 'user',
            'email' => 'none@user.com',
            'role' => User::USER_ROLE_ADMIN
        ]);
        $this->request->method('getAttribute')->with('user')->willReturn($user);
        $this->response->expects($this->once())->method('withHeader')->with('Content-type', 'application/json')->willReturnSelf();
        $this->body->expects($this->once())->method('write')->with(json_encode($user));

        /** @var ServerRequestInterface $request */
        $request = $this->request;
        /** @var ResponseInterface $response */
        $response = $this->response;
        $this->controller->defaultAction($request, $response, []);
    }
}