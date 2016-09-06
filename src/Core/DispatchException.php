<?php
namespace Toda\Core;

use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\User\Plugin;

class DispatchException extends Plugin
{
    public function beforeException(Event $event, Dispatcher $dispatcher, $exception)
    {

      
        switch ($exception->getCode()) {
            case 404:
            case Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
            case Dispatcher::EXCEPTION_ACTION_NOT_FOUND:

                $dispatcher->forward(
                    array(
                        'controller' => 'error',
                        'action' => 'show404'
                    )
                );

                return false;
            case 403:
                $dispatcher->forward(
                    array(
                        'controller' => 'error',
                        'action' => 'show403',
                        'params' => ['message' => $exception->getMessage()]
                    )
                );
                return false;
        }
    }
}