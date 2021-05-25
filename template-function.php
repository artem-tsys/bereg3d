<?php

/**
 * @brief Підключає верхнэ меню і вставляю додатковий текст
 * @param [array] $option
 */
function get_top_menu($option){

	$menu = wp_nav_menu($option).'endline';

	$menu = str_replace('<ul id="menu-', '<div id="menu-', $menu);
	$menu = str_replace('</li>', '</div>', $menu);
	$menu = str_replace('</a></div>', '</a></li>', $menu);

	echo str_replace('</ul>endline', $text.'</div>', $menu);
}



/**
 * @brief Підключає bread crumbs
 * @param [string] $class
 * @param [array] $lis
 */
function bread_crumbs($class = '', $lis = [], $li_replase = [], $title = null) {
    global $post;

    if ($title == null) $title = get_the_title();

    /**Не показувати bread crumbs на сторінках*/
    $not_visible = [];

    /**Сторінка архівів*/
    $archive_page = [26=>'news',90=>'choose-flat',32=>'choose-floor'];

    if(is_archive() && in_array($post->post_type, $archive_page)){

        $post = get_post(array_search($post->post_type, $archive_page));
    }

    if (in_array($post->ID, $not_visible)) return;

    $li =[
        [get_page_link(8), __('Головна','bereg')],
        ['', get_the_title()]
    ];


    foreach ($lis as $key=>$t) {

        array_splice($li, $key, 0, [$t]);
    }

    if (!empty($li_replase)) {

        foreach ($li_replase as $key => $t) {

            $li[$key] = $t;
        }
    }

    echo '<div class="breadcrumps '.$class.'">';

    foreach ($li as $key=>$t) {

        $text = ($t[0] != null)? '<a class="breadcrumps__link" href="'.$t[0].'">'.$t[1].'</a> '
                                   :
                                ' <a class="breadcrumps__link breadcrumps__current-link" >'.$t[1].'</a>';

        echo $text;
    }

    echo   '</div><!-- /.breadcrumb -->';

}

function bread_crumbs2($class = '', $lis = [], $li_replase = [], $title = null) {
    global $post;

    if ($title == null) $title = get_the_title();

    /**Не показувати bread crumbs на сторінках*/
    $not_visible = [];

    /**Сторінка архівів*/
    $archive_page = [26=>'news',90=>'choose-flat',32=>'choose-floor'];

    if(is_archive() && in_array($post->post_type, $archive_page)){

        $post = get_post(array_search($post->post_type, $archive_page));
    }

    if (in_array($post->ID, $not_visible)) return;

    $li =[
        [get_page_link(8), __('Головна','bereg')],
        ['', get_the_title()]
    ];


    foreach ($lis as $key=>$t) {

        array_splice($li, $key, 0, [$t]);
    }

    if (!empty($li_replase)) {

        foreach ($li_replase as $key => $t) {

            $li[$key] = $t;
        }
    }

    $a =  '<div class="breadcrumps '.$class.'">';

    foreach ($li as $key=>$t) {

        $text = ($t[0] != null)? '<a class="breadcrumps__link" href="'.$t[0].'">'.$t[1].'</a> '
                                   :
                                ' <a class="breadcrumps__link breadcrumps__current-link" >'.$t[1].'</a>';

        $a .= $text;
    }

    $a .=   '</div><!-- /.breadcrumb -->';
    return $a;

}

/**
 * @brief Обрізає текс до заданої довжини і вивидить його на єкран
 * @param [string] $text
 * @param [int] $symbol_amount
 */
function the_truncated_post($text, $symbol_amount) {

	echo get_truncated_post($text, $symbol_amount);
}

/**
 * @brief Обрізає текс до заданої довжини
 * @param [string] $text
 * @param [int] $symbol_amount
 * @return strint
 */
function get_truncated_post($text, $symbol_amount) {

	$filtered = strip_tags( preg_replace('@<style[^>]*?>.*?</style>@si', '', preg_replace('@<script[^>]*?>.*?</script>@si', '', $text)) );
	return substr($filtered, 0, strrpos(substr($filtered, 0, $symbol_amount), ' ')) . '...';
}




