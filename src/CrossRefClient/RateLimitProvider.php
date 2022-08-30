<?php

/*
 * This file is part of CrossRef Client.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\CrossRefClient;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Psr\Http\Message\RequestInterface;

if (InstalledVersions::satisfies(new VersionParser(), 'rtheunissen/guzzle-rate-limiter', '^2.0')) {
    /**
     * @see https://github.com/CrossRef/rest-api-doc#rate-limits
     */
    class RateLimitProvider extends AbstractRateLimitProvider
    {
        public function getLastRequestTime(RequestInterface $request)
        {
            return $this->doGetLastRequestTime();
        }

        public function setLastRequestTime(RequestInterface $request)
        {
            $this->doSetLastRequestTime();
        }
    }
} else {
    class RateLimitProvider extends AbstractRateLimitProvider
    {
        public function getLastRequestTime()
        {
            return $this->doGetLastRequestTime();
        }

        public function setLastRequestTime()
        {
            $this->doSetLastRequestTime();
        }
    }
}
