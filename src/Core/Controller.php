<?php
namespace Toda\Core;

use Phalcon\Mvc\Controller as ControllerRoot;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\View;
use Toda\Client\HttpClient;
use Toda\Validation\ErrorMessage;
use Toda\Validation\FileMime;


class Controller extends ControllerRoot
{
    protected $check_csrf = true;
    protected $recaptcha_secret = '';

    public function initialize()
    {
        $this->checkCSRF();

        if (DOMAIN . $this->router->getRewriteUri() !== $this->request->getHTTPReferer()) {
            if ($this->session->has('old')) {
                $this->session->remove('old');
            }
        }

        $this->view->setVars([
            'csrf_token' => $this->session->get('csrf_token')
        ]);

        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
    }

    public function beforeExecuteRoute(Dispatcher $dispatcher)
    {
        $this->beforeAction();
    }

    protected function beforeAction()
    {
    }

    protected function middleware($middleware, array $options = [])
    {
        $params = explode(':', $middleware);

        $run = false;
        if (empty($options)) {
            $run = true;
        } else {
            if (!empty($options['only']) && in_array($this->dispatcher->getActionName(), $options['only'])) {
                $run = true;
            }

            if (!empty($options['except']) && !in_array($this->dispatcher->getActionName(), $options['except'])) {
                $run = true;
            }
        }

        if ($run) {
            $name = $params[0] . 'Middleware';
            $this->$name(empty($params[1]) ? null : $params[1]);
        }
    }

