<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

/**
 * Class Controller
 * @package App\Http\Controllers
 */
class Controller extends BaseController
{
    /**
     * @param array $data
     * @param int $status
     * @param array $headers
     * @param int $options
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function response($data, $status = 200, array $headers = [], $options = 0)
    {
        return response()->json(['success' => $status < 300, 'status' => $status, 'data' => $data], 200, $headers, $options);
    }
    //
}
