<?php 
/**
* Класс для определения позиция сайта в Яндексе по ключевым словам
* По средствам yandex xml
*/
class Yandex_Site_Position 
{
	
	public static function get_xml($user, $key, $lr, $query, $num_pos = 100)
	{
		
		// Принимаем данные в формате xml	
		header('Content-type: application/xml');
		$xml = new SimpleXMLElement("<?xml version='1.0' encoding='utf-8'?><request></request>");

		// Генерируем xml 
		$url = 'https://yandex.ru/search/xml'
			 . '?user='.$user
			 . '&key='.$key
			 . '&l10n=kk'
			 . '&groupby=attr%3D%22%22.mode%3Dflat.groups-on-page%3D'.$num_pos.'.docs-in-group%3D1';

		// 	Если указан регион поиска, то добавляем его в url
		if ($lr) {
			$url .= '&lr='.$lr;
		}

		// Если указан запрос, то добавлем его в url, если нет, выдаем ошибку
		if ((isset($query)) && ($query)) {
			$url .= '&query='.$query;
		} else{
			die('Request empty!');
		}

		$data = file_get_contents($url);

		return $data;

	}

	public static function get_site_position($xml_string, $web_site)
	{

		// Парсим полученные данные 
		$domains = array();
		$i = 0;
		$xml = new SimpleXMLElement($xml_string);
		$result = array();
		$comment = '';

		// Проверяем, нет ли ошибок
		if (isset($xml->response->error)) {
			die((string)$xml->response->error);
		}
		
		foreach ($xml->response->results->grouping->group as $group) {

			$i++;
			$domain_to_reg_exp = (string) $group->doc->domain;

			// удаляем все не нужные символы, если они есть
			$domain_name = preg_replace('/(\n+)?(\s+)?(\t+)?(\r+)?/', '', $domain_to_reg_exp);

			// смотрим, есть ли нужный нам сайт среди результатов
			// если есть, то выводим и останавливаемся
			if ($domain_name  == $web_site) {

				$position = $i;
				break;

			} else {

				// Если ключевик не найден в первых 100 результатах, сообщаем об этом пользователю
				$position = 0;
				$comment = '100+';

			}

		}

		$result = array(
			'position' => $position,
			'comment'  => $comment,
		);

		return $result;

	}
}