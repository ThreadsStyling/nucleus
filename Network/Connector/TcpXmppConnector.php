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

namespace Kadet\Xmpp\Network\Connector;

use Kadet\Xmpp\Network\Connector;
use Kadet\Xmpp\Network\TcpStream;
use Kadet\Xmpp\Utils\BetterEmitter;
use Kadet\Xmpp\Utils\DnsResolver;
use Kadet\Xmpp\Utils\Logging;
use React\EventLoop\LoopInterface;
use React\Stream\DuplexStreamInterface;

class TcpXmppConnector implements Connector
{
    use Logging, BetterEmitter;

    private $_host;
    /** @var DnsResolver */
    private $_resolver;
    private $_loop;

    public function connect(array $options = []) : DuplexStreamInterface
    {
        foreach ($this->_resolver as list($ip, $port)) {
            $this->getLogger()->debug('Trying to connect to {ip}:{port}', [
                'ip'   => $ip,
                'port' => $port
            ]);

            if ($stream = @stream_socket_client("tcp://$ip:$port")) {
                stream_context_set_option($stream, 'ssl', 'peer_name', $this->_host);

                $stream = new TcpStream($stream, $this->_loop);
                $this->emit('connect', [ $stream ]);

                return $stream;
            }
        }

        throw new \RuntimeException('Cannot connect to '.$this->_host);
    }

    public function __construct(string $host, LoopInterface $loop, bool $use_srv = true, int $port = null)
    {
        if ($port == null) {
            $port = 5222;
        }
        $records = [$host => DNS_A];
        if ($use_srv) {
            array_merge([
                "_xmpp-client._tcp.$host" => DNS_SRV,
            ]);
        }
        $this->_resolver = new DnsResolver($records, $port);

        $this->_host = $host;
        $this->_loop = $loop;
    }

    public function getLoop() : LoopInterface
    {
        return $this->_loop;
    }
}
