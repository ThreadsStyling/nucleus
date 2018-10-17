<?php
/**
 * Nucleus - XMPP Library for PHP
 *
 * Copyright (C) 2016, Some rights reserved.
 *
 * @author Kacper "Kadet" Donat <kacper@kadet.net>
 *
 * Contact with author:
 * Xmpp: me@kadet.net
 * E-mail: contact@kadet.net
 *
 * From Kadet with love.
 */

namespace Kadet\Xmpp\Tests\Modules;


use Kadet\Xmpp\Component\Handshake;
use Kadet\Xmpp\Jid;
use Kadet\Xmpp\Tests\Stubs\ConnectorStub;
use Kadet\Xmpp\Xml\XmlElement;
use Kadet\Xmpp\XmppClientBase;
use Kadet\Xmpp\XmppComponent;

/**
 * Class SaslAuthenticatorTest
 * @package Kadet\Xmpp\Tests
 *
 * @covers \Kadet\Xmpp\Component\SaslAuthenticator
 */
class HandshakeTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|XmppClientBase */
    private $_client;
    private $flag;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->_client = $this->getMockComponent();
    }

    public function testChallenge()
    {
        $id = substr(md5(rand()), 0, 10);
        $secret = substr(md5(rand()), 0, 16);
        $handshake = new Handshake();
        $handshake->setPassword($secret);
        $this->_client->register($handshake);
        $this->_client->expects($this->once())->method('write')->with($this->callback(function (XmlElement $element) use($id, $secret) {
            $this->assertEquals('handshake', $element->localName);
            $this->assertEquals(sha1($id . $secret), $element->innerXml);
            return true;
        }));
        $handshake->doHandshake($id);
        $this->flag = false;
        $this->_client->once('ready', function () {
            $this->flag = true;
        });
        $this->success();
        $this->assertTrue($this->flag);
    }

    private function success()
    {
        $proceed = new XmlElement('handshake', null);

        $this->_client->emit('element', [$proceed]);
    }

    /**
     * @return XmppClientBase|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockComponent()
    {
        return $this->getMockBuilder(XmppComponent::class)
            ->setConstructorArgs([new Jid('sub.domain.tld'), 'host.domain.tld', 5269, [
                'connector' => new ConnectorStub(),
                'default-modules' => false
            ]])->setMethods(['write'])
            ->getMock();
    }
}
