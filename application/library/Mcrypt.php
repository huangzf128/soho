<?php

class Mcrypt
{
    const SECRET = "G!Y7?A2B777f)5c1#La6b09%72c#";

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

        mcrypt_generic_init($this->td, $this->key, $this->iv);

        $encrypted = mcrypt_generic($this->td, base64_encode($input));

        $this->finalize();

        return base64_encode($encrypted);
    }

    public function decrypt($encrypted)
    {
        $this->initialize();

        mcrypt_generic_init($this->td, $this->key, $this->iv);

        $encrypted = base64_decode($encrypted);

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