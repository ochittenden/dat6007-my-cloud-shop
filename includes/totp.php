<?php
declare(strict_types=1);
function base32_decode_secret(string $secret): string {
 $alphabet='ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';$bits='';$out='';
 foreach(str_split(strtoupper($secret)) as $char){$pos=strpos($alphabet,$char);if($pos===false)return ''; $bits.=str_pad(decbin($pos),5,'0',STR_PAD_LEFT);}
 foreach(str_split($bits,8) as $chunk) if(strlen($chunk)===8)$out.=chr(bindec($chunk));
 return $out;
}
function base32_random(int $byteCount=20): string {
 $bits='';foreach(str_split(random_bytes($byteCount)) as $b)$bits.=str_pad(decbin(ord($b)),8,'0',STR_PAD_LEFT);
 $alphabet='ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';$result='';foreach(str_split($bits,5) as $chunk)$result.=$alphabet[bindec(str_pad($chunk,5,'0'))];return $result;
}
function totp_code(string $secret,int $time): string {
 $key=base32_decode_secret($secret);$counter=intdiv($time,30);
 $binary=pack('N2',intdiv($counter,4294967296),$counter%4294967296);
 $hash=hash_hmac('sha1',$binary,$key,true);$offset=ord($hash[19])&15;
 $value=(unpack('N',substr($hash,$offset,4))[1]&0x7fffffff)%1000000;
 return str_pad((string)$value,6,'0',STR_PAD_LEFT);
}
function totp_verify(string $secret,string $code): bool {
 if(!preg_match('/^\d{6}$/',$code))return false;
 foreach([-30,0,30] as $shift) if(hash_equals(totp_code($secret,time()+$shift),$code))return true;
 return false;
}
