<?php

namespace Corbado;

use Corbado\Classes\Assert;
use Corbado\Classes\Helper;
use Corbado\Generated\Model\ClientInfo;
use Corbado\Generated\Model\GenericRsp;
use Corbado\Generated\Model\SmsCodeSendReq;
use Corbado\Generated\Model\SmsCodeSendRsp;
use Corbado\Generated\Model\SmsCodeSendRspAllOfData;
use Corbado\Generated\Model\SmsCodeValidateReq;
use GuzzleHttp\ClientInterface;
use JetBrains\PhpStorm\ArrayShape;

class SMSCodes
{
    private ClientInterface $client;

    #[ArrayShape(['X-Corbado-ProjectID' => "string"])]
    private function generateHeaders(string $projectId): array
    {
        return ['X-Corbado-ProjectID' => $projectId];
    }

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function send(string $projectID, string $phoneNumber, string $remoteAddress, string $userAgent, bool $create = false, ?string $requestID = ''): SmsCodeSendRsp
    {
        Assert::stringNotEmpty($projectID);
        Assert::stringNotEmpty($phoneNumber);
        Assert::stringNotEmpty($remoteAddress);
        Assert::stringNotEmpty($userAgent);

        $request = new SmsCodeSendReq();
        $request->setPhoneNumber($phoneNumber);
        $request->setRequestId($requestID);
        $request->setCreate($create);
        $request->setClientInfo(
            (new ClientInfo())->setRemoteAddress($remoteAddress)->setUserAgent($userAgent)
        );

        $res = $this->client->request('POST', 'smsCodes', ['body' => Helper::jsonEncode($request->jsonSerialize()), 'headers' => $this->generateHeaders($projectID)]);
        $json = Helper::jsonDecode($res->getBody()->getContents());

        if (Helper::isErrorHttpStatusCode($json['httpStatusCode'])) {
            Helper::throwException($json);
        }

        $data = new SmsCodeSendRspAllOfData();
        $data->setSmsCodeId($json['data']['smsCodeID']);

        $response = new SmsCodeSendRsp();
        $response->setHttpStatusCode($json['httpStatusCode']);
        $response->setMessage($json['message']);
        $response->setRequestData(Helper::hydrateRequestData($json['requestData']));
        $response->setRuntime($json['runtime']);
        $response->setData($data);

        return $response;
    }

    public function validate(string $projectID, string $smsCodeID, string $smsCode, string $remoteAddress, string $userAgent, ?string $requestID = ''): GenericRsp
    {
        Assert::stringNotEmpty($projectID);
        Assert::stringNotEmpty($smsCodeID);
        Assert::stringNotEmpty($smsCode);
        Assert::stringNotEmpty($remoteAddress);
        Assert::stringNotEmpty($userAgent);

        $request = new SmsCodeValidateReq();
        $request->setSmsCode($smsCode);
        $request->setRequestId($requestID);
        $request->setClientInfo(
            (new ClientInfo())->setRemoteAddress($remoteAddress)->setUserAgent($userAgent)
        );

        $res = $this->client->request('PUT', 'smsCodes/' . $smsCodeID . '/validate', ['body' => Helper::jsonEncode($request->jsonSerialize()), 'headers' => $this->generateHeaders($projectID)]);
        $json = Helper::jsonDecode($res->getBody()->getContents());

        if (Helper::isErrorHttpStatusCode($json['httpStatusCode'])) {
            Helper::throwException($json);
        }

        return Helper::hydrateResponse($json);
    }

}
