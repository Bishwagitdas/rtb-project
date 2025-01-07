<?php

namespace App\Traits;

trait BidRequestHandlerTrait
{
    // Validate the Bid Request JSON
    public function validateBidRequest($bidRequestJson)
    {

        $bidRequest = json_decode($bidRequestJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Invalid JSON in Bid Request'];
        }

        if (!isset($bidRequest['id']) || !isset($bidRequest['imp'][0]) || !isset($bidRequest['device'])) {
            return ['error' => 'Missing required fields in Bid Request'];
        }

        $imp = $bidRequest['imp'][0];
        if (!isset($imp['id']) || !isset($imp['bidfloor']) || !isset($imp['banner'])) {
            return ['error' => 'Missing required fields in Imp object'];
        }

        $device = $bidRequest['device'];
        if (!isset($device['geo']) || !isset($device['os'])) {
            return ['error' => 'Missing required fields in Device object'];
        }

        return $bidRequest;
    }

    public function selectCampaign($imp, $campaignArray, $geo, $device, $bidFloor, $currency)
{
    $bestCampaign = null;
    $highestBid = 0;

    foreach ($campaignArray as $campaign) {
        if (!isset($campaign['price'], $campaign['hs_os'], $campaign['country'])) {
            $this->log("Skipped campaign due to missing fields: " . json_encode($campaign));
            continue;
        }

        $deviceCompatible = in_array(strtolower($device['os']), explode(',', strtolower($campaign['hs_os']))) || $campaign['hs_os'] === "No Filter";
        $geoCompatible = $geo['country'] === $campaign['country'] || empty($campaign['country']);

        if ($deviceCompatible && $geoCompatible && $bidFloor <= $campaign['price'] && $campaign['price'] > $highestBid) {
            $bestCampaign = $campaign;
            $highestBid = $campaign['price'];
        } else {
            $this->log("Skipped campaign: " . $campaign['campaignname']);
        }
    }

    return $bestCampaign ?: ['error' => 'No suitable campaign found'];
}

    // Log the information (could be bid request or selected campaign)
    public function log($message)
    {
        file_put_contents(__DIR__ . '/../../logs/log.txt', "[" . date("Y-m-d H:i:s") . "] " . $message . "\n", FILE_APPEND);
    }
}

?>
