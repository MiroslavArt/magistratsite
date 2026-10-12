<?php

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require("b24hookfunction.php");

//echo B24USER; 

$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

if($lead_id) {
        $params = http_build_query(array(
            'halt' => 0,
            'cmd' => array(
                'get_activity' => 'crm.activity.list?'
                    . http_build_query(array(
                        'ORDER' => array(
                            'ID' => 'ASC'
                        ),
                        'FILTER' => array(
                            'OWNER_ID' => $lead_id,
                            'OWNER_TYPE_ID' => 1
                        ),
                        'SELECT' => array(
                            'ID',
                            'DESCRIPTION'
                        )
                    ))
            )
        )
    );

    $out = opt($params, B24HOST, B24USER, B24TOKENID);

    $actData = current($out['result']['result']['get_activity']);

    $organizations = array();
    $description = isset($actData['DESCRIPTION']) ? $actData['DESCRIPTION'] : '';

    if (is_string($description) && trim($description) !== '') {
        $dom = new \DOMDocument();
        $previousErrors = libxml_use_internal_errors(true);

        try {
            // Явно задаем UTF-8, чтобы сохранить кириллицу в названиях.
            if ($dom->loadHTML('<?xml encoding="UTF-8">' . $description, LIBXML_NONET)) {
                $xpath = new \DOMXPath($dom);
                // В шаблоне Saby организация указана в отдельной ячейке colspan="2".
                $links = $xpath->query(
                    '//td[@colspan="2"]/a['
                    . 'contains(concat(" ", normalize-space(@class), " "), " data_title_link ")'
                    . ' and starts-with(@href, "https://report.saby.ru/opendoc.html?")]'
                );

                foreach ($links as $link) {
                    $name = trim(preg_replace('/[\s\x{00A0}]+/u', ' ', $link->textContent));
                    if ($name !== '' && !in_array($name, $organizations, true)) {
                        $organizations[] = $name;
                    }
                }
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrors);
        }
    }

    $companies = array();
    $pendingSearches = array();
    foreach ($organizations as $index => $organization) {
        $pendingSearches['company_' . $index] = array(
            'filter' => array('%UF_CRM_1791535804' => $organization),
            'select' => array('ID', 'UF_CRM_1602579449778', 'UF_CRM_1638253233'),
            'order' => array('ID' => 'ASC'),
            'start' => 0
        );
    }

    // Batch принимает максимум 50 команд; у списка компаний также есть пагинация.
    while ($pendingSearches) {
        $searches = array_slice($pendingSearches, 0, 50, true);
        $commands = array();
        foreach ($searches as $key => $search) {
            unset($pendingSearches[$key]);
            $commands[$key] = 'crm.company.list?' . http_build_query($search);
        }

        $companyResponse = opt(http_build_query(array('halt' => 0, 'cmd' => $commands)), B24HOST, B24USER, B24TOKENID);
        if (!isset($companyResponse['result']['result'])) {
            \Bitrix\Main\Diag\Debug::writeToFile($companyResponse, 'sbis company batch error', 'hooks.log');
            break;
        }
        if (!empty($companyResponse['result']['result_error'])) {
            \Bitrix\Main\Diag\Debug::writeToFile($companyResponse['result']['result_error'], 'sbis company search errors', 'hooks.log');
        }

        foreach ($searches as $key => $search) {
            if (!isset($companyResponse['result']['result'][$key])) {
                continue;
            }
            foreach ($companyResponse['result']['result'][$key] as $company) {
                if (!empty($company['ID'])) {
                    // Одна компания может совпасть с несколькими названиями в письме.
                    $companies[$company['ID']] = $company;
                }
            }
            $next = isset($companyResponse['result']['result_next'][$key])
                ? $companyResponse['result']['result_next'][$key] : null;
            if (is_numeric($next) && (int) $next > $search['start']) {
                $search['start'] = (int) $next;
                $pendingSearches[$key] = $search;
            }
        }
    }

    $taskCommands = array();
    $deadline = (new \DateTimeImmutable('now', new \DateTimeZone('Europe/Moscow')))
        ->modify('+3 hours')->format('c');
    foreach ($companies as $companyId => $company) {
        $accountant = isset($company['UF_CRM_1638253233']) ? $company['UF_CRM_1638253233'] : '';
        $responsibleId = is_scalar($accountant) ? (int) preg_replace('/[^0-9]/', '', (string) $accountant) : 0;
        $project = isset($company['UF_CRM_1602579449778']) ? $company['UF_CRM_1602579449778'] : '';
        $groupId = is_scalar($project) && ctype_digit((string) $project) ? (int) $project : 0;
        if ($responsibleId <= 0 || $groupId <= 0) {
            \Bitrix\Main\Diag\Debug::writeToFile($company, 'sbis company skipped: missing accountant or project', 'hooks.log');
            continue;
        }

        $taskCommands['task_company_' . $companyId] = 'tasks.task.add?' . http_build_query(array(
            'fields' => array(
                'TITLE' => 'Новое требование из СБИС',
                'DEADLINE' => $deadline,
                'CREATED_BY' => 1225,
                'RESPONSIBLE_ID' => $responsibleId,
                'UF_CRM_TASK' => array('CO_' . $companyId),
                'GROUP_ID' => $groupId
            )
        ));
    }
    foreach (array_chunk($taskCommands, 50, true) as $commands) {
        $taskResponse = opt(http_build_query(array('halt' => 0, 'cmd' => $commands)), B24HOST, B24USER, B24TOKENID);
        \Bitrix\Main\Diag\Debug::writeToFile($taskResponse, 'sbis task creation result', 'hooks.log');
    }

    \Bitrix\Main\Diag\Debug::writeToFile($actData, "resp ".date('Y:m:d H:i:s'), "hooks.log");

}

