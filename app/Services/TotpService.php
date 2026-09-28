<?php
namespace App\Services;

class TotpService
{
    public function secret(int $length = 20): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $out = '';
        for ($i=0; $i<$length; $i++) $out .= $alphabet[random_int(0,31)];
        return $out;
    }
    public function code(string $secret, ?int $timestamp = null): string
    {
        $timestamp ??= time();
        $counter = intdiv($timestamp,30);
        $bin = pack('N*',0) . pack('N*',$counter);
        $key = $this->base32Decode($secret);
        $hash = hash_hmac('sha1',$bin,$key,true);
        $offset = ord($hash[19]) & 0x0f;
        $value = ((ord($hash[$offset]) & 0x7f) << 24) | ((ord($hash[$offset+1]) & 0xff) << 16) | ((ord($hash[$offset+2]) & 0xff) << 8) | (ord($hash[$offset+3]) & 0xff);
        return str_pad((string)($value % 1000000),6,'0',STR_PAD_LEFT);
    }
    public function verify(string $secret,string $code,int $window=1): bool
    {
        $code = preg_replace('/\D/','',$code);
        for($i=-$window;$i<=$window;$i++) if(hash_equals($this->code($secret,time()+($i*30)),$code)) return true;
        return false;
    }
    public function provisioningUri(string $secret,string $email,string $issuer='Chacha Prime'): string
    {
        return 'otpauth://totp/'.rawurlencode($issuer.':'.$email).'?secret='.rawurlencode($secret).'&issuer='.rawurlencode($issuer).'&algorithm=SHA1&digits=6&period=30';
    }
    private function base32Decode(string $input): string
    {
        $alphabet='ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';$input=strtoupper(preg_replace('/[^A-Z2-7]/','',$input));$bits='';
        foreach(str_split($input) as $c){$v=strpos($alphabet,$c);if($v===false)continue;$bits.=str_pad(decbin($v),5,'0',STR_PAD_LEFT);}
        $out='';for($i=0;$i+8<=strlen($bits);$i+=8)$out.=chr(bindec(substr($bits,$i,8)));return $out;
    }
}
