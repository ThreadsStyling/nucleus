<?php
/**
 * Created by PhpStorm.
 * User: dave.cridland
 * Date: 17/10/2018
 * Time: 10:21
 */

namespace Kadet\Xmpp\Component;


use Kadet\Xmpp\Stream\Features;
use Kadet\Xmpp\Xml\XmlElement;
use Kadet\Xmpp\XmppClientBase;


class Handshake extends Component implements Authenticator
{
    private $_password;

    public function setPassword(string $password = null)
    {
        $this->_password = $password;
    }

    public function setClient(XmppClientBase $client)
    {
        parent::setClient($client);
    }

    public function auth(Features $features)
    {
    }

    public function doHandshake(string $id)
    {
        $element = new XmlElement("handshake");
        $element->setContent(sha1($id . $this->_password, false));
        $this->_client->once("element", function (XmlElement $el) {
            if ($el->getLocalName() == "handshake") {
                $this->_client->emit("ready");
            }
        });
        $this->_client->write($element);
    }
}