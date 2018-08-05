<?php
header('Content-Type: text/html; charset=utf-8');

$text = "fizzbazz";
$mcrypt = new Mcrypt;

/*オリジナル*/
echo "Original Text: {$text} \r\n"."<br/>";
# Original Text: fizzbazz 

/*暗号化します*/
$encrypted = $mcrypt->encrypt($text); 
echo "Encrypted: {$encrypted} \r\n"."<br/>";
# Encrypted: KbR8BrizX2KfpMw5G6gxi0B6TLPhkRAShSY106kjO38= 

/*復号化します*/
$mcrypt1 = new Mcrypt;
$decrypted = $mcrypt1->decrypt($encrypted);
echo "Decrypted: {$decrypted} \r\n"."<br/>";


$e = base64_encode("あいうえおかきくけこ");
echo base64_decode(str_replace(" ", "+", $e));


class Mcrypt
{
    const SECRET = "5!47?92977d7fd5c1ALa6072c#";

    private $td;
    private $iv = "Y4ENR5dTx4Hy5wNLfF%UhU~J=AjNEfLq";
    private $key;

    private function initialize()
    {
        /* Open the cipher */
        $this->td = mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', MCRYPT_MODE_CBC, '');

        /* Create the IV and determine the keysize length*/
        if (!$this->iv) {
            $this->iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($this->td), MCRYPT_DEV_URANDOM);
        }
        $ks = mcrypt_enc_get_key_size($this->td);

         /* Create key */
        $this->key = substr(hash("sha256", self::SECRET), 0, $ks);
    }

    public function encrypt($input)
    {
        $this->initialize();

        /* Intialize encryption */
        mcrypt_generic_init($this->td, $this->key, $this->iv);

        /* Encrypt data */
        $encrypted = mcrypt_generic($this->td, base64_encode($input));

        $this->finalize();

        return base64_encode($encrypted);
    }

    public function decrypt($encrypted)
    {
        $this->initialize();

        /* Intialize encryption */
        mcrypt_generic_init($this->td, $this->key, $this->iv);

        $encrypted = base64_decode($encrypted);
        

        /* Decrypt encrypted string */
		$decrypted = mdecrypt_generic($this->td, $encrypted);


        $this->finalize();

        return base64_decode($decrypted);
    }

    private function finalize()
    {
        /* Terminate handle and close module */
        mcrypt_generic_deinit($this->td);
        mcrypt_module_close($this->td);
    }
}
