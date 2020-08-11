<?php

class TelegramMessage
{
    private $authToken = '1234567890qwertyuiopasdfghjklzxc'; // Авторизационный токен для приложения. Приходит в массиве POST от Битрикса
    private $telegramToken = '1234567890:QWERTYUIOPASDFGHJKLZXCVBNMqwertyuio'; // Токен телеграма
    private $chatId = '000000000'; // ID чата в телеграм
    private $crmQueryUrl = 'https://your-bitrix-domain.bitrix24.ru/rest/#userId#/#bitrixWebhookCode#/crm.lead.list'; // #userId# - ID пользователя, создавшего вебхук. #bitrixWebhookCode# - код созданного вебхука.
    private $leadInfo;

    /**
     * TelegramMessage constructor.
     * @param array $postData
     */
    public function __construct(array $postData)
    {
        if ($this->checkAuth($postData['auth']['application_token'])) {
            $this->leadInfo = $this->setLeadInfo($postData['data']['FIELDS']['ID']);
        }
    }

    /**
     * @return string
     */
    public function getAuthToken(): string
    {
        return $this->authToken;
    }

    /**
     * @return string
     */
    public function getTelegramToken(): string
    {
        return $this->telegramToken;
    }

    /**
     * @return string
     */
    public function getChatId(): string
    {
        return $this->chatId;
    }

    /**
     * @return array|null
     */
    public function getLeadInfo(): ?array
    {
        return $this->leadInfo;
    }

    /**
     * @return string
     */
    public function getCrmQueryUrl(): string
    {
        return $this->crmQueryUrl;
    }

    /*
     * @param string $applicationToken
     * @return bool
     */
    private function checkAuth(string $applicationToken): bool
    {
        $auth = false;

        if ($applicationToken === $this->getAuthToken()) {
            $auth = true;
        }

        return $auth;
    }

    /*
     * @param string $leadId
     * @return array
     */
    private function setLeadInfo(string $leadId): array
    {
        $crmQueryData = http_build_query([
            'filter' => [
                'ID' => $leadId,
            ],
            'select' => [
                '*',
                'PHONE',
                'EMAIL',
            ],
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->getCrmQueryUrl(),
            CURLOPT_POSTFIELDS => $crmQueryData
        ));

        $result = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($result, 1);

        return $result;
    }

    /**
     * @return void
     */
    public function sendTelegramMessage(): void
    {
        $leadData = $this->getLeadInfo();

        if ($leadData) {

            $messageText = 'У вас в Битриксе новый лид! ' . PHP_EOL;

            foreach ($leadData['result'] as $lead) {
                if ($lead['NAME']) {
                    $messageText .= 'Имя: ' . $lead['NAME'] . PHP_EOL;
                }
                if ($lead['PHONE']) {
                    foreach ($lead['PHONE'] as $phone) {
                        $messageText .= 'Телефон: ' . $phone['VALUE'] . PHP_EOL;
                    }
                }
                if ($lead['EMAIL']) {
                    foreach ($lead['EMAIL'] as $email) {
                        $messageText .= 'Email: ' . $email['VALUE'] . PHP_EOL;
                    }
                }
                if ($lead['COMMENTS']) {
                    $messageText .= 'Комментарий: ' . $lead['COMMENTS'] . PHP_EOL;
                }
            }

            $messageText .= 'Посмотреть лид: https://your-bitrix-domain.bitrix24.ru/crm/lead/details/' .  $leadData['result'][0]['ID'] . '/';

            $telegramQueryData = [
                'chat_id' => $this->getChatId(),
                'text' => $messageText,
            ];

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_POST => 1,
                CURLOPT_HEADER => 0,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => 'https://api.telegram.org/bot' . $this->getTelegramToken() . '/sendMessage',
                CURLOPT_POSTFIELDS => $telegramQueryData
            ));
            $result = curl_exec($curl);
            curl_close($curl);
        }
    }
}