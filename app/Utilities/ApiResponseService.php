<?php

namespace App\Utilities;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ApiResponseService
{
    /**
     *  Main Success Reponse Functions
     * @param Array $data
     * @param ?string $msg
     * @param int $code
     */

    public static function successResponse($data = [], $msg = null, $code = 200, $operation = '', $success = true)
    {
        return response()->json(
            [
                'message' => $msg ?? trans('response.success'),
                'data'    => $data,
                'operation' => $operation,
                'success' => $success
            ],
            $code
        );
    }

    /**
     *  Main Error Reponse Functions
     * @param ?string $msg
     * @param int $code
     * @param ?Array $data
     */

    public static function errorResponse($msg = null, $code = 400, $data = null)
    {
        return response()->json(
            [
                'message' => $msg,
                'errors'  => $data ?? [$msg ?? trans('response.wrong')],
            ],
            $code
        );
    }

    /*****************************************************************************************/

    public static function validateResponse($errors, $code = 422)
    {
        return static::errorResponse(
            msg: trans('response.validation_error'),
            code: $code,
            data: $errors,
        );
    }
    public static function unprocessableResponse($errors = [], $code = 422, $msg = null)
    {
        return static::errorResponse(
            msg: $msg ?? trans('response.unprocessable_response'),
            code: $code,
            data: $errors,
        );
    }

    public static function successMsgResponse($msg = null, $code = 200)
    {
        return static::successResponse(
            msg: $msg,
            code: $code
        );
    }

    public static function deletedResponse($msg = null, $code = 200)
    {
        return static::successResponse(
            msg: $msg ?? trans('response.deleted'),
            code: $code,
            operation: 'delete'
        );
    }

    public static function createdResponse($data = [], $msg = null, $code = 200)
    {
        return static::successResponse(
            data: $data,
            msg: $msg ?? trans('response.created'),
            code: $code,
            operation: 'create'
        );
    }

    public
    static function updatedResponse($data = [], $msg = null, $code = 200)
    {
        return static::successResponse(
            data: $data,
            msg: $msg ?? trans('response.updated'),
            code: $code,
            operation: 'update'
        );
    }

    public
    static function notFoundResponse($msg = null, $code = 404)
    {
        return static::errorResponse(
            $msg ?? trans('response.not_found'),
            code: $code
        );
    }

    public
    static function unauthorizedResponse($msg = null, $code = 403)
    {
        return static::errorResponse(
            $msg ?? trans('response.unauthorized'),
            code: $code
        );
    }

    public
    static function errorMsgResponse($msg = null, $code = 400)
    {
        return static::errorResponse(
            msg: $msg,
            code: $code
        );
    }
    public
    static function serverError($msg = "Internal Server Error", $code = 500)
    {
        return static::errorResponse(
            msg: $msg,
            code: $code
        );
    }
    public static function file($path)
    {
        return response()->file($path, [
            'Content-Type' => mime_content_type($path)
        ]);
    }
    public static function streamFile($path)
    {
        return response()->stream(function () use ($path) {
            readfile($path);
        }, 200, [
            'Content-Type' => mime_content_type($path)
        ]);
    }
    public static function resourceCollection(AnonymousResourceCollection $data, $msg = null)
    {
        return $data->additional(
            $data->additional +
                [
                    "message" => $msg ?? trans('response.success')
                ]
        );
    }
}
