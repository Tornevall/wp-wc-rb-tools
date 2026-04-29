<?php

if (!defined('ABSPATH')) {
    exit;
}

class Tornevall_Resurs_Toolbox_Ip_Info_Proxy
{
    private const RUN_VALUE = 'getRbIpInfo';
    private const EVENT_TYPE_VALUE = 'noevent';
    private const IPV4_LOOKUP_URL = 'https://ipv4.fraudbl.org/';
    private const RESURS_WSDL_URL = 'https://test.resurs.com/ecommerce-test/ws/V4/ConfigurationService?wsdl';

    public static function maybe_handle_request(): void
    {
        if (!self::is_target_request()) {
            return;
        }

        wp_send_json(self::build_response_payload(), 200);
    }

    private static function is_target_request(): bool
    {
        $run = isset($_REQUEST['run']) ? sanitize_text_field(wp_unslash((string)$_REQUEST['run'])) : '';
        $eventType = isset($_REQUEST['event-type']) ? sanitize_key(wp_unslash((string)$_REQUEST['event-type'])) : '';

        return $run === self::RUN_VALUE && $eventType === self::EVENT_TYPE_VALUE;
    }

    /**
     * @return array<string,string>
     */
    private static function build_response_payload(): array
    {
        $lookup = self::fetch_ipv4_info();
        $soapStatus = self::detect_resurs_wsdl_status();
        $infoLines = [];

        foreach (['ip', 'host', 'SSL_PROTOCOL'] as $key) {
            if (!isset($lookup['data'][$key]) || $lookup['data'][$key] === '') {
                continue;
            }

            $infoLines[] = sprintf(
                '<b>%s</b>: %s',
                esc_html($key),
                esc_html((string)$lookup['data'][$key])
            );
        }

        $infoLines[] = sprintf(
            '<b>%s</b>',
            esc_html(sprintf('Resurs Request contains XML: %s', $soapStatus))
        );

        return [
            'errormessage' => $lookup['ok'] ? '' : (string)$lookup['error'],
            'externalinfo' => implode(",<br>\n", $infoLines),
        ];
    }

    /**
     * @return array{ok:bool,data:array<string,string>,error:string}
     */
    private static function fetch_ipv4_info(): array
    {
        $response = wp_remote_get(self::IPV4_LOOKUP_URL, [
            'timeout' => 10,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        if (is_wp_error($response)) {
            return [
                'ok' => false,
                'data' => [],
                'error' => self::normalize_error_message($response->get_error_message()),
            ];
        }

        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            return [
                'ok' => false,
                'data' => [],
                'error' => __('Could not parse the replacement IPv4 helper response.', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
            ];
        }

        $normalized = [];
        foreach (['ip', 'host', 'SSL_PROTOCOL'] as $key) {
            if (isset($decoded[$key])) {
                $normalized[$key] = (string)$decoded[$key];
            }
        }

        return [
            'ok' => true,
            'data' => $normalized,
            'error' => '',
        ];
    }

    private static function normalize_error_message(string $message, string $code = ''): string
    {
        $message = trim($message);
        if ($message === '') {
            $message = __('Could not reach service right now. Are your server connected or allowed to do outgoing traffic?', 'tornevall-networks-toolbox-for-resurs-bank-payments');
        }

        if ($code !== '') {
            return sprintf('%s (%s)', $message, $code);
        }

        return $message;
    }

    private static function detect_resurs_wsdl_status(): string
    {
        $response = wp_remote_get(self::RESURS_WSDL_URL, [
            'timeout' => 10,
        ]);

        if (is_wp_error($response)) {
            return self::normalize_error_message($response->get_error_message(), $response->get_error_code());
        }

        $body = wp_remote_retrieve_body($response);

        return preg_match('/<?xml/i', (string)$body) === 1 ? 'Yes' : 'No';
    }
}

