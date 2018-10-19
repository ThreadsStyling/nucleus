<?php
/**
 * Created by PhpStorm.
 * User: dave.cridland
 * Date: 17/10/2018
 * Time: 09:12
 */

namespace Kadet\Xmpp;


use Kadet\Xmpp\Component\Authenticator;
use Kadet\Xmpp\Component\Handshake;
use Kadet\Xmpp\Network\Connector;
use Kadet\Xmpp\Stream\Features;

class XmppComponent extends XmppClientBase
{
    function __construct(Jid $jid, string $server, int $port, array $options = [])
    {
        $options =  array_merge([
            'default-modules' => false,
            'connector' => $options['connector'] ?? new Connector\TcpXmppConnector($server, $options['loop'], false, $port),
            'modules' => [
                Authenticator::class => new Handshake(),
            ]
        ]);

        parent::__construct($jid, $options);
        $this->on("stream.open", function ($stream) {
            $handshake = $this->getContainer()->get(Authenticator::class);
            $handshake->doHandshake($stream->getAttribute('id'));
        });
    }

    public function start(array $attributes = [])
    {
        parent::start(array_merge([
            'from' => $this->getJid(),
            'to' => $this->getJid(),
            'xmlns' => 'jabber:component:accept',
        ], $attributes));
    }
}