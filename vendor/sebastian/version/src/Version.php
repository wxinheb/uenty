<?php
/*
 * This file is part of the Version package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianBergmann;


class Version
{
    
    private $path;

    
    private $release;

    
    private $version;

    
    public function __construct($release, $path)
    {
        $this->release = $release;
        $this->path    = $path;
    }

    
    public function getVersion()
    {
        if ($this->version === null) {
            if (count(explode('.', $this->release)) == 3) {
                $this->version = $this->release;
            } else {
                $this->version = $this->release . '-dev';
            }

            $git = $this->getGitInformation($this->path);

            if ($git) {
                if (count(explode('.', $this->release)) == 3) {
                    $this->version = $git;
                } else {
                    $git = explode('-', $git);

                    $this->version = $this->release . '-' . end($git);
                }
            }
        }

        return $this->version;
    }

    
    private function getGitInformation($path)
    {
        if (!is_dir($path . DIRECTORY_SEPARATOR . '.git')) {
            return false;
        }

        $process = proc_open(
            'git describe --tags',
            [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            $path
        );

        if (!is_resource($process)) {
            return false;
        }

        $result = trim(stream_get_contents($pipes[1]));

        fclose($pipes[1]);
        fclose($pipes[2]);

        $returnCode = proc_close($process);

        if ($returnCode !== 0) {
            return false;
        }

        return $result;
    }
}
