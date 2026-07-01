<?php
declare(strict_types=1);

namespace App\Http;

use Cake\Http\Session;
use Cake\Http\ServerRequest;

class LegacyServerRequest extends ServerRequest
{
    public static function fromRequest(ServerRequest $request): self
    {
        $compat = new self([
            'environment' => $request->getServerParams(),
            'query' => $request->getQueryParams(),
            'post' => (array)$request->getParsedBody(),
            'files' => $request->getUploadedFiles(),
            'cookies' => $request->getCookieParams(),
            'params' => [
                'plugin' => $request->getParam('plugin'),
                'controller' => $request->getParam('controller'),
                'action' => $request->getParam('action'),
                '_ext' => $request->getParam('_ext'),
                'pass' => $request->getParam('pass', []),
            ],
            'session' => $request->getSession(),
            'url' => $request->getUri()->getPath(),
        ]);

        $compat->params = [
            'plugin' => $request->getParam('plugin'),
            'controller' => $request->getParam('controller'),
            'action' => $request->getParam('action'),
            '_ext' => $request->getParam('_ext'),
            'pass' => $request->getParam('pass', []),
        ];
        $compat->data = $request->getData();
        $compat->query = $request->getQueryParams();
        $compat->cookies = $request->getCookieParams();
        $compat->here = $request->getUri()->getPath();
        $compat->base = (string)$request->getAttribute('base', '');
        $compat->webroot = (string)$request->getAttribute('webroot', '/');

        return $compat;
    }

    public function &__get($name)
    {
        if (in_array($name, ['data', 'params', 'query', 'cookies', 'here', 'base', 'webroot'], true)) {
            return $this->{$name};
        }

        $null = null;

        return $null;
    }

    public function __set($name, $value): void
    {
        if (in_array($name, ['data', 'params', 'query', 'cookies', 'here', 'base', 'webroot'], true)) {
            $this->{$name} = $value;
        }
    }

    public function __isset($name): bool
    {
        return in_array($name, ['data', 'params', 'query', 'cookies', 'here', 'base', 'webroot'], true) && isset($this->{$name});
    }

    public function session(): Session
    {
        return $this->getSession();
    }
}