/**
 * @brief Формує дані полів користувача, створених через плагін
 * @param [array] $ids
 * @return object
 */
function get_data_field($ids = []){

	global $post;

	$data =(object)[];

	if (!empty($ids)) {

		foreach ($ids as $id) {

			$data->{str_replace('-', '_', $id)} = get_field($id, $post->ID);
		}
	}

	return $data;
}


/**
 * @brief Обертаэ рядок в <p></p>
 * @param [string] $str
 * @return string
 */
function get_p($str = null, $class = null) {

	$class = ($class !== null)? 'class="'.$class.'"' : '';

	return preg_replace('~^(?![\r\n])(.*?)\r?\n?$~m','<p '.$class.'>$1</p>',$str);
}

/**
 * @brief Обертаэ рядок в <span></span>
 * @param [string] $str
 * @return string
 */
function get_span($str = null, $class = null) {

	$class = ($class !== null)? 'class="'.$class.'"' : '';

	return preg_replace('~^(?![\r\n])(.*?)\r?\n?$~m','<span '.$class.'>$1</span>',$str);
}

/**
 * @brief Обертаэ рядок
 * @param [string] $str
 * @return string
 */
function get_formated_string($str = null, $class = null) {

	if (preg_match_all('|#ul#(.*)#/ul#|sei', $str, $arr)){

		$class = ($class !== null)? 'class="'.$class.'"' : '';

		foreach ($arr[1] as $key => $t) {

			$arr[1][$key] = '<ul '.$class.'>'.get_li($t).'</ul>';


			$str = str_replace(['#ul#', '#/ul#'], '', str_replace($t, "#{$key}#", $str));
		}
	}

	$str = get_data_text_array($str);

	if (isset($arr[1])) {

		foreach ($arr[1] as $key => $t) {

			if (($id =array_search("#{$key}#", $str)) !== false) $str[$id] = $t;
		}
	}

	return $str;
}


/**
 * @brief Форматуэ дату
 * @param [string] $date
 * @return string
 */
function get_date_format($date){
	$dateTime = new DateTime($date);
	$dateTime = $dateTime->format('Y-m-d');

	return $dateTime;
}


/**
 * @brief Підбір новин
 * @param [int] $postnumbers
 * @param [int] $offset
 * @return array
 */

function LoadingNews($postnumbers, $offset = 0, $type='news') {
    GLOBAL $post;
    global $wp_query;
    $postnumbers=6;

    //$countNews = $wp_query->post_count;

    if($post->post_type){
        $type = $post->post_type;
    }

    $setPage = (@$_POST['page'])? $_POST['page'] : $setPage=1;

    $myposts = get_posts(
        array(
            'numberposts' => -1,
            'post_type' => $type,
        ));

    $num=count($myposts);

    $offset = ($postnumbers>$num)? 0 : ($setPage-1)*$postnumbers;

    if($_GET['s']){
        //$myposts = $wp_query->query_vars->ReaNews;
        if (have_posts()) :
            while (have_posts()) : the_post();
                $sortNews[] = $post;
            endwhile;
        endif;
        // $myposts = $sortNews;
    }
//else {
    $myposts = get_posts(
        array(
            'numberposts' => -1,
            'post_type' => $type,
            'posts_per_page' => $postnumbers,
            'offset' => $offset
        ));
//}
    $pagination='';

    $app=Pagination::createPagination();
    $app->options= array('itemsCount' => $num, 'itemsPerPage' => $postnumbers, 'currentPage' =>$setPage );
    $app->PaginationAdd();

    if (count($app->buttons)>0) {

        ob_start();

        require (__DIR__.'/../template-parts/includes/pagination.php');
        $pagination = ob_get_clean();
    }

    return array('ReaNews'=>$myposts, 'num'=>$num, 'pagination'=>$pagination, 'qwe'=>$myposts);
}

function LoadingProjects($postnumbers = NEWS_ON_PAGE, $offset = 0) {
    GLOBAL $post;
    if ($_POST['page'])
    {
        $setPage=$_POST['page'];
    }
    else
    {
        $setPage=1;
    }
    $myposts = get_posts(
        array(
            'numberposts' => -1,
            'post_type' => 'project',
        ));

    $num=count($myposts);

    if ($postnumbers>$num)
    {
        $offset=0;
    }
    else
    {
        $offset=($setPage-1)*$postnumbers;
    }

    $myposts = get_posts(
        array(
            'numberposts' => -1,
            'post_type' => 'project',
            'posts_per_page'=>$postnumbers,
            'offset'=>$offset
        ));


    return array('ReadProjects'=>$myposts);
}

