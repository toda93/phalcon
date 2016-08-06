<?php
namespace Toda\Core;

use Phalcon\Mvc\Controller as ControllerRoot;
use Phalcon\Mvc\View;
use Toda\Client\HttpClient;
use Toda\Validation\ErrorMessage;
use Toda\Validation\OldValue;
use Toda\Validation\Validate;

class Controller extends ControllerRoot
{
    protected $errors = null;
    protected $old = null;
    protected $auth = null;

    public function initialize()
    {
        $this->checkCSRF();

        if ($this->session->has('errors')) {
            $this->errors = new ErrorMessage($this->session->get('errors'));
            $this->session->remove('errors');

            if ($this->session->has('old')) {
                $this->old = new OldValue($this->session->get('old'));
                $this->session->remove('old');
            }
        }

        if ($this->session->has('auth')) {
            $this->auth = $this->session->get('auth');
        }
        

        $this->response->setHeader("Content-Type", "text/html; charset=utf-8");
        $this->response->setHeader("Access-Control-Allow-Origin", "*");
        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);

        $this->view->setVars([
            'auth' => $this->auth,
            'errors' => $this->errors,
            'old' => $this->old
        ]);
    }

    protected function middleware($name, $params = [])
    {
        $action = $this->dispatcher->getActionName();

        $run = (preg_match('/' . $action . '/', reset($params)));

        if (($run && key($params) === 'only') || (!$run && key($params) !== 'only')) {
            $name = $name . 'Middleware';
            $controller = $this->dispatcher->getControllerName();
            
            $this->$name($controller, $action);
        }
    }

    protected function checkCSRF()
    {
        /*
         * 1: is default
         * 2: is request post method
         * 3: is csrf error
         */
        $flag = 1;
        if ($this->request->isPost() && $this->dispatcher->getControllerName() != 'errors') {
            $flag = ($this->session->get('token') != $this->cookies->get('token')) ? 3 : 2;
        }
        if (!$this->session->has('token') || !$this->cookies->has('token') || $flag > 1) {
            $token = uniqid();
            $this->session->set('token', $token);
            $this->cookies->set('token', $token);
        }

        if($flag == 3){
            return $this->dispatcher->forward([
                'controller' => 'errors',
                'action' => 'show403',
                'params' => ['message' => 'CSRF token not mismatch'],
            ]);
        }
    }

    protected function render($view = null, array $vars = [])
    {
        $this->view->setVars($vars);
        if (!is_null($view)) {
            $this->view->pick($view);
        }
        return true;
    }

    protected function back()
    {
        return $this->redirect($this->request->getHTTPReferer());
    }

    protected function redirect($to)
    {
        return $this->response->redirect($to)->send()->send();
    }

    protected function json($content)
    {
        return $this->response->setJsonContent($content)->send();
    }

    protected function errorMessages(array $messages)
    {
        $this->session->set('errors', $messages);
        if (!empty($messages)) {
            $this->session->set('old', $this->request->get());
        }
        return $this;
    }

    protected function validate(array $conditions, array $messages = [])
    {
        $valid = true;
        $error_messages = [];
        foreach ($conditions as $key => $condition) {

            $arr_condition = explode('|', $condition);
            foreach ($arr_condition as $item) {
                $params = explode(':', $item, 2);
                $method = $params[0];
                $param = empty($params[1]) ? '' : $params[1];

                $value = $this->request->get($key);
                $message = Validate::$method($key, $value, $param);
                if (!empty($message)) {
                    $valid = false;
                    $error_messages[$key] = empty($messages[$key]) ? $message : $messages[$key];
                    break;
                }
            }
        }
        if (!$valid) {
            return $this->errorMessages($error_messages)->back();
        }
    }

    protected function verifyCaptcha()
    {
        $captcha = '';
        if ($this->request->has('g-recaptcha-response')) {
            $captcha = $this->request->get('g-recaptcha-response');
        }
        $client = new HttpClient();

        $response = json_decode($client->init()
            ->get("https://www.google.com/recaptcha/api/siteverify?secret=" . page('recaptcha_secret') . "&response=" . $captcha . "&remoteip=" . $this->request->getClientAddress())
        );

        if (!$response->success) {
            return $this->errorMessages(['captcha' => 'captcha incorrect.'])->back();
        }
    }

    protected function getPage()
    {
        $page = $this->request->get('page');
        return $page < 1 || is_nan($page) ? 1 : $page;
    }

    protected function emptyTo404($data)
    {
        if (empty($data) || count($data) == 0) {
            abort(404);
        }
    }
}
