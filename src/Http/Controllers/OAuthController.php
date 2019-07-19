<?php

namespace CloudCreativity\LaravelStripe\Http\Controllers;

use CloudCreativity\LaravelStripe\Config;
use CloudCreativity\LaravelStripe\Events\OAuthError;
use CloudCreativity\LaravelStripe\Events\OAuthSuccess;
use CloudCreativity\LaravelStripe\Http\Requests\AuthorizeConnect;
use CloudCreativity\LaravelStripe\Log\Logger;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class OAuthController extends Controller
{

    /**
     * @var Logger
     */
    private $log;

    /**
     * OAuthController constructor.
     *
     * @param Logger $log
     */
    public function __construct(Logger $log)
    {
        $this->log = $log;
    }

    /**
     * Handle the Stripe Connect authorize endpoint.
     *
     * @param AuthorizeConnect $request
     * @return Response
     */
    public function __invoke(AuthorizeConnect $request)
    {
        $data = collect($request->query())->only([
            'state',
            'code',
            'scope',
            'error',
            'error_description',
        ]);

        $this->log->log('Received OAuth redirect.', $data->all());

        return $data->has('error') ? $this->error($data) : $this->success($data);
    }

    /**
     * Handle success.
     *
     * @param $data
     * @return Response
     */
    protected function success($data)
    {
        event($success = new OAuthSuccess(
            $data['code'],
            $data['scope'],
            $data['state'],
            Auth::user(),
            Config::connectSuccessView()
        ));

        return response()->view($success->view, $success->all());
    }

    /**
     * Handle an error.
     *
     * @param $data
     * @return Response
     */
    protected function error($data)
    {
        event($error = new OAuthError(
            $data['error'],
            $data['error_description'],
            $data['state'],
            Auth::user(),
            Config::connectErrorView()
        ));

        return response()->view(
            $error->view,
            $error->all(),
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }
}