//function paginationNews() {
//    $ReaNews = LoadingNews(NEWS_ON_PAGE, 0);
//
//
//    if (count($ReaNews['ReaNews'])) {
//
//        set_query_var('ReaNews', $ReaNews);
//
//        get_template_part('/includes/list-block');
//
//        echo $ReaNews['pagination'];
//
//    }
//
//    if (isset($_POST['page'])) die();
//}



/**
 * @brief Вибирає квартири з БД
 * @param [array] $filter
 * @return array
 */
function apps($filter = null){

	$posts = get_posts([
		'numberposts' => 999,
		'category'    => 1,
		'orderby'     => 'date',
		'order'       => 'DESC',
		'post_type'   => 'post',
		'post_status' => 'publish',
		'suppress_filters' => true,
	]);

		foreach ($posts as $pageFloor) {

			$floor = get_field('floor', $pageFloor->ID);
			$dom = get_field('dom', $pageFloor->ID);

			$filters = (object)['floor'=>$floor, 'dom'=>$dom];


			if ($filter) {

				$key  = key($filter);

				if (!isset($filters->{$key}) || $filters->{$key} != $filter[$key]) continue;
			}

			$flat = get_field('apartment', $pageFloor->ID);

			if (!is_array($flat)) $flat = [];

			array_walk($flat, "option_flat", [$floor, $dom, $pageFloor->ID]);

			$result = (!isset($result))? $flat : array_merge($result,$flat);
		}


	if (isset($result)){
		// Сортируем данные по floor
		array_multisort(array_column($result, 'floor'), SORT_ASC, $result);
	} else {

		$result = null;
	}


    return $result;
}


/**
 *  @brief Вибирає квартири з БД
 *
 *  @return array
 */
function appFindId($id, $type){

	$pageFloor = get_post($id);

		if ($pageFloor) {
			$floor = get_field('floor', $pageFloor->ID);
			$dom = get_field('dom', $pageFloor->ID);

			$flat = get_field('apartment', $pageFloor->ID);


			if (!is_array($flat)) $flat = [];

			array_walk($flat, "option_flat", [$floor, $dom, $pageFloor->ID]);

			foreach ($flat as $key=>$t) {

				if ($t['type'] != $type) unset($flat[$key]);
			}

			$result = (!isset($result))? $flat : array_merge($result,$flat);
		}

	if (isset($result) && $result){
		// Сортируем данные по floor
		array_multisort(array_column($result, 'floor'), SORT_ASC, $result);

		$result = $result[0];
	} else {

		$result = null;
	}


    return $result;
}


/**
 * @brief Вибирає всі поверхи проекту
 * @param [array] $flats
 * @return array
 */
function get_floors($flats = null) {

	if (!$flats) $flats = apps();


	foreach ($flats as $flat) {

		if (isset($floor[$flat['floor']]))	{

			$floor[$flat['floor']]->counts++;
			continue;
		}

		$floor[$flat['floor']] = (object)['floor'=>$flat['floor'], 'counts'=>1, 'url'=>get_page_link(19)."?floor={$flat['floor']}"];
	}

	return $floor;
}


/*
 * @brief Call-back функція. Застосовує задану функцію до кожного елементу масиву
 * @param [array] $flat
 * @param [int] $flat
 * @param [array] $option
 * @return array
 */
