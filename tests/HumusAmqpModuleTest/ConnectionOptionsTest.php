<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace HumusAmqpModuleTest;

use HumusAmqpModule\ConnectionOptions;

class ConnectionOptionsTest extends \PHPUnit_Framework_TestCase
{
    public function testGettersAndSetters()
    {
        $options = new ConnectionOptions(array(
            'host' => 'localhost',
            'port' => 5672,
            'login' => 'guest',
            'password' => 'passwd',
            'vhost' => '/',
            'persistent' => false,
            'readTimeout' => 1.00,
            'writeTimeout' => 2.00,
        ));
        $this->assertSame('localhost', $options->getHost());
        $this->assertSame(5672, $options->getPort());
        $this->assertSame('guest', $options->getLogin());
        $this->assertSame('passwd', $options->getPassword());
        $this->assertSame('/', $options->getVhost());
        $this->assertFalse($options->getPersistent());
        $this->assertEquals(1.00, $options->getReadTimeout());
        $this->assertEquals(2.00, $options->getWriteTimeout());
    }
}
