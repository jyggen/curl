<?php
/**
 * This file is part of the jyggen/curl library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Jonas Stendahl <jonas.stendahl@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @link https://jyggen.com/projects/jyggen-curl Documentation
 * @link https://packagist.org/packages/jyggen/curl Packagist
 * @link https://github.com/jyggen/curl GitHub
 */

namespace Jyggen\Curl {

    require_once './vendor/autoload.php';

    $mockAddMulti = false;
    function curl_multi_add_handle($multi, $curl)
    {
        global $mockAddMulti;
        if ($mockAddMulti) {
            return CURLM_INTERNAL_ERROR;
        }
        return \curl_multi_add_handle($multi, $curl);
    }

    $mockMultiExec = false;
    function curl_multi_exec($handle, &$active)
    {
        global $mockMultiExec;
        if ($mockMultiExec) {
            $active = false;
            return CURLM_INTERNAL_ERROR;
        }
        return \curl_multi_exec($handle, $active);
    }

}
