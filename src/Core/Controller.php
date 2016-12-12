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
    protected $old = null;

    public function initialize()
    {
        if (!$this->run_background) {
            $this->checkCSRF();

            if ($this->session->has('old')) {
                $this->old = new OldValue($this->session->get('old'));
                $this->session->remove('old');
            }
            $this->view->setVars([
                'old' => $this->old
            ]);
        }

        $this->response->setHeader("Content-Type", "text/html; charset=utf-8");

        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
    }

    protected function middleware($middleware, array $options = [])
    {
        $params = explode(':', $middleware);

        $run = (preg_match('/' . $this->dispatcher->getActionName() . '/', reset($options)));

        if (($run && key($options) === 'only') || (!$run && key($options) !== 'only')) {
            $name = $params[0] . 'Middleware';

            $this->$name(empty($params[1]) ? null : $params[1]);
        }
    }

    protected function checkCSRF()
    {
        if (!$this->session->has('token')) {
            $this->session->set('token', uniqid());
        }

        if ($this->request->isPost() && $this->dispatcher->getControllerName() != 'error') {


            if($this->request->isAjax()){
                $token = $this->request-> getHeader('X-CSRF-TOKEN');
            } else {
                $token = $this->request->get('_csrf');
            }

            if ($token != $this->session->get('token')) {

                return $this->dispatcher->forward([
                    'controller' => 'error',
                    'action' => 'show403',
                    'params' => ['message' => 'CSRF token not mismatch'],
                ]);
            }
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

    protected function validate(array $conditions, $overwrite = [])
    {

        $error_messages = [];

        $arr_check = array_map('trim', $this->request->get());

        $arr_check = array_merge($arr_check, $overwrite);

        $arr_check = array_map('htmlspecialchars', $arr_check);

        $valid = true;

        foreach ($conditions as $key => $condition) {

            $arr_condition = explode('|', $condition);
            foreach ($arr_condition as $item) {
                $params = explode(':', $item, 2);
                $method = $params[0];
                $param = empty($params[1]) ? '' : $params[1];

                $message = Validate::$method($key, $arr_check[$key], $param);

                if (!empty($message)) {

                    $valid = false;
                    $error_messages[$key] = $message;
                    break;
                }
            }
        }
        if (!$valid) {
            $this->session->set('old', $arr_check);

            if ($this->request->isAjax()) {
                return $this->json($error_messages);
            } else {
                $this->flash->error(implode('<br>', $error_messages));
                return $this->back();
            }
        }
        return $arr_check;
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
            $this->flash->error('Captcha incorrect.');
            return $this->back();
        }
    }

    protected function getPage()
    {
        $page = $this->request->get('page');
        return $page < 1 || is_nan($page) ? 1 : $page;
    }

    protected function emptyTo404($data)
    {
        if (empty($data) || count($data) == 0 || count($data->items) == 0) {
            abort(404);
        }
    }
}
