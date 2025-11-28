<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProxyRequest;
use Proxy\Proxy;
use Proxy\Http\Request;
use Proxy\Plugin\CookiePlugin;
use Proxy\Plugin\HeaderRewritePlugin;
use Proxy\Plugin\ProxifyPlugin;
use Proxy\Plugin\StreamPlugin;

class IframeController extends Controller
{
    /**
     * Proxy URL
     *
     * @param ProxyRequest $request
     */
    public function proxy(ProxyRequest $request)
    {
        $data = $request->validated();
        $url = url_decrypt($data['q']);

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return response('Invalid URL.', 400);
        }

        $proxy = new Proxy();
        $proxy->addSubscriber(new HeaderRewritePlugin());
        $proxy->addSubscriber(new StreamPlugin());
        $proxy->addSubscriber(new CookiePlugin());
        $proxy->addSubscriber(new ProxifyPlugin());
        
        try {
            $req = Request::createFromGlobals();
            $req->get->clear();
            $response = $proxy->forward($req, $url);

            $response->send();
        } catch (\Exception $e) {
            return response('Proxy error: ' . $e->getMessage(), 500);
        }
    }
}
