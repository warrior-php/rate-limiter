<?php
declare(strict_types=1);

namespace Warrior\RateLimiter;

use support\exception\BusinessException;
use Webman\Http\Request;
use Webman\Http\Response;

class RateLimitException extends BusinessException
{
    /**
     * Render
     *
     * @param Request $request
     *
     * @return Response|null
     */
    public function render(Request $request): ?Response
    {
        $code = $this->getCode() ?: 429;
        $data = $this->data ?? [];
        $message = $this->trans($this->getMessage(), $data);
        if ($request->expectsJson()) {
            $json = ['code' => $code, 'msg' => $message, 'data' => $data];
            return new Response(200, ['Content-Type' => 'application/json'], json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        return new Response($code, [], $message);
    }
}