function option_flat(&$flat, $key, $option)
{

    /*Якщо вибраний шаблон планування, підміняємо на дані з нього*/
    if ($flat['room']['get_room'] != null) {

        $flat['room']['room'] = get_field('room', $flat['room']['get_room']->ID);
        $flat['room']['redevelopment'] = get_field('redevelopment', $flat['room']['get_room']->ID);
    }


    $flat['livSquare'] = $flat['square'] = 0;

    if (isset($flat['room'])) {

        $flat['livSquare'] = livSq($flat['room']['room']);
        $flat['square'] = SquareAll($flat['room']['room']);
    }

	$flat['rooms'] = substr($flat['type'], 5, 1);
    if(is_numeric ($flat['rooms'])){

    }else{
        $flat['rooms'] = substr($flat['type'], 6, 1);
    }

	$flat['url'] = get_page_link(15)."?flat={$option[2]}&type={$flat['type']}";
	$flat['floor'] = $option[0];
	$flat['dom'] = $option[1];
	$flat['post_ID'] = $option[2];

    $img_wp = ($flat['room']['img'])? $flat['room']['img'] : get_field('img', @$flat['room']['get_room']->ID);


    if ($img_wp) {

        $img_wp = ['img' => $img_wp['sizes']['large'], 'alt' => ($img_wp['alt'])? $img_wp['alt'] : '' ];
    } else {

        $img_wp = ['img' => get_bloginfo('template_url').'/assets/images/no-image.png', 'alt' => '' ];
    }

    $flat['img'] = $img_wp;

}


/*
 * @brief Перевіряє чи існує вказане зображення
 * @param [array] $option
 * @return string
 */
function get_img_full_path($option){

	$img = __DIR__."/../../../uploads/layouts/dom{$option['dom']}/{$option['typ']}.png";

	//$img = (!file_exists($img ))? "/wp-content/uploads/layouts/no-image.png" : "/wp-content/uploads/layouts/dom{$option['dom']}/{$option['floor']}/{$option['typ']}.png";
	$img = (!file_exists($img ))? "/wp-content/uploads/layouts/no-image.png" : "/wp-content/uploads/layouts/dom{$option['dom']}/{$option['typ']}.png";



	return $img;
}


/*
 * @brief Підраховує загальну площу
 * @param [array] $app
 * @return float
 */
function SquareAll($app){

	$sumR = 0;

	if (empty($app))   return $sumR;

    foreach ($app as $room) {

        $sumR+= floatval($room['square']);
    }

    return $sumR;
}


/*
 * @brief Підраховує житлову площу
 * @param [array] $app
 * @param [array] $array_living
 * @return float
 */
function livSq($app, $array_living = ['004','005','006','007','008','027','035','036','042','043','044']){

	$sumR = 0;

	if (empty($app))   return $sumR;

    foreach ($app as $room) {

        if(in_array($room['type'],$array_living)) {
            $sumR+= floatval($room['square']);
        }
    }

    return $sumR;
}


/*
 * @brief Виводить 404 сторінку
 */
function set_404(){
	status_header(404);
	include(get_query_template('404'));
	die();
}

/**
 * @brief (Змінює розмір зображення і оптимізовує їх)
 * @parem [sting] $img
 * @parem [sting] $table
 * @parem [int] $width
 * @parem [int] $heigt
 * @return string
 */
function get_img($img, $typ = 'resize', $width = '', $heigt = '' ) {

    if (empty($img)) return $img;


	$img = str_replace(get_site_url(), '', $img);


	if (!empty($width) || !empty($heigt)) {
        $file = explode(".", $img);
        $dir = get_theme_root().'/../../';

        $new_name = $file[0].'-'.$width.'x85-'.$heigt;

		if (strtolower($file[1]) == 'svg') return $img;

		if (strtolower($file[1]) == 'jpeg') {

            $file[1] = 'jpg';
            $oldImg = $img;

            $img = $file[0].'.'.$file[1];

            if (!file_exists($dir.$img)) copy ($dir.$oldImg, $dir.$file[0].'.jpg');
        }

        if (!file_exists($dir.$new_name.'.'.strtolower($file[1]))) {

            $imgs = new AcResizeImage($dir.$img);

            if (empty($width)) $width = false;
            if (empty($heigt)) $heigt = false;

            switch($typ){
                case 'resize':
                    $imgs->resize($width, $heigt);
                    break;
                case 'thumbnail':
                    $imgs->thumbnail($width, $heigt,2);
                    break;
                default:
                    $imgs->resize($width, $heigt);
            }

            $imgs->save($dir, $new_name, strtolower($file[1]), false, 95); //сохранили
        }

        return $new_name.'.'.strtolower($file[1]);
    }

    return $img;
}

