<?php

namespace App\Exceptions;

use Exception;
use App\Traits\ApiResponser;
use Illuminate\Database\QueryException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    use ApiResponser;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof ModelNotFoundException){
            $model = strtolower(class_basename($exception->getModel()));
            return $this->errorResponse([
                'message' => "No existe ninguna instancia de {$model}",'code' => 404
            ], 404);
        }
        if ($exception instanceof AuthenticationException) {
            return $this->unauthenticatedApi($request, $exception);
        }
        if ($exception instanceof AuthorizationException){
            return $this->errorResponse([
                'error' => 'No poseee permisos para ejecutar esta accion',
                'code' => 403
            ], 403);
        }
        if ($exception instanceof NotFoundHttpException) {
            return $this->errorResponse([
                'error' => 'No se econtro la URL.',
                'code' => 404
            ], 404);
        }
        if ($exception instanceof MethodNotAllowedHttpException) {
            return $this->errorResponse([
                'error' => 'EL método especificado en la petición no es válido.',
                'code' => 405
            ], 405);
        }
        if ($exception instanceof HttpException) {
            return $this->errorResponse([
                'error' => $exception->getMessage(),
                'code' => $exception->getStatusCode()
            ], $exception->getStatusCode());
        }
        if ($exception instanceof QueryException) {
            $code = $exception->errorInfo[1];
            switch ($code) {
                case 1451:
                        return $this->errorResponse([
                            'error' => 'No se puede eliminar de forma permanente el recurso',
                            'code' => 409
                        ], 409);
                    break;
            }
        }

        if ($exception instanceof TokenMismatchException) {
            return redirect()->back()->withInput($request->input());
        }

        // if ($this->isFrontend($request)) {
        //     return $request->ajax() ? response()->json($)
        // }

        if (config('app.debug')) {
            return parent::render($request, $exception);
        }

        return $this->errorResponse([
            'error' => 'Falla inseperada. Intenta más tarde',
            'code' => 500
        ], 500);
    }

    protected function unauthenticatedApi($request, AuthenticationException $exception)
    {
        if ($this->isFrontend($request)) {
            return redirect()->guest('login');
        }
        return $this->errorResponse([
            'error' => 'No autenticado.',
            'code' => 401
        ], 401);
        // return $request->expectsJson()
        //             ? response()->json(['message' => $exception->getMessage()], 401)
        //             : redirect()->guest($exception->redirectTo() ?? route('login'));
    }

    private function isFrontend($request)
    {
        return $request->acceptsHtml() && collect($request->route()->middleware())->contains('web');
    }
}
