<?php

	include $_SERVER['DOCUMENT_ROOT']."/api/sto/1.0/connect_db.php";
	
	header('Content-Type: text/html; charset=utf-8');

	$year =date("y");   
	$month =date("m");  
	$day = date("d");  
	$id_user_car = 18; // id пользователя, чей заказ-наряд загружается.
	$doc_root = "$_SERVER['DOCUMENT_ROOT']" ;

	//Лист запрещенных форматов
	$blacklist = array(".php", ".phtml", ".php3", ".php4", ".html", ".htm"); 
	foreach ($blacklist as $item)
	if(preg_match("/$item\$/i", $_FILES['filename']['name'])) exit;
	
	//Проверяем на формат и размер
	$type = $_FILES['filename']['type'];
	$size = $_FILES['filename']['size'];
	//if (($type != "application/pdf") && ($type != "application/x-pdf")) exit("Не тот формат");
	//if ($size > 31457280) exit("Файл слишком большой"); //ограничение на 30 мб

	//Проверки, существуют ли директории и если нет, то создаются. Возможно для Линукса права доступа надо поправить.(757) если не сработает, то (777)
	if(!file_exists ($_SERVER['DOCUMENT_ROOT'] . "files/".$year)){mkdir($_SERVER['DOCUMENT_ROOT'] . "files/" . $year , 0700);} // год
	if(!file_exists ($_SERVER['DOCUMENT_ROOT'] . "files/". $year . "/" . $month)){mkdir($_SERVER['DOCUMENT_ROOT'] . "files/". $year . "/" . $month , 0700);} //месяц
	if(!file_exists ($_SERVER['DOCUMENT_ROOT'] . "files/". $year . "/" . $month . "/" . $day)){mkdir($_SERVER['DOCUMENT_ROOT'] . "files/" . $year . "/" . $month . "/" . $day , 0700);} //число месяца

	//Получаем md5 хэш
	$file_hash = md5_file($_FILES['filename']['name']);
	echo $file_hash;
	//Сохраняем файл из временной папки в конечную
	$uploadfile = $_SERVER['DOCUMENT_ROOT'] . "files/". $year . "/" . $month . "/" . $day . "/"  .  $_FILES['filename']['name'];
	move_uploaded_file($_FILES['filename']['tmp_name'], $uploadfile);
	
	//Переименовываем наш загруженный файл в md5 хэш
	rename ($_SERVER['DOCUMENT_ROOT'] . "files/". $year . "/" . $month . "/" . $day . "/"  . $_FILES['filename']['name'], $_SERVER['DOCUMENT_ROOT'] . "files/". $year . "/" . $month . "/" . $day . "/"  . $file_hash . ".pdf"); 
	
	//Присваиваем переменным полные пути
	(string)$original_name =  $_FILES['filename']['name'] ;
	(string)$md5_name = $_SERVER['DOCUMENT_ROOT'] . "files/". $year . "/" . $month . "/" . $day . "/"  . $file_hash . ".pdf" ;
	
	//Готовим и отправляем запрос на внесение в таблицу оригинального названия файла и md5
	$sql = "UPDATE history.record SET original_name = '$original_name', md5_name = '$md5_name' WHERE id_user_car = $id_user_car";
	$res = mysqli_query($dbh, $sql);
		
?>