<?php
session_start();


class ApiDevbase
{
    public function curl($method, $url, $data = null)
    {

        $new_url = 'https://api-devbase.vip-saga.com.ua/v1/' . $url;

        if ($method == "GET") $new_url .= '?' . http_build_query($data);

        $curlOptions = [
            CURLOPT_URL => $new_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ];

        if ($method == "POST") {

            $curlOptions[CURLOPT_CUSTOMREQUEST] = 'POST';
            $curlOptions[CURLOPT_POSTFIELDS] = json_encode($data);
            $curlOptions[CURLOPT_HTTPHEADER] = array('Content-Type: application/json');
        }

        $ch = curl_init();

        curl_setopt_array($ch, $curlOptions);

        $result = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $code = (int)$code;

        return ['data' => json_decode($result, true), 'code' => $code];
    }
}

function getToken(){
    $devbase = new ApiDevbase();
    $curTime = time();
//    $value = get_field( "block_2", 'option');
    $value['token'] = $_SESSION['token'];

    $data = $devbase->curl('GET', 'apartments',
        [
            'access_token'=>$value['token'],
            'option' => [
                'id'=> 1
            ]
        ]);

    if(($curTime>$value['token_time']) || ($data['code'] == 400 || $data['code'] == 403)){
//    if($data['code'] == 400 || $data['code'] == 403){
        $data = $devbase->curl('POST', 'authentication', ['api_key'=>'11c5fcebaa65b560eaf06c3fbeb481ae44b8d611']);

        $_SESSION['token'] = $data['data']['access_token'];
        $_SESSION['time'] = $data['data']['expires_exp'];
    }

}
//функции для 3D в json без отрисовки страницы

function getFlatById($id){
//    $devbase = new ApiDevbase();
    getToken();
    $value['token'] = $_SESSION['token'];
    $devbase = new ApiDevbase();
//    $value = get_field( "block_2", 'option');
    $data = $devbase->curl('GET', 'apartments',
        [
            'access_token'=>$value['token'],
            'option' => [
                'id'=>$id
//                'id'=>$_POST['id']
            ]
        ]);
    $json = json_encode($data['data']['data']);
    echo $json;
    exit();
}
function getFlats(){
    getToken();
    $value['token'] = $_SESSION['token'];
    $devbase = new ApiDevbase();
    $data = $devbase->curl('GET', 'apartments',
        [
            'access_token'=>$value['token'],
            'option' => [
                'project_id' => 1,
                'type_object' => 1
            ]
        ]);
    $json = json_encode($data['data']['data']);
    echo $json;
    exit();
}
function getFloor($dom,$floor){
    getToken();
    $devbase = new ApiDevbase();
    $value['token'] = $_SESSION['token'];
    $data = $devbase->curl('GET', 'floor',
        [
            'access_token'=>$value['token'],
            'option' => [
                'project_id' => 1,
                'build' => $dom,
                'floor' => $floor,
            ]
        ]);
    return $data;
//    echo json_encode($data);

}
function createSvg(){
    $data = getFloor($_POST['house'], $_POST['floor']);
    $url = 'https://boston-wp.smarto.agency/flat/';
//    $img_svg = get_template_directory_uri().'/assets'.$data['data']['img'];
    $img_svg = 'https://boston-wp.smarto.agency/wp-content/themes/boston/assets/img/projects/1/color-floors/6.png';


    $size = getimagesize($img_svg);
    if ($size[0]<=1200){

        $cssSize = $size[0];
    } else {

        $cssSize = 1250;
        $keff = $size[0]/$cssSize; // коэф. для просчета высоты, делим реальную ширину на 1250
    }

    $cssSizeHeight = ($cssSize != 1250)? $size[1] : $size[1]/$keff;
    $svg_block = '<image x="0" y="0" height="100%" width="100%" style="overflow:visible; transform: scaleY(0.987) translateY(-4%);" xlink:href="'.$img_svg.'"/>'
        .'<style>.plan-appartment{opacity:0.55;} .plan-appartment:hover{opacity:0.8;}</style>
                    <g style="transform: scale(0.39, 0.385) translateX(-3%);">';
    if(!empty($data['data']['dataList'])):
        foreach ($data['data']['dataList'] as $item){
            $cords = $item['sorts'];
            $svg_block.= '<polygon '.$style.'  class="flat-link-path '.'
						 '.$class.'" points="'.$cords.'"
						  data-name="'.$item['type'].'"
						   data-link="'.$item['type'].'"
						    data-rooms="'.$item['rooms'].'"
						     data-total="'.$item['all_room'].'"
						      data-living="'.$item['life_room'].'"
						      data-id="'.$item['id'].'" ></polygon>';
        }
    endif;

    $svg_block.= '</g></svg>';
    $svg_block = '<svg  viewBox="0 0  '.$cssSize.' '.$cssSizeHeight*1.1.'" id="floor"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">'.$svg_block ;
//    return $svg_block;

    echo json_encode($svg_block);
    exit();
//    return $svg_block;
}



