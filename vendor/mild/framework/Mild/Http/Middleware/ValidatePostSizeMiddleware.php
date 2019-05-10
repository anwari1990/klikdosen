<?php
/**
 * Mild Framework component
 *
 * @author Mochammad Riyadh Ilham Akbar Pasya
 * @link https://github.com/mildphp/mild
 * @copyright 2018
 * @license https://github.com/mildphp/mild/blob/master/LICENSE (MIT Licence)
 */
namespace Mild\Http\Middleware;

use Mild\Routing\RouterException;

class ValidatePostSizeMiddleware
{
    /**
     * @param \Mild\Http\Request $request
     * @param \Mild\Http\Response $response
     * @param callable $next
     * @return \Mild\Http\Response
     */
    public function __invoke($request, $response, $next)
    {
        $size = ini_get('post_max_size');
        switch (strtoupper($size[-1])) {
            case 'K':
                $size = (int) $size * 1024;
                break;
            case 'M':
                $size = (int) $size * 1048576;
                break;
            case 'G':
                $size = (int) $size * 1073741824;
                break;
            default:
                $size = (int) $size;
                break;
        }
        if ($size > 0 && $request->getServerParam('CONTENT_LENGTH') > $size) {
            throw new RouterException(413);
        }
        return $next($request, $response);
    }
}
