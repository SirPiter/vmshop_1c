<?php
//***********************************************************************
// Назначение: Передача товаров из 1С в virtuemart
//Для Джумлы 2.5, если первая не работает
// Модуль: checkauth.php - Авторизация на сервере

//***********************************************************************

if ( !defined( 'VM_1CEXPORT' ) )
{
	print "<h1>Несанкционированный доступ</h1>Ваш IP уже отправлен администратору.";
	die();
}

// Проверяем логин и пароль на доступ
if( ! isset($_SERVER['PHP_AUTH_USER']) OR ! isset($_SERVER['PHP_AUTH_PW']) ) {
	$log->addEntry ( array ('comment' => 'Этап 1) Не введен логин и пароль') );
	if(!defined( 'VM_SITE' ))
	{
		print "failure\n";
		print "no login/password";
		exit;
	}
	else
	{
		$logs_http[] = "<strong>Авторизация на сервере</strong> - Не введен логин и пароль";
	}
}

$username	=	trim($_SERVER['PHP_AUTH_USER']);
$password	=	trim($_SERVER['PHP_AUTH_PW']);

if (VM_VERVM == '2')
{
	$username_esc = $db->escape($username,true);
}
elseif(VM_VERVM == '1')
{
	$username_esc = $db->getEscaped($username,true);
}

$query = "SELECT `id`, `password` FROM #__users where username='" . $username_esc . "'";
$db->setQuery( $query );
$result = $db->loadObject();
$log->addEntry ( array ('comment' => 'Этап 1) логин: ' .$username_esc) );
// Авторизуем
if( !$result ) {
	$log->addEntry ( array ('comment' => 'Этап 1) Неверный логин') );
	if(!defined( 'VM_SITE' ))
	{
		print "failure\n";
		print "error login";
		exit;
	}
	else
	{
		$logs_http[] = "<strong>Авторизация на сервере</strong> - Неверный логин";
	}
}

//$parts   = explode( ':', $result->password );
//$log->addEntry ( array ('comment' => 'Этап 1) parts: ' .$result->password) );
//$crypt   = $parts[0];
//$salt   = @$parts[1];
//$testcrypt = JUserHelper::getCryptedPassword($password, $salt);

//Аматор
//$log->addEntry ( array ('comment' => 'Этап 1) пароль: ' .$password) );
//$log->addEntry ( array ('comment' => 'Этап 1) crypt: ' .$crypt) );
//$log->addEntry ( array ('comment' => 'Этап 1) salt: ' .$salt) );
//$log->addEntry ( array ('comment' => 'Этап 1) $testcrypt: ' .$testcrypt) );

//Denis
$credentials = array( 'username'=>$username_esc, 'password'=>$password );
$options = array( 'remember'=>true );

//Denis
if( JFactory::getApplication()->login( $credentials, $options )){
//if($crypt == $testcrypt){
	$id_admin = $result->id;

	$somecontent = $id_admin."\n".$username;
	
	$log->addEntry ( array ('comment' => 'Этап 1) Пользователь: '.$username_esc.' успешно авторизовался.') );	
	if(!defined( 'VM_SITE' ))
	{
		echo 'success';
	}
	else
	{
		$logs_http[] = "<strong>Авторизация на сервере</strong> - Успешно";
		require_once(JPATH_BASE_1C .DS.'form'.DS.'auth_form.php');
	}
}
else
{
	$log->addEntry ( array ('comment' => 'Этап 1) Неверный пароль') );

	

	if(!defined( 'VM_SITE' ))
	{
		print "failure\n";
		print "error password";
		exit;
	}
	else
	{
		$logs_http[] = "<strong>Авторизация на сервере</strong> - Неверный пароль";
	}
}

?>