// функции для 3D с отрисовкой
function createSvg2($dom,$floor,$app){
    $data = getFloor($dom, $floor);
//    $data = getFloor($_POST['house'], $_POST['floor']);
    $url = 'https://boston-wp.smarto.agency/flat/';
    $img_svg = 'https://boston-wp.smarto.agency/wp-content/themes/boston/assets'.$data['data']['img'];
//    $img_svg = get_template_directory_uri().'/assets'.$data['data']['img'];
//    $img_svg = 'https://boston-wp.smarto.agency/wp-content/themes/boston/assets/img/projects/1/color-floors/6.png';


    $size = getimagesize($img_svg);
    if ($size[0]<=1200){

        $cssSize = $size[0];
    } else {

        $cssSize = 1250;
        $keff = $size[0]/$cssSize; // коэф. для просчета высоты, делим реальную ширину на 1250
    }

    $cssSizeHeight = ($cssSize != 1250)? $size[1] : $size[1]/$keff;
    $svg_block = '<image x="0" y="0" height="100%" width="100%" style="overflow:visible;transform: rotate(90deg) scale(1.65) translateX(4%);transform-origin: center;" xlink:href="'.$img_svg.'"/>'
        .'<style>.plan-appartment{opacity:0.55;} .plan-appartment:hover{opacity:0.8;}</style>
                    <g style="transform: scale(0.395) rotate(90deg) translateY(-166%) translateX(-6%);transform-origin: center;">';
    if(!empty($data['data']['dataList'])):
        foreach ($data['data']['dataList'] as $item){
            if($app == $item['id']) {
                $class = ' current__flat ';
            }else{
                $class = '';
            }

            $cords = $item['sorts'];
            $svg_block.= '<polygon '.$style.'  class="flat-link-path '.$class.'"
                         points="'.$cords.'"
						  data-name="'.$item['type'].'"
						   data-link="'.$item['type'].'"
						    data-rooms="'.$item['rooms'].'"
						     data-total="'.$item['all_room'].'"
						      data-living="'.$item['life_room'].'"
						      data-flat_id="'.$item['id'].'" ></polygon>';
        }
    endif;

    $svg_block.= '</g></svg>';
    $svg_block = '<svg  viewBox="0 0 '.$cssSizeHeight*1.1.' '.$cssSize.'" style="height: 170px;" id="floor"   xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">'.$svg_block ;
//    return $svg_block;

//    echo json_encode($svg_block);
//    exit();
    return $svg_block;
}