/**
 *  @brief Шукає url в рядку
 *  @param $url string
 *  @return string
 */
function searchUrl($url){
	preg_match("/ href=[\"|\'](.*?)[\"|\']/is", $url, $p1);

	return $p1;
}


/**
 * @brief визначає вибрану мову
 * @param [string] $par
 * @return string
 */
function lang($par = ''){
	$LANG=qtrans_getLanguage();
	if ($LANG && !$par) {$LANG='/'.$LANG;
	}
	return $LANG;
}


/**
 * @brief формує url адресу з врахуванням вибраної мови
 * @param [string] $url
 * @param [int] $print  0/1
 * @return string
 */
function setUrl($url, $print = 1){

	$langDefault = '/'.LANG_DEFAULT;
	$setLang = lang();

	mb_internal_encoding("UTF-8");

	$url = ($url[0] == '/')? mb_substr($url,1) : $url;
	$url = ($setLang == $langDefault)? '/'.$url : $setLang.'/'.$url;

	if ($print) {

		echo $url;
	} else {

		return $url;
	}
}

/**
 * @brief Шлях до ресурсів
 */
function get_assets_dir(){

	echo get_bloginfo('template_url')."/assets/images/";
}

/**
 * @brief Перевіряє чи заповнений alt для заброження
 * @param [string] $defolt
  * @param [string] $insert
 * @return string
 */
function get_alt($defolt, $insert = null) {

	if ($insert == null) return $defolt;


	return (isset($insert['alt']) && $insert['alt'])? $insert['alt'] : $defolt;

}


/**
 * @brief Обертає рядок в <li></li>
 * @param [string] $str
 * @return string
 */
function get_li($str = null, $class = null) {

	$class = ($class !== null)? 'class="'.$class.'"' : '';

	return preg_replace('~^(?![\r\n])(.*?)\r?\n?$~m','<li '.$class.'>$1</li>',$str);
}

function get_data_text_array($data) {

		$text = explode(PHP_EOL,  $data);


		return $text;
}

function get_formated_str($text, $lenght = 10) {

	return preg_replace('/\s+?(\S+)?$/', '', substr(wp_filter_nohtml_kses($text), 0, $lenght)).'...';
}




function get_data_fasad($floor = null){

	if($floor == null) $floor = (object)['url'=>'#', ];

	echo ' data-href="'.(@$floor->url ? $floor->url : 'javascript:;').'&dom=1" href="'.(@$floor->url ? $floor->url : 'javascript:;').'" xlink:href="'.(@$floor->url ? $floor->url : 'javascript:;').'&dom=1" ';

}

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
    $value = get_field( "block_2", 'option');


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
        $val = array(
            "field_5e4d23c13828e"	=> $data['data']['access_token'],
            "field_5e61f6390bd6d" => $data['data']['expires_exp']
        );
        update_field("field_5e4ceb1dbbfc6", $val, 'option');
//        header('Location: '.$_SERVER['REQUEST_URI']);
//        wp_redirect($_SERVER['REQUEST_URI']);
    }

}
//функции для 3D в json без отрисовки страницы

