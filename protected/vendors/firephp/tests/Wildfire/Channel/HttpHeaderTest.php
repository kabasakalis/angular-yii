<?php

require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'TestHelper.php';
require_once 'PHPUnit/Framework.php';

require_once 'Wildfire/Message.php';
require_once 'Wildfire/Dispatcher.php';
require_once 'Wildfire/Channel/HttpHeader.php';
 
class Wildfire_MessageTest extends PHPUnit_Framework_TestCase
{
    
    public function testSmall()
    {
        $channel = new Wildfire_MessageTest__Wildfire_Channel_HttpHeader();

        $dispatcher = new Wildfire_Dispatcher();
        $dispatcher->setChannel($channel);
        
        $message = new Wildfire_Message();
        $message->setData('Hello World');
        $message->setMeta('{"line":10}');
        $message->setProtocol('http://registry.pinf.org/cadorn.org/wildfire/@meta/protocol/component/0.1.0');
        $message->setSender('http://pinf.org/cadorn.org/wildfire/packages/lib-php');
        $message->setReceiver('http://pinf.org/cadorn.org/fireconsole');        
        
        $dispatcher->dispatch($message);
        $dispatcher->dispatch($message);
        
        $channel->flush();

        $this->assertEquals(
            array(
                'x-wf-protocol-1' => 'http://registry.pinf.org/cadorn.org/wildfire/@meta/protocol/component/0.1.0',
                'x-wf-1-index' => '2',
                'x-wf-1-1-receiver' => 'http://pinf.org/cadorn.org/fireconsole',
                'x-wf-1-1-1-sender' => 'http://pinf.org/cadorn.org/wildfire/packages/lib-php',
                'x-wf-1-1-1-1' => '23|{"line":10}|Hello World|',
                'x-wf-1-1-1-2' => '23|{"line":10}|Hello World|'
            ),
            $channel->getMessageParts()
        );
    }

    public function testLarge()
    {
        $channel = new Wildfire_MessageTest__Wildfire_Channel_HttpHeader();
        $channel->setMessagePartMaxLength(10);

        $dispatcher = new Wildfire_Dispatcher();
        $dispatcher->setChannel($channel);
        $dispatcher->setProtocol('http://registry.pinf.org/cadorn.org/wildfire/@meta/protocol/component/0.1.0');
        $dispatcher->setSender('http://pinf.org/cadorn.org/wildfire/packages/lib-php');
        $dispatcher->setReceiver('http://pinf.org/cadorn.org/fireconsole');        
        
        $message = new Wildfire_Message();

        $data = array();
        for( $i=0 ; $i<3 ; $i++ ) {
            $data[] = 'line ' . $i;
        }
        $message->setData(implode($data, "; "));
        
        $dispatcher->dispatch($message);
        $dispatcher->dispatch($message);

        $channel->flush();

        $this->assertEquals(
            array(
                'x-wf-protocol-1' => 'http://registry.pinf.org/cadorn.org/wildfire/@meta/protocol/component/0.1.0',
                'x-wf-1-index' => '6',
                'x-wf-1-1-receiver' => 'http://pinf.org/cadorn.org/fireconsole',
                'x-wf-1-1-1-sender' => 'http://pinf.org/cadorn.org/wildfire/packages/lib-php',
                'x-wf-1-1-1-1' => '23||line 0; l|\\',
                'x-wf-1-1-1-2' => '|ine 1; lin|\\',
                'x-wf-1-1-1-3' => '|e 2|',
                'x-wf-1-1-1-4' => '23||line 0; l|\\',
                'x-wf-1-1-1-5' => '|ine 1; lin|\\',
                'x-wf-1-1-1-6' => '|e 2|'
            ),
            $channel->getMessageParts()
        );
    }