function FlatX($id){
//    $id = 285;
    getToken();
    $value['token'] = $_SESSION['token'];
    $devbase = new ApiDevbase();
    $data = $devbase->curl('GET', 'apartments',
        [
            'access_token'=>$value['token'],
            'option' => [
                'id'=>$id
            ]
        ]);


//    $_POST['house'], $_POST['floor']

    $floor = createSvg2($data['data']['data'][0]['build'],$data['data']['data'][0]['floor'],$id);
    $flatPage = "";
    $flatPage .= "";
    $flatPage .= "<div class='s3d-popup__mini-plan js-s3d-popup__mini-plan'>".$floor."
        <div class='close s3d-popup__mini-plan__close js-s3d-popup__mini-plan__close'><span></span><span></span></div></div> 
    <div class='flat-parameters'>
                <div class='s3d-flat__param__list s3d-flat__param__list-bolt'>
                    <div class='s3d-flat__param s3d-flat__param--title'>Параметри:</div>
                    <div class='s3d-flat__param'>Поверх:<span class='s3d-flat__param-digit'>
                 ".$data['data']['data'][0]['floor']."</span></div>
                    <div class='s3d-flat__param'>Загальна:<span class='s3d-flat__param-digit'>
                 ".$data['data']['data'][0]['all_room']."м<sub>2</sub></span></div>
                    <div class='s3d-flat__param'>Житлова:<span class='s3d-flat__param-digit'>".$data['data']['data'][0]['life_room']." м<sub>2</sub></span></div>
                </div>
                <div class='s3d-flat__param__list'>";
    foreach ($data['data']['data'][0]['properties'] as $room){
        $flatPage .="<div class='s3d-flat__param'>
                        <span>".$room['property_name']."</span>
                        <span class='s3d-flat__param-digit'>".$room['property_flat']." м<sub>2</sub></span>
                    </div>";
    }?>
    <? $flatPage .= " </div>
              </div>
              <div class='flat-buttons-group'>
  <a class='button flat-price-info-button form-js s3d-flat__button'>
   <span class='button_text'>Дізнатись ціну</span>
  </a>
  <a class='transparent-button flat-transparent-pdf s3d-flat__button' target='_blank' href='/pdf?flat=".$data['data']['data'][0]['id']."&floor=".$data['data']['data'][0]['floor']."'>завантажити PDF</a>
</div>
<div class='flat-group2'>
    <div class='mini-floor-plan'>".$floor."
    </div>
</div>";
    $jSD = $data['data']['data'];
    $dataAtr = '';
    foreach ($jSD[0] as $key=>$item){
        $dataAtr .= ' data-'.$key.'="'.$item.'" ';
    }
//    $imgPathVerOne = str_replace('(Nomer-versii_-2)','(Nomer-versii_-1)',$data['data']['data'][0]['img_web']);
//    $imgPathVerOne = str_replace('(Nomer-versii_-3)','(Nomer-versii_-1)',$imgPathVerOne);
    $imgPathVerOne = $data['data']['data'][0]['img_web'];
    $flatPage .="
            <a class='js-flat-plan-mfp' href='https://boston-wp.smarto.agency/wp-content/themes/boston/assets".$data['data']['data'][0]['img_big']."'  ".$dataAtr." >
                <img class='flat-plan' src='https://boston-wp.smarto.agency/wp-content/themes/boston/assets".$data['data']['data'][0]['img_big']."'  ".$dataAtr." title='foto' alt='foto'/>
            </a>
            </div>
        ";
    $flatPage .= "<script>$('.js-flat-plan-mfp').magnificPopup({
    type: 'image',
    showCloseBtn: true,
    callbacks: {
        open: function() { $('.js-s3d__slideModule').addClass('no-scroll')},
        close: function() { $('.js-s3d__slideModule').removeClass('no-scroll')}
        }
     })</script>";
//return $flatPage;
    $json = json_encode($flatPage);
    echo $json;
    exit();
}

