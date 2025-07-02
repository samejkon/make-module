<?php

if (!function_exists('apiJson')) {
    /**
     * $code: 200, 400, 401, 403, 404, 500
     *   200: success
     *   400: bad request
     *   401: unauthorized
     *   403: forbidden
     *   404: not found
     *   500: server error
     *
     * @param mixed $data
     * @param int $code
     * @param string $message
     * @param array $headers
     * @return \Illuminate\Http\JsonResponse
     * */
    function apiJson($data, $code = 200, $message = null, $errors = null, $headers = [], $meta = [])
    {
        return response()->json(apiJsonDoc($data, $code, $message, $errors, $meta), $code <= 0 || $code > 599 ? 500 : $code, $headers);
    }
}

if (!function_exists('apiJsonDoc')) {
    function apiJsonDoc($data = null, $code = 200, $message = null, $errors = null, $meta = [])
    {
        //remove links last_page_url first_page_url next_page_url path prev_page_url

        if ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $data = $data->toArray();
        }
        $arrs = ['links', 'last_page_url', 'first_page_url', 'next_page_url', 'path', 'prev_page_url'];
        foreach ($arrs as $item) {
            if (isset($data[$item])) {
                unset($data[$item]);
            }
        }
        return [
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'errors' => $errors,
            'timestamp' => time(),
            'success' => $code < 400,
            'meta' => $meta,
            'version' => env('APP_VERSION', "DEV"),
        ];
    }
}
