<?php
namespace Dokobit\Login;

use Dokobit\StatusResultInterface;

/**
 * Result object for mobile ID login status response.
 */
class MobileStatusResult extends AbstractStatusResult
{
    /**
     * Fields expected in response
     * @return array
     */
    public function getFields()
    {
        return [
            'status',
        ];
    }
}
