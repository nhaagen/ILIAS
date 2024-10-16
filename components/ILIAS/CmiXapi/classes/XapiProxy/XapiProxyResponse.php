<?php

declare(strict_types=1);
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace XapiProxy;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;

class XapiProxyResponse
{
    //        private $dic;
    private XapiProxy $xapiproxy;
    //private $xapiProxyRequest;

    public function __construct(XapiProxy $xapiproxy)
    {
        //            $this->dic = $GLOBALS['DIC'];
        $this->xapiproxy = $xapiproxy;
    }

    public function checkResponse(array $response, string $endpoint): bool
    {
        if ($response['state'] == 'fulfilled') {
            $status = $response['value']->getStatusCode();
            if ($status === 200 || $status === 204 || $status === 404) {
                return true;
            } else {
                $this->xapiproxy->log()->error("LRS error {$endpoint}: " . $response['value']->getBody());
                return false;
            }
        } else {
            try {
                $this->xapiproxy->log()->error("Connection error {$endpoint}: " . $response['reason']->getMessage());
            } catch (\Exception $e) {
                $this->xapiproxy->log()->error("error {$endpoint}:" . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * @param Request           $request
     * @param Response          $response
     * @param array|string|null $fakePostBody
     * @return void
     */
    public function handleResponse(Request $request, Response $response, array|string $fakePostBody = null): void
    {
        // check transfer encoding bug
        if ($fakePostBody !== null) {
            $origBody = $response->getBody();
            $this->xapiproxy->log()->debug($this->msg("orig body: " . $origBody));
            $this->xapiproxy->log()->debug($this->msg("fake body: " . json_encode($fakePostBody)));
            // because there is a real response object, it should also be possible to override the response stream...
            // but this does the job as well:
            $this->fakeResponseBlocked($fakePostBody);
        }
        $status = $response->getStatusCode();
        $headers = $response->getHeaders();
        if (array_key_exists('Transfer-Encoding', $headers) && $headers['Transfer-Encoding'][0] == "chunked") {
            $this->xapiproxy->log()->debug($this->msg("sniff response transfer-encoding for unallowed Content-length"));
            $body = (string) $response->getBody();
            unset($headers['Transfer-Encoding']);
            $headers['Content-Length'] = array(strlen($body));
            $response2 = new \GuzzleHttp\Psr7\Response($status, $headers, $body);
            $this->emit($response2);
        } else {
            $this->emit($response);
        }
    }

    /**
     * @param array|string|null $post
     * @return void
     */
    public function fakeResponseBlocked(array|string $post = null): void
    {
        $this->xapiproxy->log()->debug($this->msg("fakeResponseFromBlockedRequest"));
        if ($post === null) {
            $this->xapiproxy->log()->debug($this->msg("post === NULL"));
            try {
                $origin = (isset($_SERVER["HTTP_ORIGIN"])) ? $_SERVER["HTTP_ORIGIN"] : $_SERVER["HTTP_REFERRER"];
                if (isset($origin) && $origin != "") {
                    header('Access-Control-Allow-Origin: ' . $origin);
                } else {
                    $this->xapiproxy->log()->warning("could not get \$_SERVER[\"HTTP_ORIGIN\"] or \$_SERVER[\"HTTP_REFERRER\"]");
                }
            } catch (\Exception $e) {
                $this->xapiproxy->log()->warning($e->getMessage());
            }
            header('Access-Control-Allow-Credentials: true');
            header('X-Experience-API-Version: 1.0.3');
            header('HTTP/1.1 204 No Content');
            exit;
        } else {
            $ids = json_encode($post);
            $this->xapiproxy->log()->debug($this->msg("post: " . $ids));
            try {
                $origin = (isset($_SERVER["HTTP_ORIGIN"])) ? $_SERVER["HTTP_ORIGIN"] : $_SERVER["HTTP_REFERRER"];
                if (isset($origin) && $origin != "") {
                    header('Access-Control-Allow-Origin: ' . $origin);
                } else {
                    $this->xapiproxy->log()->warning("could not get \$_SERVER[\"HTTP_ORIGIN\"] or \$_SERVER[\"HTTP_REFERRER\"]");
                }
            } catch (\Exception $e) {
                $this->xapiproxy->log()->warning($e->getMessage());
            }
            header('Access-Control-Allow-Credentials: true');
            header('X-Experience-API-Version: 1.0.3');
            header('Content-Length: ' . strlen($ids));
            header('Content-Type: application/json; charset=utf-8');
            header('HTTP/1.1 200 Ok');
            echo $ids;
            exit;
        }
    }

    public function exitResponseError(): void
    {
        try {
            $origin = (isset($_SERVER["HTTP_ORIGIN"])) ? $_SERVER["HTTP_ORIGIN"] : $_SERVER["HTTP_REFERRER"];
            if (isset($origin) && $origin != "") {
                header('Access-Control-Allow-Origin: ' . $origin);
            } else {
                $this->xapiproxy->log()->warning("could not get \$_SERVER[\"HTTP_ORIGIN\"] or \$_SERVER[\"HTTP_REFERRER\"]");
            }
        } catch (\Exception $e) {
            $this->xapiproxy->log()->warning($e->getMessage());
        }
        header('Access-Control-Allow-Credentials: true');
        header('X-Experience-API-Version: 1.0.3');
        header("HTTP/1.1 412 Wrong Response");
        echo "HTTP/1.1 412 Wrong Response";
        exit;
    }

    public function exitProxyError(): void
    {
        try {
            $origin = (isset($_SERVER["HTTP_ORIGIN"])) ? $_SERVER["HTTP_ORIGIN"] : $_SERVER["HTTP_REFERRER"];
            if (isset($origin) && $origin != "") {
                header('Access-Control-Allow-Origin: ' . $origin);
            } else {
                $this->xapiproxy->log()->warning("could not get \$_SERVER[\"HTTP_ORIGIN\"] or \$_SERVER[\"HTTP_REFERRER\"]");
            }
        } catch (\Exception $e) {
            $this->xapiproxy->log()->warning($e->getMessage());
        }
        header('Access-Control-Allow-Credentials: true');
        header('X-Experience-API-Version: 1.0.3');
        header("HTTP/1.1 500 XapiProxy Error (Ask For Logs)");
        echo "HTTP/1.1 500 XapiProxy Error (Ask For Logs)";
        exit;
    }

    public function exitBadRequest(): void
    {
        try {
            $origin = (isset($_SERVER["HTTP_ORIGIN"])) ? $_SERVER["HTTP_ORIGIN"] : $_SERVER["HTTP_REFERRER"];
            if (isset($origin) && $origin != "") {
                header('Access-Control-Allow-Origin: ' . $origin);
            } else {
                $this->xapiproxy->log()->warning("could not get \$_SERVER[\"HTTP_ORIGIN\"] or \$_SERVER[\"HTTP_REFERRER\"]");
            }
        } catch (\Exception $e) {
            $this->xapiproxy->log()->warning($e->getMessage());
        }
        header('Access-Control-Allow-Credentials: true');
        header('X-Experience-API-Version: 1.0.3');
        header("HTTP/1.1 400 XapiProxy Bad Request (Ask For Logs)");
        echo "HTTP/1.1 400 XapiProxy Bad Request (Ask For Logs)";
        exit;
    }

    public function sendData(string $obj): void
    {
        $this->xapiproxy->log()->debug($this->msg("sendData: " . $obj));
        try {
            $origin = (isset($_SERVER["HTTP_ORIGIN"])) ? $_SERVER["HTTP_ORIGIN"] : $_SERVER["HTTP_REFERRER"];
            if (isset($origin) && $origin != "") {
                header('Access-Control-Allow-Origin: ' . $origin);
            } else {
                $this->xapiproxy->log()->warning("could not get \$_SERVER[\"HTTP_ORIGIN\"] or \$_SERVER[\"HTTP_REFERRER\"]");
            }
        } catch (\Exception $e) {
            $this->xapiproxy->log()->warning($e->getMessage());
        }
        header('Access-Control-Allow-Credentials: true');
        header('X-Experience-API-Version: 1.0.3');
        header('Content-Length: ' . strlen($obj));
        header('Content-Type: application/json; charset=utf-8');
        header('HTTP/1.1 200 Ok');
        echo $obj;
        exit;
    }

    public function emit(\GuzzleHttp\Psr7\Response $response): void
    {
        $this->xapiproxy->log()->debug($this->msg('emitting response'));
        if (headers_sent()) {
            $this->xapiproxy->log()->error($this->msg("Headers already sent!"));
            $this->exitProxyError();
        }
        if (ob_get_level() > 0 && ob_get_length() > 0) {
            $this->xapiproxy->log()->error($this->msg("Outputstream not empty!"));
            $this->exitProxyError();
        }

        $reasonPhrase = $response->getReasonPhrase();
        $statusCode = $response->getStatusCode();

        // header
        foreach ($response->getHeaders() as $header => $values) {
            $name = ucwords($header, '-');
            $first = $name === 'Set-Cookie' ? false : true;
            foreach ($values as $value) {
                header(sprintf(
                    '%s: %s',
                    $name,
                    $value
                ), $first, $statusCode);
                $first = false;
            }
        }

        // statusline
        header(sprintf(
            'HTTP/%s %d%s',
            $response->getProtocolVersion(),
            $statusCode,
            ($reasonPhrase ? ' ' . $reasonPhrase : '')
        ), true, $statusCode);

        // body
        echo $response->getBody();
    }

    private function msg(string $msg): string
    {
        return $this->xapiproxy->msg($msg);
    }
}
