<?php
/**
 * Created by PhpStorm.
 * User: dave.cridland
 * Date: 17/10/2018
 * Time: 09:12
 */

namespace Kadet\Xmpp;


use Kadet\Xmpp\Component\Roster;

class XmppClient extends XmppClientBase
{
    function __construct(Jid $jid, array $options = [])
    {
        $options =  array_merge($options, [
            'default-modules' => true,
        ]);

        parent::__construct($jid, $options);
    }

    /**
     * @return Roster
     */
    public function getRoster(): Roster
    {
        return $this->get(Roster::class);
    }

    public function start(array $attributes = [])
    {
        parent::start(array_merge([
            'xmlns' => 'jabber:client',
            'version' => '1.0',
        ], $attributes));
    }
}