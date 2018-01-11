<?php

function exception_handler($exception) {
	$base_dir = str_replace(DIRECTORY_SEPARATOR.'common', '', __DIR__).DIRECTORY_SEPARATOR;
	$files_dir = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'errors'.DIRECTORY_SEPARATOR;
	$exception_string = $exception->__toString();
	$hash = md5($exception_string);
	$file = $files_dir.str_replace(array($base_dir, DIRECTORY_SEPARATOR), array('', '$'), $exception->getFile()).'#'.$exception->getLine().'#'.$hash.'.log';
	if(!file_exists($file))
		file_put_contents($file, $exception_string);
	$msg = 'Erro: Infelizmente um erro grave e inesperado ocorreu. Por favor, tente novamente.';
	if((php_sapi_name() == 'cli') || ((defined('GDE_ADMIN')) && (GDE_ADMIN === true)))
		$msg .= "\n\n".$exception_string;
	if(defined('JSON'))
		\GDE\Base::Error_JSON($msg);
	else {
		echo $msg;
		if(isset($FIM))
			echo $FIM;
	}
};
set_exception_handler('exception_handler');

function exception_error_handler($severity, $message, $file, $line) {
	if(!(error_reporting() & $severity)) {
		// This error code is not included in error_reporting
		return;
	}
	throw new \ErrorException($message, 0, $severity, $file, $line);
}
set_error_handler("exception_error_handler");

function exception_fatal_handler() {
	$error = error_get_last();
	if(($error !== null) && ($error['type'] === E_ERROR)) {
		throw new \ErrorException($error["message"], 0, $error["type"], $error["file"], $error["line"]);
	}
}

register_shutdown_function("exception_fatal_handler");
