<?php
namespace Crud\Error;

use Cake\Core\Configure;
use Cake\Core\Exception\MissingPluginException;
use Cake\Datasource\ConnectionManager;
use Cake\Event\Event;
use Cake\View\Exception\MissingViewException;
use Exception;

/**
 * Exception renderer for ApiListener
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class ExceptionRenderer extends \Cake\Error\ExceptionRenderer
{

    /**
     * Renders validation errors and sends a 412 error code
     *
     * @param \Exception $error Exception instance
     * @return void
     */
    public function validation($error)
    {
        $url = $this->controller->request->here();
        $status = $code = $error->getCode();
        try {
            $this->controller->response->statusCode($status);
        } catch (Exception $e) {
            $status = 412;
            $this->controller->response->statusCode($status);
        }

        $sets = [
            'code' => $code,
            'url' => h($url),
            'message' => $error->getMessage(),
            'error' => $error,
            'errorCount' => $error->getValidationErrorCount(),
            'errors' => $error->getValidationErrors(),
            '_serialize' => ['code', 'url', 'message', 'errorCount', 'errors']
        ];
        $this->controller->set($sets);
        return $this->_outputMessage('error400');
    }

    /**
     * Generate the response using the controller object.
     *
     * If there is no specific template for the raised error (normally there won't be one)
     * swallow the missing view exception and just use the standard
     * error format. This prevents throwing an unknown Exception and seeing instead
     * a MissingView exception
     *
     * @param string $template The template to render.
     * @return \Cake\Network\Response
     */
    protected function _outputMessage($template)
    {
        try {
            $viewVars = ['success', 'data'];
            $this->controller->set('success', false);
            $this->controller->set('data', $this->_getErrorData());
            if (Configure::read('debug')) {
                $queryLog = $this->_getQueryLog();
                if ($queryLog) {
                    $this->controller->set(compact('queryLog'));
                    $viewVars[] = 'queryLog';
                }
            }
            $this->controller->set('_serialize', $viewVars);
            $this->controller->render($template);
            $event = new Event('Controller.shutdown', $this->controller);
            $this->controller->afterFilter($event);
            return $this->controller->response;
        } catch (MissingViewException $e) {
            $attributes = $e->getAttributes();
            if (isset($attributes['file']) && strpos($attributes['file'], 'error500') !== false) {
                return $this->_outputMessageSafe('error500');
            }
            return $this->_outputMessage('error500');
        } catch (MissingPluginException $e) {
            $attributes = $e->getAttributes();
            if (isset($attributes['plugin']) && $attributes['plugin'] === $this->controller->plugin) {
                $this->controller->plugin = null;
            }
            return $this->_outputMessageSafe('error500');
        } catch (\Exception $e) {
            $this->controller->set([
                'error' => $e,
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            return $this->_outputMessageSafe('error500');
        }
    }

    /**
     * A safer way to render error messages, replaces all helpers, with basics
     * and doesn't call component methods.
     *
     * @param string $template The template to render
     * @return \Cake\Network\Response
     */
    protected function _outputMessageSafe($template)
    {
        $this->controller->layoutPath = '';
        $this->controller->subDir = '';
        $this->controller->viewPath = 'Errors/';
        $this->controller->viewClass = 'View';
        $this->controller->helpers = ['Form', 'Html', 'Session'];

        $this->controller->render($template);
        return $this->controller->response;
    }

    /**
     * Helper method used to generate extra debugging data into the error template
     *
     * @return array debugging data
     */
    protected function _getErrorData()
    {
        $data = [];

        $viewVars = $this->controller->viewVars;
        if (!empty($viewVars['_serialize'])) {
            foreach ($viewVars['_serialize'] as $v) {
                $data[$v] = $viewVars[$v];
            }
        }

        if (!empty($viewVars['error']) && Configure::read('debug')) {
            $data['exception'] = [
                'class' => get_class($viewVars['error']),
                'code' => $viewVars['error']->getCode(),
                'message' => $viewVars['error']->getMessage(),
                'trace' => preg_split('@\n@', $viewVars['error']->getTraceAsString()),
            ];
        }

        return $data;
    }

    /**
     * Helper method to get query log.
     *
     * @return array Query log.
     */
    protected function _getQueryLog()
    {
        $queryLog = [];
        $sources = ConnectionManager::configured();
        foreach ($sources as $source) {
            $logger = ConnectionManager::get($source)->logger();
            if (method_exists($logger, 'getLogs')) {
                $queryLog[$source] = $logger->getLogs();
            }
        }
        return $queryLog;
    }
}