function floorX()
{
    $data = getFloor($_POST['house'], $_POST['floor']);
//    return $data;

    $FloorData = $data['data'];
    $floorPage = '';
    $floorPage .= "<div class='floor-wrapper custom-scroll' id='js-floor'>";
    $img_svg = 'https://boston-wp.smarto.agency/wp-content/themes/boston/assets'.$data['data']['img'];
    $size = getimagesize($img_svg);
    if ($size[0]<=1200){
        $cssSize = $size[0];
    } else {

        $cssSize = 1250;
        $keff = $size[0]/$cssSize; // коэф. для просчета высоты, делим реальную ширину на 1250
    }
    $cssSizeHeight = ($cssSize != 1250)? $size[1] : $size[1]/$keff;

    $svg = '<svg class="s3d-floor__style-svg" id="floor--svg" '.' viewBox="0 0 '.$cssSize.' '.$cssSizeHeight*1.1.'" version="1.0" xmlns="http://www.w3.org/2000/svg" >
                                <image xlink:href="'.$img_svg.'" x="0" y="0" height="100%" width="100%" style="transform: translateY(-4%);"></image>';



    $cnt = 0;

    foreach ($FloorData['dataList'] as $key=>$polygon) {
//        if (!$polygon->sorts) continue;
        $filter_poligon = (isset($polygon['filter']) && !$polygon['filter'])? ' entrance-flats__item_floor--not-active' : ' entrance-flats__item_floor--active';

        $img_flat = 'https://boston-wp.smarto.agency/wp-content/themes/boston/assets/img/projects/1/'.$polygon['build'].'/'.$polygon['img'];
        if(!empty($polygon['sorts'])) {
            $svg .= '<g   style="-webkit-transform: scale(0.39, 0.385) translateX(-3%);-ms-transform: scale(0.39, 0.385) translateX(-3%);transform: scale(0.39, 0.385) translateX(-3%);"
							  data-type = "' . $polygon['type'] . 'К"
							  data-rooms = "' . $polygon['rooms'] . 'К"
							  data-image = "' . $img_flat . '"
							  data-color = "' . $polygon['status_color'] . '"
							  data-price_m="' . $polygon['price_m2'] . '"
							  data-square="' . $polygon['all_room'] . '"
							  data-living="' . $polygon['life_room'] . '"
							  data-price="' . $polygon['price'] . '"
							  data-floor="' . $polygon['floor'] . '"
							  data-flat_id="' . $polygon['id'] . '"
							  data-num="' . (($polygon['number']) ? "№ {$polygon['number']}" : '') . '"
						
							  data-text="' . $polygon['statu_text'] . '"
							  
							class="entrance-flats__item-js js-hover-mini-flat-item js-open-tab plan-floor-appartment">
											<polygon class="floor-svg-polygon fill-' . $polygon['status_color'] . $filter_poligon . '" points="' . $polygon['sorts'] . '"></polygon>

											<symbol id="qwery' . $cnt . '" viewBox="0 0 80 80" class="svg-tip-plan-floor" >
												<rect x="0.5" y="15.5" width="80" height="39" class="st0 ' . $polygon['status_color'] . '" />
												<rect x="24" y="0" width="30" height="30" class="st1 ' . $polygon['status_color'] . '" />

												<text x="30" y="19" transform="matrix(1 0 0 1 30.71 20)" class="st2 st3 st4">' . $polygon['rooms'] . 'к</text>
												<text x="15" y="45" transform="matrix(1 0 0 1 11.15 46)" class="st3 st4">' . $polygon['all_room'] . 'м<tspan y="-3.5" style="font-size: 0.7em" class="st3 st4">2</tspan></text>
//
											</symbol>

											<use x="0" y="0" width="340px" height="340px" style="width:340px; height:340px;"  xlink:href="#qwery' . $cnt . '" class="svg-tip-plan-floor--small svg-tip-plan-floor-' . $cnt . '"><tspan y="-3.5" style="font-size: 0.7em" class="st3 st4">2</tspan></use>
									  </g>';
            $cnt++;
        }

    }

    $svg.= '</svg>';

//    echo $svg;

    $floorPage .= $svg.'</div>';

//    return $floorPage;

    $json = json_encode($floorPage);
    echo $json;
    exit();
}


switch ($_POST['action']) {
    case 'getFloor':
//        createSvg();
        floorX();
        break;
    case 'getFlatById':
//        getFlatById($_POST['id']);
        FlatX($_POST['id']);
        break;
    case 'getFlats':
        getFlats();
        break;
}

//конец функций для 3D
/**
 * @brief Транслитерация для цветных планировок
 * @param [array] $filter
 * @return array
 */
function rus2translit($string) {
    $converter = array(
        'а' => 'a',   'б' => 'b',   'в' => 'v',
        'г' => 'g',   'д' => 'd',   'е' => 'e',
        'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
        'и' => 'i',   'й' => 'y',   'к' => 'k',
        'л' => 'l',   'м' => 'm',   'н' => 'n',
        'о' => 'o',   'п' => 'p',   'р' => 'r',
        'с' => 's',   'т' => 't',   'у' => 'u',
        'ф' => 'f',   'х' => 'h',   'ц' => 'c',
        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
        'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

        'А' => 'A',   'Б' => 'B',   'В' => 'V',
        'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
        'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
        'И' => 'I',   'Й' => 'Y',   'К' => 'K',
        'Л' => 'L',   'М' => 'M',   'Н' => 'N',
        'О' => 'O',   'П' => 'P',   'Р' => 'R',
        'С' => 'S',   'Т' => 'T',   'У' => 'U',
        'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
        'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
        'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
        'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
        'Є' => 'Ye',
    );
    return strtr($string, $converter);
}
function str2url($str) {
    // переводим в транслит
    $str = rus2translit($str);
    // в нижний регистр
    $str = strtolower($str);
    // заменям все ненужное нам на "-"
    $str = preg_replace('~[^-a-z0-9_]+~u', '-', $str);
    // удаляем начальные и конечные '-'
    $str = trim($str, "-");
    return $str;
}