function getFlatById($id){
    $devbase = new ApiDevbase();
    $value = get_field( "block_2", 'option');
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

    $devbase = new ApiDevbase();
    $value = get_field( "block_2", 'option');
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

    $devbase = new ApiDevbase();
    $value = get_field( "block_2", 'option');
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
    $url = 'https://bereg-wp.smarto.agency/flat/';
//    $img_svg = get_template_directory_uri().'/assets'.$data['data']['img'];
    $img_svg = 'https://bereg-wp.smarto.agency/wp-content/themes/bereg/assets/img/projects/1/color-floors/6.png';


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
function createSvg2($dom,$floor){
    $data = getFloor($dom, $floor);
//    $data = getFloor($_POST['house'], $_POST['floor']);
    $url = 'https://bereg-wp.smarto.agency/flat/';
    $img_svg = get_template_directory_uri().'/assets'.$data['data']['img'];
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

            $cords = $item['sorts'];
            $svg_block.= '<polygon '.$style.'  class="flat-link-path '.$class.'"
                         points="'.$cords.'"
						  data-name="'.$item['type'].'"
						   data-link="'.$item['type'].'"
						    data-rooms="'.$item['rooms'].'"
						     data-total="'.$item['all_room'].'"
						      data-living="'.$item['life_room'].'"
						      data-id="'.$item['id'].'" ></polygon>';
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
    $devbase = new ApiDevbase();
    $value = get_field( "block_2", 'option');
    $data = $devbase->curl('GET', 'apartments',
        [
            'access_token'=>$value['token'],
            'option' => [
                'id'=>$id
            ]
        ]);


//    $_POST['house'], $_POST['floor']

    $floor = createSvg2($data['data']['data'][0]['build'],$data['data']['data'][0]['floor']);
    $flatPage = "";
    $flatPage .= "";
    $flatPage .= " <div class='flat-parameters'><h1>ID-".$id."</h1>
                <div class='flat-parameters__general-block'>
                    <div class='flat-parameters__title common-subtitle'>".__('Параметри:','bereg')."</div>
                    <div class='total-square'>".__('Загальна:','bereg')."<span class='square-digit'>
                 ".$data['data']['data'][0]['all_room']."м<sub>2</sub></span></div>
                    <div class='living-square'>".__('Житлова:','bereg')."<span class='square-digit'>".$data['data']['data'][0]['life_room']." м<sub>2</sub></span></div>
                </div>
                <div class='flat-parameters__rooms-block'>";
    foreach ($data['data']['data'][0]['properties'] as $room){
        $flatPage .="<div class='room-type'>
                        <span>".__($room['property_name'],'bereg')."</span>
                        <span class='room-type-digit'>".$room['property_flat']."м<sub>2</sub></span>
                    </div>";
    }?>
    <? $flatPage .= " </div>
              </div>
              <div class='flat-buttons-group'>
  <a class='button flat-price-info-button form-js'>
   <span class='button_text'>".__('Дізнатись ціну','bereg')."</span>
  </a>
  <a class='transparent-button flat-transparent-pdf' target='_blank' href='/pdf?flat=".$data['data']['data'][0]['id']."&floor=".$data['data']['data'][0]['floor']."'>".__('завантажити PDF','bereg')."</a>
</div>
<div class='flat-group2'>
    <div class='mini-floor-plan'>".$floor."
    </div>
</div>

              ";

    $flatPage .="
            <a class='flat-button-return' href='/choose-floor/'>
              ".__('Вибрати іншу квартиру','bereg')."
            </a>
            <a class='js-flat-plan-mfp' href='".get_template_directory_uri()."/assets".$data['data']['data'][0]['img_big']."'>
                <img class='flat-plan' src='".get_template_directory_uri()."/assets".$data['data']['data'][0]['img_big']."' title='foto' alt='foto'/>
            </a>
            </div>
        ";

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
    $floorPage = '<div class="s3d-floor__helper">
    <div class="s3d-floor__helper-logo"><img src="'.get_template_directory_uri().'/assets/s3d/images/icon/small-logo.svg" /></div>
    <div class="s3d-floor__helper-img"><img src="" class="js-s3d-floor__helper-img" /></div>
    <div class="s3d-floor__helper-type">Квартира <span class="js-s3d-floor__helper-type">2A</span></div>
    <div class="s3d-floor__helper-flat">Кімнат <span class="js-s3d-floor__helper-flat">3</span></div>
    <div class="s3d-floor__helper-area">Житл. пл<span class="js-s3d-floor__helper-area">79.93</span><span>м2</span></div>
    <div class="s3d-floor__helper-place">Заг. пл<span class="js-s3d-floor__helper-place">129.93</span><span>м2</span></div>
 </div>';
    $floorPage .= "<div class='floor-wrapper custom-scroll' id='js-floor' 
                        data-zoom-on-wheel='zoom-amount: 0.001; max-scale: 10;' 
                        data-pan-on-drag='button: left;' >";
    $img_svg = get_template_directory_uri().'/assets'.$data['data']['img'];
    $size = getimagesize($img_svg);
    if ($size[0]<=1200){

        $cssSize = $size[0];
    } else {

        $cssSize = 1250;
        $keff = $size[0]/$cssSize; // коэф. для просчета высоты, делим реальную ширину на 1250
    }
    $cssSizeHeight = ($cssSize != 1250)? $size[1] : $size[1]/$keff;

    $svg = '<svg id="floor--svg" '.' viewBox="0 0 '.$cssSize.' '.$cssSizeHeight*1.1.'" version="1.0" xmlns="http://www.w3.org/2000/svg" >
                                <image xlink:href="'.$img_svg.'" x="0" y="0" height="100%" width="100%"></image>';



    $cnt = 0;

    foreach ($FloorData['dataList'] as $key=>$polygon) {
//        if (!$polygon->sorts) continue;
        $filter_poligon = (isset($polygon['filter']) && !$polygon['filter'])? ' entrance-flats__item_floor--not-active' : ' entrance-flats__item_floor--active';

        $img_flat = get_template_directory_uri().'/assets/img/projects/1/'.$polygon['build'].'/'.$polygon['img'];
        $svg.= '<g   style="transform: scale(0.39, 0.385) translateX(-3%);"
							  data-type = "'.$polygon['type'].'К"
							  data-rooms = "'.$polygon['rooms'].'К"
							  data-image = "'.$img_flat.'"
							  data-color = "'.$polygon['status_color'].'"
							  data-price_m="'.$polygon['price_m2'].'"
							  data-square="'.$polygon['all_room'].'"
							  data-living="'.$polygon['life_room'].'"
							  data-price="'.$polygon['price'].'"
							  data-floor="'.$polygon['floor'].'"
							  data-flat_id="'.$polygon['id'].'"
							  data-num="'.(($polygon['number'])? "№ {$polygon['number']}" : '').'"
						
							  data-text="'.$polygon['statu_text'].'"
							  
							class="entrance-flats__item-js js-hover-mini-flat-item js-open-tab plan-floor-appartment">
											<polygon class="floor-svg-polygon fill-'.$polygon['status_color'].$filter_poligon.'" points="'.$polygon['sorts'].'"></polygon>
											<symbol id="qwer'.$cnt.'" viewBox="0 0 80 30" class="svg-tip-plan-floor">
												<path class="u-st0" d="M65,1H15C7.3,1,1,7.3,1,15s6.3,14,14,14h50c7.7,0,14-6.3,14-14S72.7,1,65,1z"/>
												<path class="u-st1" d="M65,0H15C6.7,0,0,6.7,0,15c0,8.3,6.7,15,15,15h50c8.3,0,15-6.7,15-15C80,6.7,73.3,0,65,0z M65,29H15
													C7.3,29,1,22.7,1,15S7.3,1,15,1h50c7.7,0,14,6.3,14,14S72.7,29,65,29z"/>
												<circle class="'.$polygon['status_color'].'" cx="17" cy="15" r="9.5"/>
												<rect x="8" y="10.8" class="u-st3" width="18.3" height="7.8"/>
												<text transform="matrix(1 0 0 1 11.8252 18.3863)" class="u-st6 u-st4 u-st5">'.$polygon['rooms'].'к</text>
												<text transform="matrix(1 0 0 1 30.9188 19)" class="u-st6 u-st7 u-st5">'.$polygon['all_room'].'м<tspan y="-3.5" style="font-size: 0.7em" class="g-kva">2</tspan></text>
											</symbol>
											<symbol  id="qwery'.$cnt.'" viewBox="0 0 28 28" >
												<circle class="little-cir-st0" cx="14" cy="14" r="14"/>
												<circle class="'.$polygon['status_color'].'" cx="14" cy="14" r="10"/>
												<text transform="matrix(1 0 0 1 8.6602 16.4624)" class="little-cir-st2 little-cir-st3 little-cir-st4">'.$polygon['rooms'].'к</text>
											</symbol>
											<use x="0" y="0" width="340px" height="340px"  xlink:href="#qwer'.$cnt.'" class="svg-tip-plan-floor--big svg-tip-plan-floor-'.$cnt.'"></use>
											<use x="0" y="0" width="340px" height="340px"  xlink:href="#qwery'.$cnt.'" class="svg-tip-plan-floor--small svg-tip-plan-floor-'.$cnt.'"></use>
									  </g>';
        $cnt++;
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


