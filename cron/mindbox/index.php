<?php
include __DIR__ . '/../../assets/includes/ini_set.inc';
include __DIR__ . '/../../assets/includes/translit.inc';


$aAdditionalData['projects'] = json_decode( file_get_contents(__DIR__ . '/../_additional_data/projects.json', true), true );
$aAPIData['apartments'] = json_decode(file_get_contents(__DIR__ . '/../_data_from_api/apartments.json', true), true);
//die('<pre>' . print_r($aAPIData['apartments']) . '</pre>');


$aCompleted = json_decode(file_get_contents(__DIR__ . '/../_data_from_crm/completed.json', true), true);
//die('<pre>' . print_r($aCompleted, true) . '</pre>');

$a = [];
foreach($aCompleted as $building){ //die("$building[MountingBeginning] => " . $building['MountingBeginning']);
    $aCurrentProjectAdditionalData = $aAdditionalData['projects'][ $building['BuildingGroupID'] ]; //die('<pre>' . print_r($aCurrentProjectAdditionalData, true) . '</pre>');

    #< data
    $a_addressNumber = explode('-', $building['AddressNumber']);
    (count($a_addressNumber) > 1) && ($building['AddressNumber'] = trim($a_addressNumber[0]));

    $a_deliveryPeriod = explode('к', "{$building['DeliveryPeriod']}");
    #> data...

    foreach((array)$building['Sections']['Section'] as $section){ //die('=> <pre>' . print_r($section, true) . '</pre>');
        foreach((array)$section['Apartments'] as $apartment){
            #< ...filter
            //if($apartment['StatusCode'] != 4) continue;
            #> filter

            #< ...data
            $aImages = ['apartment' => [0 => null, 1 => null]];
            foreach($aAPIData['apartments'][ (string)$apartment['ArticleId'] ]['images'] as $k => $v){
                if($v == '') continue;

                if($k == 'plain'){
                    $aImages['apartment'][0] = $v;
                } else if($k == 'floor'){
                    $aImages['apartment'][1] = $v;
                } else{
                    $aImages['apartment'][] = $v;
                }
            }
            #
            //ksort($aImages['apartment']);

            $livingArea = $kitchenArea = 0;
            //die('> ' . print_r($apartment['RoomsProject']['Room'], true));
            foreach((array)$apartment['RoomsProject']['Room'] as $room){ //die('> ' . print_r($room, true));
                if(mb_stripos($room['TypeName'], 'комната') !== false){ //$room['TypeName'] == 'Жилая комната' or $room['TypeName'] == 'Комната'
                    $livingArea += $room['AreaProject'] * 1;
                } else if(mb_stripos($room['TypeName'], 'кухня') !== false){ //$room['TypeName'] == 'Кухня'
                    //die('> ' . $room['AreaProject']);
                    $kitchenArea += $room['AreaProject'] * 1;
                }
            }

            $totalArea = $apartment['Quantity'];
            if(isset($apartment['SpaceDesign']) && $apartment['SpaceDesign'] !== ''){
                $totalArea = $apartment['SpaceDesign'];
            }
            if(isset($apartment['SpaceBti']) && $apartment['SpaceBti'] !== ''){
                $totalArea = $apartment['SpaceBti'];
            }

            $number = 0;
            if(isset($apartment['BtiNumberTxt']) && $apartment['BtiNumberTxt'] !== ''){
                $number = $apartment['BtiNumberTxt'];
            }
            if(isset($apartment['BtiNumber']) && $apartment['BtiNumber'] !== ''){
                $number = $apartment['BtiNumber'];
            }
            if(isset($apartment['BeforeBtiNumberTxt']) && $apartment['BeforeBtiNumberTxt'] !== ''){
                $number = $apartment['BeforeBtiNumberTxt'];
            }
            if(isset($apartment['BeforeBtiNumber']) && $apartment['BeforeBtiNumber'] !== ''){
                $number = $apartment['BeforeBtiNumber'];
            }

            $houseNumber = is_array($building['AddressNumber']) ? $building['AddressNumber'][0] : (string)$building['AddressNumber'];
            #> data

            $a[ (string)$apartment['Code'] ] = [
                'project' => [
                    'crm-id' => (string)$building['BuildingGroupID'],
                    'title' => $aCurrentProjectAdditionalData['title'], //$apartment['AddressName'],
                    'name' => $building['BuildingGroupName'],
                    'e-mail' => stripos((string)$building['AddressBuild'], 'Москва') ? 'ozmsk@ingrad.com' : 'ozmo@ingrad.com'
                ],
                'house' => [
                    'crm-id' => (string)$building['BuildingID'],
                    'number' => $houseNumber,
                    'title' => (string)$building['Name'],
                    'floors count' => abs($building['FloorsCount']),
                    'region' => (string)$building['AddressRegion'],
                    'address' => (string)$building['AddressBuild'],
                    'built' => [
                        'year' => count($a_deliveryPeriod) > 1 ? (int)$a_deliveryPeriod[1] : '',
                        'quarter' => count($a_deliveryPeriod) > 1 ? (int)$a_deliveryPeriod[0] : ''
                    ]
                ],
                'section' => [
                    'crm-id' => (string)$section['SectionID'],
                    'number' => (int)$section['SectionNumber']
                ],
                'crm-id' => (string)$apartment['ArticleId'],
                'api-id' => (int)$aAPIData['apartments'][ (string)$apartment['ArticleId'] ]['api-id'],
                'article type' => (string)$apartment['ArticleType'],
                'code' => [
                    'ru' => $apartment['Code'],
                    'translite' => translit($apartment['Code'])
                ],
                'crm-membership' => (string)$apartment['AddressName'],
                'price' => [
                    'new' => (double)$apartment['DiscountMax'],
                    'old' => (double)$apartment['Price'],
                ],
                'area' => [
                    'living' => (double)$livingArea,
                    'kitchen' => (double)$kitchenArea,
                    'total' => (string)$totalArea
                ],
                'status' => [
                    'code' => $apartment['StatusCode'],
                    'title' => $apartment['StatusCodeName']
                ],
                'count on floor' => (int)$section['ObjectsCount'],
                'finishing' => trim(mb_strtolower(is_array($apartment['FinishTypeId']) ? '' : $apartment['FinishTypeId'])),
                'floor number' => (int)$apartment['Floor'],
                'images' => (array)$aImages['apartment'],
                'number' => $number,
                'number on floor' => (int)$apartment['PlatformNumber'],
                'rooms count' => (int)$apartment['Rooms'],
                'rooms data' => (array)$apartment['RoomsProject']['Room'],
                'townhouse' => (boolean)$apartment['TownHouse'],
                'type' => (string)$apartment['ArticleSubType'],
                'url' => "https://www.ingrad.ru{$aAPIData['apartments'][ (string)$apartment['ArticleId'] ]['link']}",
                'unique' => ((string)$apartment['isUnique'] == 'true') ? true : false,
                'building project' => mb_strtolower(current($apartment['BuildingProjects']))
            ];
        }
    }
}


require __DIR__ . '/templates/ym.inc';
echo getYM($a);
