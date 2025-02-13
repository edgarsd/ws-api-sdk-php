<?php
namespace Dokobit\Http;

use GuzzleHttp;
use GuzzleHttp\Exception\ClientException;
use Dokobit\Exception;

/**
 * Adapter for GuzzleHttp client
 */
class GuzzleClientAdapter implements ClientInterface
{
    /** @var GuzzleHttp\ClientInterface */
    private $client;

    /**
     * @param type GuzzleHttp\ClientInterface $client
     */
    public function __construct(GuzzleHttp\ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Send HTTP request
     * @param string $method POST|GET
     * @param string $url http URL
     * @param array $options query options. Query values goes under 'body' key.
     * Example:
     * $options = [
     *     'query' => [
     *         'access_token' => 'foobar',
     *     ],
     *     'body' => [
     *         'param1' => 'value1',
     *         'param2' => 'value2',
     *     ]
     * ]
     * @return array
     */
    public function sendRequest($method, $url, array $options = [])
    {
        $result = [];

        try {
            $response = $this->client->send(
                $this->client->createRequest($method, $url, $options)
            );
            $result = $response->json();
        } catch (ClientException $e) {
            if ($e->getCode() == 400) {
                throw new Exception\InvalidData(
                    'Data validation failed',
                    400,
                    $e,
                    $e->getResponse()->json()
                );
            } elseif ($e->getCode() == 403) {
                throw new Exception\InvalidApiKey(
                    'Access forbidden. Invalid API key.',
                    403,
                    $e,
                    $e->getResponse()->getBody()
                );
            } elseif ($e->getCode() == 500) {
                throw new Exception\ServerError(
                    'Error occurred on server side while handling request',
                    500,
                    $e,
                    $e->getResponse()->getBody()
                );
            } elseif ($e->getCode() == 504) {
                throw new Exception\Timeout(
                    'Request timeout',
                    504,
                    $e,
                    $e->getResponse()->getBody()
                );
            } else {
                throw new Exception\UnexpectedResponse(
                    'Unexpected error occurred',
                    $e->getCode(),
                    $e,
                    $e->getResponse()->getBody()
                );
            }
        } catch (\Exception $e) {
            throw new Exception\UnexpectedError('Unexpected error occurred', 0, $e);
        }

        return $result;
    }
}