    protected function checkCSRF()
    {
        if (!$this->session->has('csrf_token')) {
            $this->session->set('csrf_token', uniqid());
        }

        if ($this->check_csrf) {

            if ($this->request->isPost() && $this->dispatcher->getControllerName() != 'error') {

                if ($this->request->isAjax()) {
                    $token = $this->request->getHeader('X-CSRF-TOKEN');
                } else {
                    $token = $this->request->get('_csrf');
                }

                if ($token != $this->session->get('csrf_token') && $token != $this->setting->app_key) {
                    return $this->abort(403, 'Token not mismatch');
                }
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

    protected function renderJson($content)
    {
        return $this->response->setJsonContent($content)->send();
    }

    protected function back()
    {
        return $this->redirect($this->request->getHTTPReferer());
    }

    protected function redirect($to)
    {
        return $this->response->redirect($to)->send()->send();
    }

    protected function download($file, $expires = 0)
    {
        $this->view->setRenderLevel(View::LEVEL_NO_RENDER);
        header('Pragma: public');
        header('Expires: ' . $expires);
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header('Content-Description: File Transfer');
        header('Content-type:: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Content-length: ' . filesize($file));
        header('Content-Transfer-Encoding: binary');
        readfile($file);
        die();

    }

    protected function validateRequest(array $fields, $overwrite = [])
    {

        $error_messages = [];

        $request = $overwrite;

        /* Load request*/
        foreach ($this->request->get() as $key => $item) {
            if ($key != '_url' && $key != '_csrf') {
                if (!isset($request[$key])) {
                    if (is_array($item)) {
                        $request[$key] = array_map('htmlentities', array_map('trim', $item));
                    } else {
                        $request[$key] = htmlspecialchars(trim($item));
                    }
                }
            }
        }

        $file_keys = [];
        if ($this->request->hasFiles() == true) {
            foreach ($this->request->getUploadedFiles() as $file) {

                if ($file->getSize() != 0) {
                    $file_keys[] = $file->getKey();
                    $request[$file->getKey()] = $file;
                }
            }
        }
        /*End load request*/


        foreach ($fields as $item) {

            $conditions = explode('|', $item['value']);

            $value_check = is_null($request[$item['key']]) ? '' : $request[$item['key']];
            $field_name = $item['name'];

            foreach ($conditions as $condition) {

                $condition = explode(':', $condition, 2);
                $method = $condition[0];
                $param = empty($condition[1]) ? '' : $condition[1];

                if (!is_object($value_check)) {
                    if ($method == 'required') {
                        if (is_null($value_check) || $value_check == '') {

                            $error_messages[] = sprintf($this->lang->get('validate', $method), $field_name);
                        }
                    } else if ($method == 'email') {
                        if (!empty($value_check) && !preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/', $value_check)) {
                            $error_messages[] = sprintf($this->lang->get('validate', $method), $field_name);
                        }
                    } else if ($method == 'number') {
                        if (!empty($value_check) && !preg_match('/^[-]?[0-9]*\.?[0-9]+$/', $value_check)) {
                            $error_messages[] = sprintf($this->lang->get('validate', $method), $field_name);
                        }
                    } else if ($method == 'regex') {
                        if (!empty($value_check) && !preg_match($param, $value_check)) {
                            $error_messages[] = sprintf($this->lang->get('validate', $method), $field_name);
                        }
                    } else if ($method == 'min') {

                        if (!is_null($value_check) && floatval($value_check) <= floatval($param)) {
                            $error_messages[] = sprintf($this->lang->get('validate', $method), $field_name, $param);
                        }
                    } else if ($method == 'max') {
                        if (!is_null($value_check) && floatval($value_check) >= floatval($param)) {
                            $error_messages[] = sprintf($this->lang->get('validate', $method), $field_name, $param);
                        }
                    } else if ($method == 'min_length') {
                        if (!empty($value_check) && strlen($value_check) <= $param) {
                            $error_messages[] = sprintf($this->lang->get('validate', $method), $field_name, $param);
                        }
                    } else if ($method == 'max_length') {
                        if (!empty($value_check) && strlen($value_check) >= $param) {
                            $error_messages[] = sprintf($this->lang->get('validate', $method), $field_name, $param);
                        }
                    } else if ($method == 'confirmed') {
                        if (!empty($value_check) && $value_check !== $param) {
                            $error_messages[] = sprintf($this->lang->get('validate', $method), $field_name, $param);
                        }
                    } else if ($method == 'unique') {

                        if (!empty($value_check)) {
                            $params = explode(',', $param);

                            $query = $params[0]::where($item['key'], $value_check);

                            if (!empty($params[1])) {
                                $query->andWhere($item['key'], '!=', $params[1]);
                            }

                            if ($query->first()) {
                                $error_messages[] = sprintf($this->lang->get('validate', $method), $field_name);
                            }
                        }
                    }
                } else {
                    if ($method == 'size') {

                        if ($value_check->getSize() > floatval($param)) {
                            $error_messages[] = sprintf($this->lang->get('validate', $method), $field_name, $value_check->getSize(), $param);
                        }

                    } else if ($method == 'image') {
                        $result = FileMime::checkExtenstion($value_check->getType(), 'png,jpeg,gif');

                        if ($result['status'] == 0) {
                            $error_messages[] = sprintf($this->lang->get('validate', $method), $value_check->getType());
                        }

                    } else if ($method == 'file') {
                        $result = FileMime::checkExtenstion($value_check->getType(), $param);

                        if ($result['status'] == 0) {
                            $error_messages[] = sprintf($this->lang->get('validate', $method), $field_name);
                        }
                    }
                }
            }
        }
        if (!empty($error_messages)) {

            foreach ($file_keys as $key) {
                $request[$key] = null;
            }


            $this->session->set('old', $request);

            if ($this->request->isAjax()) {
                return $this->json($error_messages);
            } else {
                $this->flash->error(implode('<br>', $error_messages));
                return $this->back();
            }
        }
        return $request;
    }


    protected function verifyRecaptcha()
    {
        $captcha = '';
        if ($this->request->has('g-recaptcha-response')) {
            $captcha = $this->request->get('g-recaptcha-response');
        }
        $client = new HttpClient();

        $response = json_decode($client->get("https://www.google.com/recaptcha/api/siteverify?secret=" . $this->recaptcha_secret . "&response=" . $captcha . "&remoteip=" . $this->request->getClientAddress())
        );

        if (!$response->success) {
            $this->flash->error('Captcha invalid!!!');
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
            $this->abort(404);
        }
    }

    public function loadRequest($model, $request, $guard = [])
    {
        $guard = array_merge($guard, ['id', 'created_at', 'updated_at', 'deleted_at', 'created_id', 'updated_id', 'deleted_id']);;

        foreach ($request as $key => $value) {

            if (!in_array($key, $guard) && property_exists($model, $key)) {
                $model->$key = $value;
            }
        }

        return $model;

    }

    protected function abort($code, $message = '')
    {
        return $this->dispatcher->forward([
            'controller' => 'error',
            'action' => 'show' . $code,
            'params' => ['message' => $message],
        ]);
    }
}
