<?php
	
	include $_SERVER['DOCUMENT_ROOT']."/api/sto/1.0/errors_log.php";
	
	$ArrayAll = array();
	
	// присваиваем перемнной id имя файла без .pdf
	$id = (int)$_FILES['file']['name'];
	$file =  $_FILES['file']['name'];
	
	//$ArrayAll['id'] = $id;
	//$ArrayAll['file'] = $file;
	
	if (!empty($file)){
		include $_SERVER['DOCUMENT_ROOT']."/api/sto/1.0/connect_db.php";
		
		//Проверяем существование id
		$sql = "SELECT id FROM history.record WHERE id = $id";
		$res = mysqli_query($dbh, $sql);
		$row = mysqli_fetch_array($res);
		if(!empty($row)){
									
			$year = date("y");
			$month = date("m");
			$day = date("d");  
			$doc_root = $_SERVER['DOCUMENT_ROOT'];
			
			//Лист разрешенных форматов
			$blacklist = array(".pdf"); 
			foreach ($blacklist as $item)
			if(!preg_match("/$item\$/i", $_FILES['file']['name'])) exit;
			
			//Проверяем на формат и размер
			$type = $_FILES['file']['type'];
			$size = $_FILES['file']['size'];
			if (($type != "application/pdf") && ($type != "application/x-pdf"));// exit("Не тот формат");
			//if ($size > 31457280);// exit("Файл слишком большой"); //ограничение на 30 мб
			
			//Проверки, существуют ли директории и если нет, то создаются.
			if(!file_exists ("$doc_root/files/$year")) mkdir("$doc_root/files/$year" , 0777); // год
			if(!file_exists ("$doc_root/files/$year/$month")) mkdir("$doc_root/files/$year/$month" , 0777); //месяц
			if(!file_exists ("$doc_root/files/$year/$month/$day")) mkdir("$doc_root/files/$year/$month/$day" , 0777); //число месяца
			
			//Получаем md5 хэш
		// 	$file_hash = md5_file($_FILES['filename']['name']);
			$file_hash = md5($_FILES['file']['name']).".pdf";
			$file_name = $_FILES['file']['name'].$_FILES['file']['type'];
			
			//Сохраняем файл из временной папки в конечную
			$uploadfile = "$doc_root/files/$year/$month/$day/$file_hash";
			move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);
			
			//Присваиваем переменным полные пути
			(string)$original_name =  $_FILES['file']['name'];
			(string)$md5_name = "files/$year/$month/$day/$file_hash";
			
			//Готовим и отправляем запрос на внесение в таблицу оригинального названия файла и md5
			$sql = "UPDATE history.record SET original_name = '$original_name', md5_name = '$md5_name' WHERE id = $id";
			$res = mysqli_query($dbh, $sql);
			
			$ArrayAll['Error'] = '10' ;
	
		}else{
			$ArrayAll['Error'] = '00' ;
		}

		//Закрываем соединение с БД
		mysqli_close($dbh);
		
	}else{
		$ArrayAll['Error'] = '01';
	}
	OutPut($ArrayAll);
	
?>
