<?php
namespace Dokobit\Tests\Integration;

use Dokobit\ResultInterface;
use Dokobit\Sign\Sc;
use Dokobit\Sign\ScPrepare;
use Dokobit\Sign\ScPrepareResult;

class ScSignTest extends TestCase
{
    /**
     * @expectedException Dokobit\Exception\QueryValidator
     */
    public function testRequiredParams()
    {
        $this->client->get(new ScPrepare(
            null,
            null,
            null,
            null,
            array()
        ));
    }

    public function testPrepare()
    {
        /** @var ScPrepareResult $result */
        $result = $this->client->get(new ScPrepare(
            base64_encode(CERTIFICATE_SIGN),
            'pdf',
            false,
            'LT',
            $this->getDocumentParams()
        ));

        $this->assertSame(ResultInterface::STATUS_OK, $result->getStatus());
        $this->assertNotEmpty($result->getAlgorithm());
        $this->assertNotEmpty($result->getToken());
        $this->assertNotEmpty($result->getDtbsHash());
        $this->assertNotEmpty($result->getDtbs());

        return $result;
    }

    /**
     * @depends testPrepare
     *
     * @param ScPrepareResult $result
     */
    public function testSign(ScPrepareResult $result)
    {
        $result = $this->client->get(new Sc($result->getToken(), $this->sign($result->getDtbs(), PRIVATE_KEY_SIGN)));

        $this->assertSame(ResultInterface::STATUS_OK, $result->getStatus());
        $this->assertSame('Signature1', $result->getSignatureId());
        $this->assertNotEmpty($result->getFile());
    }

    /**
     * Test parameters validation on API by sending invalid personal code
     * @expectedException Dokobit\Exception\InvalidData
     * @expectedExceptionMessage Data validation failed
     */
    public function testBadRequest()
    {
        $documentParams = $this->getDocumentParams();
        unset($documentParams['contact']);
        /** @var Dokobit\Sign\MobileResult $result */
        $result = $this->client->get(new ScPrepare(
            base64_encode(CERTIFICATE_SIGN),
            'pdf',
            false,
            'LT',
            $documentParams
        ));
    }

    private function getDocumentParams()
    {
        return [
            'files' => [
                __DIR__.'/../data/document.pdf',
            ],
            'contact'   => 'Ponas Testuotojas',
            'reason'    => 'Pasirašymas',
            'location'  => 'Vilnius',
        ];
    }
}
