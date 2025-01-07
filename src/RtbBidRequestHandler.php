<?php

require_once(__DIR__ . '/Traits/BidRequestHandlerTrait.php');

use App\Traits\BidRequestHandlerTrait;

class RtbBidRequestHandler
{
    use BidRequestHandlerTrait;

    public function handleBidRequest()
    {
        // Read the bid request from the 'data' directory
        $bidRequestJson = file_get_contents(__DIR__ . '/../data/bid_request.json');
        

        $bidRequest = $this->validateBidRequest($bidRequestJson);
        if (isset($bidRequest['error'])) {
            return $this->sendErrorResponse($bidRequest['error']);
        }

        // Read the campaigns from the 'data' directory
        $campaignArray = json_decode(file_get_contents(__DIR__ . '/../data/campaigns.json'), true);


        $imp = $bidRequest['imp'][0] ?? null;
        $geo = $bidRequest['device']['geo'] ?? null;
        $device = $bidRequest['device'] ?? null;
        $bidFloor = $imp['bidfloor'] ?? 0;
        $currency = $bidRequest['cur'][0] ?? 'USD';

        // Get the Best Campaign Based on Bid Request
        $selectedCampaign = $this->selectCampaign($imp, $campaignArray, $geo, $device, $bidFloor, $currency);

        if (isset($selectedCampaign['error'])) {
            return $this->sendErrorResponse($selectedCampaign['error']);
        }

        $this->log("Campaign selected: " . $selectedCampaign['campaignname']);

        $response = [
            'id' => $bidRequest['id'],
            'seatbid' => [
                [
                    'bid' => [
                        [
                            'id' => uniqid(),
                            'impid' => $imp['id'],
                            'price' => $selectedCampaign['price'],
                            'adid' => $selectedCampaign['creative_id'],
                            'nurl' => $selectedCampaign['url'],
                            'adm' => '<img src="' . $selectedCampaign['image_url'] . '" />', 
                            'cid' => $selectedCampaign['campaignname'],
                            'advertiser' => $selectedCampaign['advertiser'],
                            'width' => $imp['banner']['w'],
                            'height' => $imp['banner']['h']
                        ]
                    ]
                ]
            ]
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    private function sendErrorResponse($errorMessage)
    {
        $this->log("Error: " . $errorMessage);

        header('Content-Type: application/json');
        echo json_encode(['error' => $errorMessage]);
        exit;
    }
}

// Instantiate the handler class and handle the bid request
$handler = new RtbBidRequestHandler();
$handler->handleBidRequest();
?>
