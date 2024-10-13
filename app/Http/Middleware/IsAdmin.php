<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
       // ログインユーザーが管理者かどうかを確認
       if (auth()->check() && auth()->user()->auth == 1) {
        return $next($request);
    }

    // 管理者でない場合は403エラー
    throw new AccessDeniedHttpException('このページにアクセスする権限がありません。');
    }
}
