<?
require_once (__DIR__ .'/crest_tula.php');
require_once (__DIR__ .'/crest_ufa.php');
require_once (__DIR__ .'/getQuery.php');
require_once (__DIR__ .'/SafeMySQL.php');

$db = new SafeMySQL();

$deals_tyla = getQuery('CRestTula', 'crm.deal.list',[
	'filter' => [
        'STAGE_ID' => ['C3:UC_OKWQ29', 'C3:UC_08V9ZM', 'C3:UC_NU0NEA'],
        'CATEGORY_ID' => 3,
    ],
]);

$arDeals_tula = $deals_tyla['result'];
if( $deals_tyla['total'] > 50 ){
    $count_tula = 50;
    while( $count_tula < $deals_tyla['total'] ){
        $res_x = getQuery('CRestTula', 'crm.deal.list', [ //Получение списка сделок
            'filter' => [
                'STAGE_ID' => ['C3:UC_OKWQ29', 'C3:UC_08V9ZM', 'C3:UC_NU0NEA'],
                'CATEGORY_ID' => 3,
            ],
            'start'=>$count_tula
        ]);
        $arDeals_tula = array_merge($arDeals_tula,$res_x['result']);
        $count_tula = $count_tula + 50;
    }
}


$deals_ufa = getQuery('CRestUfa', 'crm.deal.list',[
	'filter' => [
        'CATEGORY_ID' => 58,
    ],
]);

$arDeals_ufa = $deals_ufa['result'];
if( $deals_ufa['total'] > 50 ){
    $count_ufa = 50;
    while( $count_ufa < $deals_ufa['total'] ){
        $res_x = getQuery('CRestUfa', 'crm.deal.list', [ //Получение списка сделок
            'filter' => [
                'CATEGORY_ID' => 58,
            ],
            'start'=>$count_ufa
        ]);
        $arDeals_ufa = array_merge($arDeals_ufa,$res_x['result']);
        $count_ufa = $count_ufa + 50;
    }
}


foreach($arDeals_tula as $deal_tula){
    $meaning = 0;
    $contact = getQuery('CRestTula', 'crm.contact.get',[
        'ID' => $deal_tula['ID']
    ]);

    $date = explode(' ', $contact['UF_CRM_6333543A28B78']);

    foreach($date as $value){
        $time = strtotime($value);
        if($time == true){
            $time = $value;
            unset($date[array_search($time, $date)]);
        }
    }

    $date = implode(' ', $date);

    if($contact['UF_CRM_62D05D7F42F09'] == 53){
        $contact['UF_CRM_62D05D7F42F09'] = 'Тула';
    } elseif ($contact['UF_CRM_62D05D7F42F09'] == 55){
        $contact['UF_CRM_62D05D7F42F09'] = 'Владимир';
    } else {
        $contact['UF_CRM_62D05D7F42F09'] = 'Другой (ОНЛАЙН)';
    }

    foreach($arDeals_ufa as $deal_ufa){
        if($deal_tula['UF_CRM_1664374736018'] != $deal_ufa['UF_CRM_1627447542'] && !empty($deal_tula['UF_CRM_1664374736018']) && !empty($deal_ufa['UF_CRM_1627447542'])){
                $meaning = 1;
        }
    }

    if ($meaning == 1){
       $batch_list_ufa = [
	        'contact' => [
		        'method' => 'crm.contact.add',
		        'params' => [
			        'fields' => [
				        'NAME' => $contact['NAME'],
	                    'SECOND_NAME' => $contact['SECOND_NAME'],
	                    'LAST_NAME' => $contact['LAST_NAME'],
	                    'PHONE' => [[
                            'VALUE' => $contact['PHONE'][0]['VALUE'],
                            'VALUE_TYPE' => $contact['PHONE'][0]['VALUE_TYPE'],
                            ]],
	                    'BIRTHDATE' => $contact['BIRTHDATE'],
	                    'ADDRESS' => $contact['ADDRESS'],
	                    'EMAIL' => [[
                            'VALUE' => $contact['EMAIL'][0]['VALUE'],
                            'VALUE_TYPE' => $contact['EMAIL'][0]['VALUE_TYPE'],
                        ]],
	                    'UF_CRM_629A1B699D519' => $contact['UF_CRM_62D05D7F42F09'],
	                    'UF_CRM_629F51D7AE750' => mb_substr($contact['UF_CRM_6333543A1D22F'], 0, 4),
	                    'UF_CRM_629F51D7F1D30' => mb_substr($contact['UF_CRM_6333543A1D22F'], -6, 6),
	                    'UF_CRM_629F51D834666' => $time,
	                    'UF_CRM_629F51D85F1A7' => $date,
			        ]
		        ]
	        ],
	        'deal' => [
		        'method' => 'crm.deal.add',
		        'params' => [
			        'fields' => [
				        'TITLE' => $deal_tula['TITLE'],
	                    'CONTACT_ID' => '$result[contact]',
	                    'CATEGORY_ID' => 58,
	                    'ASSIGNED_BY_ID' => 208,
	                    'UF_CRM_1701760298' => 1,
	                    'UF_CRM_1653545949629' => $contact['UF_CRM_62D05D7F42F09'],
	                    'COMMENTS' => $deal_tula['COMMENTS'],
	                    'UF_CRM_5D53E58571DB8' => $deal_tula['UF_CRM_6333543AAB9A1'],
	                    'UF_CRM_1627447542' => $deal_tula['UF_CRM_1664374736018'],
	                    'UF_CRM_1650372775123' => $deal_tula['UF_CRM_1664373248467'],
	                    'UF_CRM_1654154788530' => $deal_tula['UF_CRM_1664374644067'],
	                    'UF_CRM_625D560433A58' => 6182,
	                    'UF_CRM_1621386904' => 1,
	                    'TYPE_ID' => 'UC_M0M7LA',
	                    'SOURCE_ID' => 'UC_5IIS3U',
			        ]
		        ]
	        ],
        ];

        $ufa = getQueryBatch('CRestUfa', $batch_list_ufa);

    }
}

