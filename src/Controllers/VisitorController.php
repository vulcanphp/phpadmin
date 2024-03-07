<?php

namespace VulcanPhp\PhpAdmin\Controllers;

use Throwable;
use VulcanPhp\PhpAdmin\Extensions\Whoer\Whoer;
use VulcanPhp\PhpAdmin\Models\Visitor;

class VisitorController
{
    // put all visitor record except these paths
    protected array $except = [
        '/admin/*',
        '/api/*',
    ];

    public function newVisitor(): void
    {
        if (setting('enabled_visitor_analytics') === 'true' && !$this->skip()) {

            $client = Whoer::init();

            if (!empty(trim(strval(request()->userAgent()))) && !$client->isBot() && strlen(url()->relativeUrl()) <= 200) {
                try {
                    $ip = inet_aton(request()->getIp());
                    $country = $client->country();
                    if ($country !== null && $ip !== false) {
                        Visitor::create([
                            'ip' => $ip,
                            'country' => $country,
                            'os' => $client->agent('os'),
                            'browser' => $client->agent('browser'),
                            'device' => $client->agent('device'),
                            'page' => url()->relativeUrl(),
                            'referer' => $client->refererDomain(),
                            'date' => date('Y-m-d', strtotime('now'))
                        ]);
                    }
                } catch (Throwable $e) {
                    if (is_dev()) {
                        throw $e;
                    }
                }
            }
        }
    }

    public function skip(): bool
    {
        $path = $this->parsePath(url()->getPath());

        foreach ($this->except as $url) {
            $url = rtrim($url, '/');

            if ($url[strlen($url) - 1] === '*') {
                $skip = stripos($path, $this->parsePath($url)) !== false;
            } else {
                $skip = ($this->parsePath($url) === $path);
            }

            if ($skip) {
                return true;
            }
        }

        return in_array(
            strtolower(pathinfo(url()->getPath(), PATHINFO_EXTENSION)),
            ['php', 'css', 'js', 'png', 'jpg', 'jpeg', 'json', 'html', 'gif', 'txt', 'ico', 'xml', 'pdf']
        );
    }

    protected function parsePath(string $path): string
    {
        return preg_replace('~/+~', '/', '/' . trim(str_replace('*', '', $path), '/') . '/');
    }
}
