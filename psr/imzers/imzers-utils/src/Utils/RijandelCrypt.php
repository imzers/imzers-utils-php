<?php
namespace Imzers\Utils;
Class RijandelCrypt {
	protected $encrypt_config = array();
	function __construct($encrypt_config) {
		$this->encrypt_config = array(
			'ENCRYPT_KEY'					=> (isset($encrypt_config['ENCRYPT_KEY']) ? $encrypt_config['ENCRYPT_KEY'] : 'F389E802B10F7FEF499C9EAE56CC0B81'),
			'ENCRYPT_IV'					=> (isset($encrypt_config['ENCRYPT_IV']) ? $encrypt_config['ENCRYPT_IV'] : '76b08b24596e12d4553bd41fc93cccd5bac2fe7a'),
		);
	}
	public function decrypt($input) {
        $dectext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->encrypt_config['ENCRYPT_KEY'], base64_decode(strtr($input, '-_', '+/')), MCRYPT_MODE_CBC, $this->encrypt_config['ENCRYPT_IV']);
        $dectext= rtrim($dectext,"\x00..\x1F");
        return $dectext;
    }
	public function encrypt($text) {
		$block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
        $padding = $block - (strlen($text) % $block);
        $text .= str_repeat(chr($padding), $padding);
        $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->encrypt_config['ENCRYPT_KEY'], $text, MCRYPT_MODE_CBC, $this->encrypt_config['ENCRYPT_IV']);
		return base64_encode(strtr($crypttext, '+/', '-_'));
    }
	
	
	
	
	
	
	
}