    public function testMultipleProtocols()
    {
        $channel = new Wildfire_MessageTest__Wildfire_Channel_HttpHeader();

        $dispatcher = new Wildfire_Dispatcher();
        $dispatcher->setChannel($channel);
        
        $message = new Wildfire_Message();
        $message->setData('Hello World');
        $message->setMeta('{"line":10}');
        $message->setProtocol('http://registry.pinf.org/cadorn.org/wildfire/@meta/protocol/component/0.1.0');
        $message->setSender('http://pinf.org/cadorn.org/wildfire/packages/lib-php');
        $message->setReceiver('http://pinf.org/cadorn.org/fireconsole');        
        
        $dispatcher->dispatch($message);

        $message->setProtocol('__TEST__');

        $dispatcher->dispatch($message);
        
        $channel->flush();

        $this->assertEquals(
            array(
                'x-wf-protocol-1' => 'http://registry.pinf.org/cadorn.org/wildfire/@meta/protocol/component/0.1.0',
                'x-wf-1-index' => '1',
                'x-wf-1-1-receiver' => 'http://pinf.org/cadorn.org/fireconsole',
                'x-wf-1-1-1-sender' => 'http://pinf.org/cadorn.org/wildfire/packages/lib-php',
                'x-wf-1-1-1-1' => '23|{"line":10}|Hello World|',
                'x-wf-protocol-2' => '__TEST__',
                'x-wf-2-index' => '1',
                'x-wf-2-1-receiver' => 'http://pinf.org/cadorn.org/fireconsole',
                'x-wf-2-1-1-sender' => 'http://pinf.org/cadorn.org/wildfire/packages/lib-php',
                'x-wf-2-1-1-1' => '23|{"line":10}|Hello World|'
            ),
            $channel->getMessageParts()
        );
    }

    public function testMultipleSenders()
    {
        $channel = new Wildfire_MessageTest__Wildfire_Channel_HttpHeader();

        $dispatcher = new Wildfire_Dispatcher();
        $dispatcher->setChannel($channel);
        
        $message = new Wildfire_Message();
        $message->setData('Hello World');
        $message->setMeta('{"line":10}');
        $message->setProtocol('http://registry.pinf.org/cadorn.org/wildfire/@meta/protocol/component/0.1.0');
        $message->setSender('http://pinf.org/cadorn.org/wildfire/packages/lib-php');
        $message->setReceiver('http://pinf.org/cadorn.org/fireconsole');        
        
        $dispatcher->dispatch($message);

        $message->setSender('__TEST__');

        $dispatcher->dispatch($message);
        
        $channel->flush();

        $this->assertEquals(
            array(
                'x-wf-protocol-1' => 'http://registry.pinf.org/cadorn.org/wildfire/@meta/protocol/component/0.1.0',
                'x-wf-1-index' => '2',
                'x-wf-1-1-receiver' => 'http://pinf.org/cadorn.org/fireconsole',
                'x-wf-1-1-1-sender' => 'http://pinf.org/cadorn.org/wildfire/packages/lib-php',
                'x-wf-1-1-1-1' => '23|{"line":10}|Hello World|',
                'x-wf-1-1-2-sender' => '__TEST__',
                'x-wf-1-1-2-2' => '23|{"line":10}|Hello World|'
            ),
            $channel->getMessageParts()
        );
    }    

    public function testMultipleReceivers()
    {
        $channel = new Wildfire_MessageTest__Wildfire_Channel_HttpHeader();

        $dispatcher = new Wildfire_Dispatcher();
        $dispatcher->setChannel($channel);
        
        $message = new Wildfire_Message();
        $message->setData('Hello World');
        $message->setMeta('{"line":10}');
        $message->setProtocol('http://registry.pinf.org/cadorn.org/wildfire/@meta/protocol/component/0.1.0');
        $message->setSender('http://pinf.org/cadorn.org/wildfire/packages/lib-php');
        $message->setReceiver('http://pinf.org/cadorn.org/fireconsole');        
        
        $dispatcher->dispatch($message);

        $message->setReceiver('__TEST__');

        $dispatcher->dispatch($message);
        
        $channel->flush();

        $this->assertEquals(
            array(
                'x-wf-protocol-1' => 'http://registry.pinf.org/cadorn.org/wildfire/@meta/protocol/component/0.1.0',
                'x-wf-1-index' => '2',
                'x-wf-1-1-receiver' => 'http://pinf.org/cadorn.org/fireconsole',
                'x-wf-1-1-1-sender' => 'http://pinf.org/cadorn.org/wildfire/packages/lib-php',
                'x-wf-1-1-1-1' => '23|{"line":10}|Hello World|',
                'x-wf-1-2-receiver' => '__TEST__',
                'x-wf-1-2-1-sender' => 'http://pinf.org/cadorn.org/wildfire/packages/lib-php',
                'x-wf-1-2-1-2' => '23|{"line":10}|Hello World|'
            ),
            $channel->getMessageParts()
        );
    }    
}


class Wildfire_MessageTest__Wildfire_Channel_HttpHeader extends Wildfire_Channel_HttpHeader
{
    var $parts = array();

    public function getMessageParts()
    {
        return $this->parts;
    }
    
    public function setMessagePart($key, $value)
    {
        $this->parts[$key] = '' . $value;
    }
    
    public function getMessagePart($key)
    {
        if(isset($this->parts[$key])) return $this->parts[$key];
        return false;
    }
        
}
